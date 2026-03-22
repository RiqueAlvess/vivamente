<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        $companies = Company::withCount('users')->latest()->paginate(20);

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Companies/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:companies,slug|regex:/^[a-z0-9-]+$/',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'is_active' => true,
        ]);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Empresa criada com sucesso.');
    }

    public function show(Company $company): Response
    {
        $company->loadCount(['users', 'collaborators', 'campaigns']);

        return Inertia::render('Admin/Companies/Show', [
            'company' => $company,
        ]);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Admin/Companies/Edit', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $company->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Empresa removida com sucesso.');
    }
}
