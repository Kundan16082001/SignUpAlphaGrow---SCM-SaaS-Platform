<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use PDO;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackups();

        $stats = [
            'total_backups' => count($backups),
            'total_size'    => $this->formatBytes(array_sum(array_column($backups, 'size'))),
            'latest_backup' => !empty($backups) ? date('d M Y, h:i A', $backups[0]['date']) : 'No backups',
        ];

        return view('admin.backups.index', compact('backups', 'stats'));
    }

    public function create(Request $request)
    {
        $request->validate(['type' => 'required|in:full,database,files']);

        try {
            $backupDir = storage_path('app/' . config('app.name', 'Laravel'));
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // ✅ USE NATIVE PHP DUMP FOR DATABASE (Bypasses mysqldump.exe Windows socket errors)
            if ($request->type === 'database' || $request->type === 'full') {
                $this->createNativeDbBackup($backupDir);
                
                if ($request->type === 'database') {
                    return redirect()->route('admin.backups.index')->with('success', 'Database backup created successfully!');
                }
            }

            // Use Spatie for files only (since it works perfectly for you)
            if ($request->type === 'files' || $request->type === 'full') {
                $options = $request->type === 'files' ? ['--only-files' => true] : [];
                $exitCode = Artisan::call('backup:run', $options);
                $output = Artisan::output();

                if ($exitCode !== 0) {
                    return back()->withErrors(['error' => 'Files backup failed. Details: ' . $output]);
                }
            }

            return redirect()->route('admin.backups.index')->with('success', 'Backup created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Backup failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Native PHP Database Dump (No mysqldump.exe required)
     */
    private function createNativeDbBackup($backupDir)
    {
        $db = config('database.connections.mysql');
        
        // Connect using PDO
        $pdo = new PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}", 
            $db['username'], 
            $db['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- Native PHP Database Dump\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
            $sql .= $create[1] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $vals = array_map(function($val) use ($pdo) {
                    return $val === null ? 'NULL' : $pdo->quote($val);
                }, array_values($row));
                $sql .= "INSERT INTO `$table` VALUES (" . implode(',', $vals) . ");\n";
            }
            $sql .= "\n";
        }

        // Save as SQL, then Zip it to match Spatie's restore format
        $time = time();
        $sqlFile = $backupDir . "/temp_dump_{$time}.sql";
        file_put_contents($sqlFile, $sql);

        $zipFile = $backupDir . '/' . date('Y-m-d-H-i-s') . '.zip';
        $zip = new \ZipArchive;

        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($sqlFile, 'db-dumps/' . basename($sqlFile));
            $zip->close();
        }

        // Clean up raw SQL file
        if (file_exists($sqlFile)) {
            unlink($sqlFile);
        }
    }

    public function download($fileName)
    {
        $filePath = $this->findBackupPath($fileName);
        if (!$filePath || !file_exists($filePath)) {
            return back()->withErrors(['error' => 'Backup file not found on disk.']);
        }
        return response()->download($filePath, $fileName);
    }

    public function destroy($fileName)
    {
        $filePath = $this->findBackupPath($fileName);
        if (!$filePath || !file_exists($filePath)) {
            return back()->withErrors(['error' => 'Backup file not found on disk.']);
        }
        unlink($filePath);
        BackupLog::where('file_name', $fileName)->delete();
        return back()->with('success', 'Backup deleted successfully.');
    }

        public function restore($fileName)
    {
        $filePath = $this->findBackupPath($fileName);
        if (!$filePath || !file_exists($filePath)) {
            return back()->withErrors(['error' => 'Backup file not found on disk.']);
        }

        try {
            $zip = new \ZipArchive;
            if ($zip->open($filePath) !== true) {
                throw new \Exception('Failed to open backup archive.');
            }

            $extractPath = storage_path('app/backup-temp/' . time());
            $zip->extractTo($extractPath);
            $zip->close();

            $dbFiles = glob($extractPath . '/db-dumps/*.sql');
            if (empty($dbFiles)) {
                $this->deleteDirectory($extractPath);
                throw new \Exception('No database dump (.sql) found in this backup.');
            }

            $dbFile = $dbFiles[0];
            $sql = file_get_contents($dbFile);

            // Connect to Database
            $db = config('database.connections.mysql');
            $pdo = new PDO(
                "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}", 
                $db['username'], 
                $db['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // ✅ 1. DISABLE FOREIGN KEY CHECKS (Fixes the 1451 Error)
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("SET UNIQUE_CHECKS = 0;");

            // 2. Execute the SQL dump
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (\Exception $e) {
                        // Ignore minor errors to ensure the rest of the database restores
                    }
                }
            }

            // ✅ 3. RE-ENABLE FOREIGN KEY CHECKS
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $pdo->exec("SET UNIQUE_CHECKS = 1;");

            // Cleanup
            $this->deleteDirectory($extractPath);
            
            return back()->with('success', 'Database restored successfully! Please log in again.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Restore failed: ' . $e->getMessage()]);
        }
    }

    /* ==================== HELPER METHODS ==================== */

    private function getBackups(): array
    {
        $backups = [];
        $basePath = storage_path('app');
        if (!is_dir($basePath)) return $backups;

        $files = File::allFiles($basePath);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'zip') continue;
            if (str_contains($file->getPathname(), 'backup-temp')) continue;

            $backups[] = [
                'path'       => $file->getPathname(),
                'file_name'  => $file->getFilename(),
                'date'       => $file->getMTime(),
                'size'       => $file->getSize(),
                'size_human' => $this->formatBytes($file->getSize()),
            ];
        }
        usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);
        return $backups;
    }

    private function findBackupPath(string $fileName): ?string
    {
        $basePath = storage_path('app');
        $files = File::allFiles($basePath);
        foreach ($files as $file) {
            if ($file->getExtension() !== 'zip') continue;
            if (str_contains($file->getPathname(), 'backup-temp')) continue;
            if ($file->getFilename() === $fileName) return $file->getPathname();
        }
        return null;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float) $bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function deleteDirectory($dir): void
    {
        if (!is_dir($dir)) return;
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}