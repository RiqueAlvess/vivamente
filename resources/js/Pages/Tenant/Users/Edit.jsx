import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Edit({ user, availableHierarchies }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        role: user.role,
        password: '',
        password_confirmation: '',
        hierarchies: user.hierarchies || [],
    });
    const [newH, setNewH] = useState({ unidade: '', setor: '' });

    const unidades = [...new Set(availableHierarchies.map(h => h.unidade))];
    const setoresByUnidade = newH.unidade
        ? availableHierarchies.filter(h => h.unidade === newH.unidade).map(h => h.setor)
        : [];

    function addHierarchy() {
        if (!newH.unidade || !newH.setor) return;
        const exists = data.hierarchies.some(h => h.unidade === newH.unidade && h.setor === newH.setor);
        if (!exists) setData('hierarchies', [...data.hierarchies, { ...newH }]);
        setNewH({ unidade: '', setor: '' });
    }

    function removeHierarchy(idx) {
        setData('hierarchies', data.hierarchies.filter((_, i) => i !== idx));
    }

    function submit(e) {
        e.preventDefault();
        put(route('tenant.users.update', user.id));
    }

    return (
        <TenantLayout title="Editar Usuário">
            <Head title="Editar Usuário" />
            <div className="max-w-2xl">
                <form onSubmit={submit} className="card p-6 space-y-4">
                    <div>
                        <label className="label">Nome</label>
                        <input className="input" value={data.name} onChange={e => setData('name', e.target.value)} required />
                        {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                    </div>
                    <div>
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
                    <div className="border-t pt-4">
                        <p className="text-sm text-gray-500 mb-3">Nova senha (deixe em branco para manter a atual)</p>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="label">Nova Senha</label>
                                <input type="password" className="input" value={data.password} onChange={e => setData('password', e.target.value)} />
                            </div>
                            <div>
                                <label className="label">Confirmar</label>
                                <input type="password" className="input" value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} />
                            </div>
                        </div>
                    </div>

                    {data.role === 'leader' && (
                        <div>
                            <label className="label">Hierarquias</label>
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
                                <select className="input" value={newH.unidade}
                                    onChange={e => setNewH(h => ({ ...h, unidade: e.target.value, setor: '' }))}>
                                    <option value="">Unidade...</option>
                                    {unidades.map(u => <option key={u} value={u}>{u}</option>)}
                                </select>
                                <select className="input" value={newH.setor}
                                    onChange={e => setNewH(h => ({ ...h, setor: e.target.value }))}>
                                    <option value="">Setor...</option>
                                    {setoresByUnidade.map(s => <option key={s} value={s}>{s}</option>)}
                                </select>
                                <button type="button" onClick={addHierarchy} className="btn-secondary whitespace-nowrap">+ Adicionar</button>
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end gap-3 pt-2">
                        <a href={route('tenant.users.index')} className="btn-secondary">Cancelar</a>
                        <button type="submit" disabled={processing} className="btn-primary">
                            {processing ? 'Salvando...' : 'Salvar'}
                        </button>
                    </div>
                </form>
            </div>
        </TenantLayout>
    );
}
