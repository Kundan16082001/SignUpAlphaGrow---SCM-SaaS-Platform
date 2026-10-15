<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = CompanySetting::firstOrCreate(['tenant_id' => $tenantId]);
        return view('admin.company.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'currency' => 'required|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $settings = CompanySetting::firstOrCreate(['tenant_id' => $tenantId]);

        $data = $request->only(['company_name', 'address', 'phone', 'email', 'tax_id', 'currency']);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $settings->update($data);
        return redirect()->back()->with('success', 'Company settings updated successfully!');
    }
}