<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use App\Models\LeaderHierarchy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('hierarchies')
            ->where('company_id', Auth::user()->company_id)
            ->latest()
            ->paginate(20);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $hierarchies = $this->getAvailableHierarchies();

        return Inertia::render('Users/Create', [
            'availableHierarchies' => $hierarchies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:rh,leader',
            'hierarchies' => 'array',
            'hierarchies.*.unidade' => 'required_if:role,leader|string',
            'hierarchies.*.setor' => 'required_if:role,leader|string',
        ]);

        $companyId = Auth::user()->company_id;

        $user = User::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        if ($request->role === 'leader' && $request->hierarchies) {
            foreach ($request->hierarchies as $h) {
                LeaderHierarchy::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'unidade' => $h['unidade'],
                    'setor' => $h['setor'],
                ]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function show(User $user): Response
    {
        $user->load('hierarchies');

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('hierarchies');
        $hierarchies = $this->getAvailableHierarchies();

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'availableHierarchies' => $hierarchies,
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

        if ($request->has('password') && $request->password) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

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

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário removido com sucesso.');
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'desbloqueado' : 'bloqueado';
        return back()->with('success', "Usuário {$status} com sucesso.");
    }

    public function getHierarchies(): JsonResponse
    {
        return response()->json($this->getAvailableHierarchies());
    }

    private function getAvailableHierarchies(): array
    {
        return Collaborator::select('unidade', 'setor')
            ->distinct()
            ->orderBy('unidade')
            ->orderBy('setor')
            ->get()
            ->toArray();
    }
}
