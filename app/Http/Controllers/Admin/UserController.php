<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeaderHierarchy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('company')
            ->whereNotNull('company_id')
            ->latest()
            ->paginate(30);

        $companies = Company::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'companies' => $companies,
        ]);
    }

    public function create(): Response
    {
        $companies = Company::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Users/Create', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:rh,leader',
            'company_id' => 'required|exists:companies,id',
            'hierarchies' => 'array',
            'hierarchies.*.unidade' => 'required_if:role,leader|string',
            'hierarchies.*.setor' => 'required_if:role,leader|string',
        ]);

        $user = User::create([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        if ($request->role === 'leader' && $request->hierarchies) {
            foreach ($request->hierarchies as $h) {
                LeaderHierarchy::create([
                    'company_id' => $request->company_id,
                    'user_id' => $user->id,
                    'unidade' => $h['unidade'],
                    'setor' => $h['setor'],
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function show(User $user): Response
    {
        $user->load(['company', 'hierarchies']);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('hierarchies');
        $companies = Company::where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:rh,leader',
            'hierarchies' => 'array',
        ]);

        $user->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        if ($request->role === 'leader') {
            LeaderHierarchy::where('user_id', $user->id)->delete();
            foreach ($request->hierarchies ?? [] as $h) {
                LeaderHierarchy::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'unidade' => $h['unidade'],
                    'setor' => $h['setor'],
                ]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'desbloqueado' : 'bloqueado';
        return back()->with('success', "Usuário {$status} com sucesso.");
    }
}
