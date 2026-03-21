<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::where('is_active', true)->get();

        $allUsers = [];
        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            $users = TenantUser::all()->map(function ($user) use ($tenant) {
                return array_merge($user->toArray(), [
                    'tenant_name' => $tenant->name,
                    'tenant_slug' => $tenant->slug,
                    'tenant_id' => $tenant->id,
                ]);
            });
            $allUsers = array_merge($allUsers, $users->toArray());
            tenancy()->end();
        }

        return Inertia::render('Central/Users/Index', [
            'users' => $allUsers,
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        $tenants = Tenant::where('is_active', true)->get();

        return Inertia::render('Central/Users/Create', [
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:rh,leader',
            'tenant_id' => 'required|exists:tenants,id',
            'hierarchies' => 'array',
            'hierarchies.*.unidade' => 'required_if:role,leader|string',
            'hierarchies.*.setor' => 'required_if:role,leader|string',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        tenancy()->initialize($tenant);

        // Check email uniqueness within tenant
        if (TenantUser::where('email', $request->email)->exists()) {
            tenancy()->end();
            return back()->withErrors(['email' => 'Este e-mail já está em uso neste tenant.']);
        }

        $user = TenantUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        if ($request->role === 'leader' && $request->hierarchies) {
            foreach ($request->hierarchies as $h) {
                $user->hierarchies()->create([
                    'unidade' => $h['unidade'],
                    'setor' => $h['setor'],
                ]);
            }
        }

        tenancy()->end();

        return redirect()->route('central.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(string $tenantId, string $userId): Response
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);
        $user = TenantUser::with('hierarchies')->findOrFail($userId);
        tenancy()->end();

        $tenants = Tenant::where('is_active', true)->get();

        return Inertia::render('Central/Users/Edit', [
            'user' => array_merge($user->toArray(), ['tenant_id' => $tenantId]),
            'tenants' => $tenants,
        ]);
    }

    public function update(Request $request, string $tenantId, string $userId): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:rh,leader',
            'hierarchies' => 'array',
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);

        $user = TenantUser::findOrFail($userId);
        $user->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        if ($request->role === 'leader') {
            $user->hierarchies()->delete();
            foreach ($request->hierarchies ?? [] as $h) {
                $user->hierarchies()->create([
                    'unidade' => $h['unidade'],
                    'setor' => $h['setor'],
                ]);
            }
        }

        tenancy()->end();

        return redirect()->route('central.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(string $tenantId, string $userId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);
        TenantUser::findOrFail($userId)->delete();
        tenancy()->end();

        return redirect()->route('central.users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }

    public function toggleBlock(Request $request, string $tenantId, string $userId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);

        $user = TenantUser::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);

        tenancy()->end();

        $status = $user->is_active ? 'desbloqueado' : 'bloqueado';
        return back()->with('success', "Usuário {$status} com sucesso.");
    }

    public function show(string $tenantId, string $userId): Response
    {
        $tenant = Tenant::findOrFail($tenantId);
        tenancy()->initialize($tenant);
        $user = TenantUser::with('hierarchies')->findOrFail($userId);
        tenancy()->end();

        return Inertia::render('Central/Users/Show', [
            'user' => array_merge($user->toArray(), ['tenant_id' => $tenantId, 'tenant_name' => $tenant->name]),
        ]);
    }
}
