<?php

namespace App\Http\Controllers;

use App\Models\Collaborator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollaboratorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Collaborator::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('email', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->unidade) {
            $query->where('unidade', $request->unidade);
        }

        if ($request->setor) {
            $query->where('setor', $request->setor);
        }

        $collaborators = $query->latest()->paginate(20)->withQueryString();

        $unidades = Collaborator::distinct()->pluck('unidade')->sort()->values();
        $setores = Collaborator::distinct()->pluck('setor')->sort()->values();

        return Inertia::render('Collaborators/Index', [
            'collaborators' => $collaborators,
            'unidades' => $unidades,
            'setores' => $setores,
            'filters' => $request->only(['search', 'unidade', 'setor']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Collaborators/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'unidade' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'genero' => 'nullable|string|max:50',
            'faixa_etaria' => 'nullable|string|max:50',
        ]);

        Collaborator::create($request->only([
            'name', 'email', 'unidade', 'setor', 'cargo', 'genero', 'faixa_etaria',
        ]));

        return redirect()->route('collaborators.index')
            ->with('success', 'Colaborador criado com sucesso.');
    }

    public function show(Collaborator $collaborator): Response
    {
        return Inertia::render('Collaborators/Show', [
            'collaborator' => $collaborator,
        ]);
    }

    public function edit(Collaborator $collaborator): Response
    {
        return Inertia::render('Collaborators/Edit', [
            'collaborator' => $collaborator,
        ]);
    }

    public function update(Request $request, Collaborator $collaborator): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'unidade' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'genero' => 'nullable|string|max:50',
            'faixa_etaria' => 'nullable|string|max:50',
        ]);

        $collaborator->update($request->only([
            'name', 'email', 'unidade', 'setor', 'cargo', 'genero', 'faixa_etaria',
        ]));

        return redirect()->route('collaborators.index')
            ->with('success', 'Colaborador atualizado com sucesso.');
    }

    public function destroy(Collaborator $collaborator): RedirectResponse
    {
        $collaborator->delete();

        return redirect()->route('collaborators.index')
            ->with('success', 'Colaborador removido com sucesso.');
    }
}
