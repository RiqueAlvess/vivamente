import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import CentralLayout from '../../../Layouts/CentralLayout';

export default function Edit({ user, tenants }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        role: user.role,
        hierarchies: user.hierarchies || [],
    });

    const [newHierarchy, setNewHierarchy] = useState({ unidade: '', setor: '' });

    function addHierarchy() {
        if (!newHierarchy.unidade || !newHierarchy.setor) return;
        const exists = data.hierarchies.some(h => h.unidade === newHierarchy.unidade && h.setor === newHierarchy.setor);
        if (!exists) setData('hierarchies', [...data.hierarchies, { ...newHierarchy }]);
        setNewHierarchy({ unidade: '', setor: '' });
    }

    function removeHierarchy(idx) {
        setData('hierarchies', data.hierarchies.filter((_, i) => i !== idx));
    }

    function submit(e) {
        e.preventDefault();
        put(route('central.users.update', [user.tenant_id, user.id]));
    }

    const tenantName = tenants.find(t => t.id === user.tenant_id)?.name || user.tenant_id;

    return (
        <CentralLayout title="Editar Usuário">
            <Head title="Editar Usuário" />
            <div className="max-w-2xl">
                <form onSubmit={submit} className="card p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <label className="label">Nome</label>
                            <input className="input" value={data.name} onChange={e => setData('name', e.target.value)} required />
                            {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                        </div>
                        <div className="col-span-2">
                            <label className="label">E-mail</label>
                            <input type="email" className="input bg-gray-50 text-gray-500" value={user.email} disabled />
                        </div>
                        <div>
                            <label className="label">Role</label>
                            <select className="input" value={data.role} onChange={e => setData('role', e.target.value)}>
                                <option value="rh">RH</option>
                                <option value="leader">Líder</option>
                            </select>
                        </div>
                        <div>
                            <label className="label">Tenant</label>
                            <input className="input bg-gray-50 text-gray-500" value={tenantName} disabled />
                        </div>
                    </div>

                    {data.role === 'leader' && (
                        <div>
                            <label className="label">Hierarquias (Unidade / Setor)</label>
                            <div className="space-y-2 mb-3">
                                {data.hierarchies.map((h, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-purple-50 border border-purple-200 px-3 py-2 rounded-lg text-sm">
                                        <span className="font-medium text-purple-800">{h.unidade}</span>
                                        <span className="text-gray-400">/</span>
                                        <span className="text-purple-700">{h.setor}</span>
                                        <button type="button" onClick={() => removeHierarchy(i)} className="ml-auto text-red-500 text-xs">✕</button>
                                    </div>
                                ))}
                            </div>
                            <div className="flex gap-2">
                                <input className="input" placeholder="Unidade" value={newHierarchy.unidade}
                                    onChange={e => setNewHierarchy(h => ({ ...h, unidade: e.target.value }))} />
                                <input className="input" placeholder="Setor" value={newHierarchy.setor}
                                    onChange={e => setNewHierarchy(h => ({ ...h, setor: e.target.value }))} />
                                <button type="button" onClick={addHierarchy} className="btn-secondary whitespace-nowrap">+ Adicionar</button>
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end gap-3 pt-2">
                        <a href={route('central.users.index')} className="btn-secondary">Cancelar</a>
                        <button type="submit" disabled={processing} className="btn-primary">
                            {processing ? 'Salvando...' : 'Salvar'}
                        </button>
                    </div>
                </form>
            </div>
        </CentralLayout>
    );
}
