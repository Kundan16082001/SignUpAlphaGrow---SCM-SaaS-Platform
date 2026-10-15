<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DmsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $query = Document::with('uploader')->where('tenant_id', $tenantId);

        // Filters
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $documents = $query->latest()->paginate(12);

        // Stats
        $stats = [
            'total_docs' => Document::where('tenant_id', $tenantId)->count(),
            'total_size' => Document::where('tenant_id', $tenantId)->sum('file_size'),
            'recent_uploads' => Document::where('tenant_id', $tenantId)->whereDate('created_at', today())->count(),
        ];

        $categories = ['All', 'Invoice', 'Contract', 'Compliance', 'HR', 'General'];

        return view('intelligence.dms.index', compact('documents', 'stats', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,zip|max:10240', // Max 10MB
            'category' => 'required|string',
            'name' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $file = $request->file('document_file');
        
        // Generate a safe file name
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName) . '.' . $extension;

        // Store file in 'documents' disk (public)
        $filePath = $file->storeAs('documents', $fileName, 'public');

        Document::create([
            'tenant_id' => $tenantId,
            'name' => $request->name ?: $originalName,
            'file_path' => $filePath,
            'file_type' => strtolower($extension),
            'file_size' => round($file->getSize() / 1024), // KB
            'category' => $request->category,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('intelligence.dms.index')->with('success', 'Document uploaded successfully!');
    }

    public function destroy(Document $document)
    {
        if ($document->tenant_id !== Auth::user()->tenant_id) abort(403);

        // Delete physical file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully!');
    }
}