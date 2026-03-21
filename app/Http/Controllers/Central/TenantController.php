<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::withCount('domains')
            ->latest()
            ->paginate(20);

        return Inertia::render('Central/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Central/Tenants/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:tenants,slug|regex:/^[a-z0-9-]+$/',
        ]);

        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'slug' => $request->slug,
            'is_active' => true,
        ]);

        $tenant->domains()->create([
            'domain' => $request->slug . '.' . config('tenancy.central_domains')[0],
        ]);

        return redirect()->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant criado com sucesso.');
    }

    public function show(Tenant $tenant): Response
    {
        $tenant->load('domains');

        // Count tenant users
        tenancy()->initialize($tenant);
        $userCount = \App\Models\Tenant\User::count();
        $collaboratorCount = \App\Models\Tenant\Collaborator::count();
        tenancy()->end();

        return Inertia::render('Central/Tenants/Show', [
            'tenant' => $tenant,
            'stats' => [
                'user_count' => $userCount,
                'collaborator_count' => $collaboratorCount,
            ],
        ]);
    }

    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $tenant->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('central.tenants.show', $tenant)
            ->with('success', 'Tenant atualizado com sucesso.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('central.tenants.index')
            ->with('success', 'Tenant removido com sucesso.');
    }
}
