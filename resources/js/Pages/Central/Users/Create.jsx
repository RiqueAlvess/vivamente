import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import CentralLayout from '../../../Layouts/CentralLayout';

export default function Create({ tenants }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'rh',
        tenant_id: '',
        hierarchies: [],
    });

    const [newHierarchy, setNewHierarchy] = useState({ unidade: '', setor: '' });

    function addHierarchy() {
        if (!newHierarchy.unidade || !newHierarchy.setor) return;
        setData('hierarchies', [...data.hierarchies, { ...newHierarchy }]);
        setNewHierarchy({ unidade: '', setor: '' });
    }

    function removeHierarchy(idx) {
        setData('hierarchies', data.hierarchies.filter((_, i) => i !== idx));
    }

    function submit(e) {
        e.preventDefault();
        post(route('central.users.store'));
    }

    return (
        <CentralLayout title="Novo Usuário">
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
                            {errors.password && <p className="text-red-600 text-xs mt-1">{errors.password}</p>}
                        </div>
                        <div>
                            <label className="label">Confirmar Senha</label>
                            <input type="password" className="input" value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} required />
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
                            <select className="input" value={data.tenant_id} onChange={e => setData('tenant_id', e.target.value)} required>
                                <option value="">Selecione...</option>
                                {tenants.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                            </select>
                            {errors.tenant_id && <p className="text-red-600 text-xs mt-1">{errors.tenant_id}</p>}
                        </div>
                    </div>

                    {data.role === 'leader' && (
                        <div>
                            <label className="label">Hierarquias (Unidade / Setor)</label>
                            <div className="space-y-2 mb-3">
                                {data.hierarchies.map((h, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg text-sm">
                                        <span className="font-medium">{h.unidade}</span>
                                        <span className="text-gray-400">/</span>
                                        <span>{h.setor}</span>
                                        <button type="button" onClick={() => removeHierarchy(i)} className="ml-auto text-red-500 text-xs hover:underline">Remover</button>
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
                            {processing ? 'Salvando...' : 'Criar Usuário'}
                        </button>
                    </div>
                </form>
            </div>
        </CentralLayout>
    );
}
