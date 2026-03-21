import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Create({ availableHierarchies }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '', email: '', password: '', password_confirmation: '',
        role: 'rh', hierarchies: [],
    });
    const [newH, setNewH] = useState({ unidade: '', setor: '' });

    function addHierarchy() {
        if (!newH.unidade || !newH.setor) return;
        const exists = data.hierarchies.some(h => h.unidade === newH.unidade && h.setor === newH.setor);
        if (!exists) setData('hierarchies', [...data.hierarchies, { ...newH }]);
        setNewH({ unidade: '', setor: '' });
    }

    function removeHierarchy(idx) {
        setData('hierarchies', data.hierarchies.filter((_, i) => i !== idx));
    }

    const unidades = [...new Set(availableHierarchies.map(h => h.unidade))];
    const setoresByUnidade = newH.unidade
        ? availableHierarchies.filter(h => h.unidade === newH.unidade).map(h => h.setor)
        : [];

    function submit(e) {
        e.preventDefault();
        post(route('tenant.users.store'));
    }

    return (
        <TenantLayout title="Novo Usuário">
            <Head title="Novo Usuário" />
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
                            <input type="email" className="input" value={data.email} onChange={e => setData('email', e.target.value)} required />
                            {errors.email && <p className="text-red-600 text-xs mt-1">{errors.email}</p>}
                        </div>
                        <div>
                            <label className="label">Senha</label>
                            <input type="password" className="input" value={data.password} onChange={e => setData('password', e.target.value)} required />
                        </div>
                        <div>
                            <label className="label">Confirmar Senha</label>
                            <input type="password" className="input" value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} required />
                        </div>
                        <div className="col-span-2">
                            <label className="label">Role</label>
                            <select className="input" value={data.role} onChange={e => setData('role', e.target.value)}>
                                <option value="rh">RH</option>
                                <option value="leader">Líder</option>
                            </select>
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
                            {availableHierarchies.length === 0 && (
                                <p className="text-xs text-amber-600 mt-2">Importe colaboradores primeiro para que as hierarquias apareçam aqui.</p>
                            )}
                        </div>
                    )}

                    <div className="flex justify-end gap-3 pt-2">
                        <a href={route('tenant.users.index')} className="btn-secondary">Cancelar</a>
                        <button type="submit" disabled={processing} className="btn-primary">
                            {processing ? 'Salvando...' : 'Criar Usuário'}
                        </button>
                    </div>
                </form>
            </div>
        </TenantLayout>
    );
}
