<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCsvImport;
use App\Models\Tenant\ImportJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function index(): Response
    {
        $imports = ImportJob::with('user')->latest()->paginate(20);

        return Inertia::render('Tenant/Imports/Index', [
            'imports' => $imports,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $filename = 'imports/' . uniqid() . '_' . $file->getClientOriginalName();
        Storage::put($filename, file_get_contents($file));

        $importJob = ImportJob::create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'status' => 'processando',
        ]);

        ProcessCsvImport::dispatch($importJob->id, $filename, tenant('id'));

        return redirect()->route('tenant.imports.show', $importJob)
            ->with('success', 'Arquivo enviado. O processamento foi iniciado em background.');
    }

    public function show(ImportJob $importJob): Response
    {
        return Inertia::render('Tenant/Imports/Show', [
            'importJob' => $importJob,
        ]);
    }
}
