<?php

namespace App\Http\Controllers;

use App\Models\PrintTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrintTemplateController extends Controller
{
    /**
     * Display the template manager page.
     */
    public function index()
    {
        $templates = PrintTemplate::with('creator')->latest()->get();
        $activePR  = PrintTemplate::activeForPR();
        $activePO  = PrintTemplate::activeForPO();
        return view('print-templates.index', compact('templates', 'activePR', 'activePO'));
    }

    /**
     * Store a newly uploaded template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:300',
            'file'             => 'required|file|mimes:png,jpg,jpeg,pdf|max:15360', // max 15MB
            'padding_top_mm'   => 'nullable|numeric|min:0|max:150',
            'padding_left_mm'  => 'nullable|numeric|min:0|max:100',
            'padding_right_mm' => 'nullable|numeric|min:0|max:100',
            'padding_bottom_mm'=> 'nullable|numeric|min:0|max:100',
        ]);

        $file    = $request->file('file');
        $path    = $file->store('print-templates', 'public');
        $imgInfo = @getimagesize(Storage::disk('public')->path($path));

        PrintTemplate::create([
            'name'             => $request->name,
            'description'      => $request->description,
            'file_path'        => $path,
            'file_name'        => $file->getClientOriginalName(),
            'file_size'        => $file->getSize(),
            'width_px'         => $imgInfo[0] ?? 2480,
            'height_px'        => $imgInfo[1] ?? 3508,
            'padding_top_mm'   => $request->padding_top_mm   ?? 45,
            'padding_left_mm'  => $request->padding_left_mm  ?? 14,
            'padding_right_mm' => $request->padding_right_mm ?? 14,
            'padding_bottom_mm'=> $request->padding_bottom_mm ?? 22,
            'used_for_pr'      => false,
            'used_for_po'      => false,
            'created_by'       => Auth::id(),
        ]);

        return back()->with('success', 'Template "' . $request->name . '" berhasil diupload!');
    }

    /**
     * Update template name, description, and padding.
     */
    public function update(Request $request, PrintTemplate $printTemplate)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string|max:300',
            'padding_top_mm'   => 'nullable|numeric|min:0|max:150',
            'padding_left_mm'  => 'nullable|numeric|min:0|max:100',
            'padding_right_mm' => 'nullable|numeric|min:0|max:100',
            'padding_bottom_mm'=> 'nullable|numeric|min:0|max:100',
        ]);

        $printTemplate->update([
            'name'             => $request->name,
            'description'      => $request->description,
            'padding_top_mm'   => $request->padding_top_mm   ?? 45,
            'padding_left_mm'  => $request->padding_left_mm  ?? 14,
            'padding_right_mm' => $request->padding_right_mm ?? 14,
            'padding_bottom_mm'=> $request->padding_bottom_mm ?? 22,
        ]);

        return back()->with('success', 'Template berhasil diperbarui!');
    }

    /**
     * Delete a template and its file.
     */
    public function destroy(PrintTemplate $printTemplate)
    {
        // Delete physical file
        if (Storage::disk('public')->exists($printTemplate->file_path)) {
            Storage::disk('public')->delete($printTemplate->file_path);
        }

        $name = $printTemplate->name;
        $printTemplate->delete();

        return back()->with('success', 'Template "' . $name . '" berhasil dihapus.');
    }

    /**
     * Set a template as active for PR, PO, or both.
     */
    public function setActive(Request $request, PrintTemplate $printTemplate)
    {
        $request->validate([
            'type' => 'required|in:pr,po,both,none_pr,none_po',
        ]);

        $type = $request->type;

        if ($type === 'pr' || $type === 'both') {
            // Deactivate all others for PR first
            PrintTemplate::where('id', '!=', $printTemplate->id)->update(['used_for_pr' => false]);
            $printTemplate->update(['used_for_pr' => true]);
        }

        if ($type === 'po' || $type === 'both') {
            // Deactivate all others for PO first
            PrintTemplate::where('id', '!=', $printTemplate->id)->update(['used_for_po' => false]);
            $printTemplate->update(['used_for_po' => true]);
        }

        if ($type === 'none_pr') {
            $printTemplate->update(['used_for_pr' => false]);
        }

        if ($type === 'none_po') {
            $printTemplate->update(['used_for_po' => false]);
        }

        $labels = [
            'pr'      => 'Purchase Request (PR)',
            'po'      => 'Purchase Order (PO)',
            'both'    => 'PR & PO',
            'none_pr' => 'PR (dinonaktifkan)',
            'none_po' => 'PO (dinonaktifkan)',
        ];

        return back()->with('success', 'Template "' . $printTemplate->name . '" diatur untuk ' . ($labels[$type] ?? $type));
    }
}
