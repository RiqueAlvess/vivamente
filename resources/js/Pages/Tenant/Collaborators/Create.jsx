import { Head, useForm } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '', email: '', unidade: '', setor: '', cargo: '', genero: '', faixa_etaria: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('tenant.collaborators.store'));
    }

    return (
        <TenantLayout title="Novo Colaborador">
            <Head title="Novo Colaborador" />
            <div className="max-w-2xl">
                <form onSubmit={submit} className="card p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <label className="label">Nome *</label>
                            <input className="input" value={data.name} onChange={e => setData('name', e.target.value)} required />
                            {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                        </div>
                        <div className="col-span-2">
                            <label className="label">E-mail *</label>
                            <input type="email" className="input" value={data.email} onChange={e => setData('email', e.target.value)} required />
                            {errors.email && <p className="text-red-600 text-xs mt-1">{errors.email}</p>}
                        </div>
                        <div>
                            <label className="label">Unidade *</label>
                            <input className="input" value={data.unidade} onChange={e => setData('unidade', e.target.value)} required />
                            {errors.unidade && <p className="text-red-600 text-xs mt-1">{errors.unidade}</p>}
                        </div>
                        <div>
                            <label className="label">Setor *</label>
                            <input className="input" value={data.setor} onChange={e => setData('setor', e.target.value)} required />
                            {errors.setor && <p className="text-red-600 text-xs mt-1">{errors.setor}</p>}
                        </div>
                        <div>
                            <label className="label">Cargo *</label>
                            <input className="input" value={data.cargo} onChange={e => setData('cargo', e.target.value)} required />
                            {errors.cargo && <p className="text-red-600 text-xs mt-1">{errors.cargo}</p>}
                        </div>
                        <div>
                            <label className="label">Gênero</label>
                            <select className="input" value={data.genero} onChange={e => setData('genero', e.target.value)}>
                                <option value="">Não informado</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                                <option value="Não-binário">Não-binário</option>
                                <option value="Prefiro não dizer">Prefiro não dizer</option>
                            </select>
                        </div>
                        <div>
                            <label className="label">Faixa Etária</label>
                            <select className="input" value={data.faixa_etaria} onChange={e => setData('faixa_etaria', e.target.value)}>
                                <option value="">Não informado</option>
                                <option value="18-24">18-24</option>
                                <option value="25-34">25-34</option>
                                <option value="35-44">35-44</option>
                                <option value="45-54">45-54</option>
                                <option value="55+">55+</option>
                            </select>
                        </div>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <a href={route('tenant.collaborators.index')} className="btn-secondary">Cancelar</a>
                        <button type="submit" disabled={processing} className="btn-primary">
                            {processing ? 'Salvando...' : 'Criar Colaborador'}
                        </button>
                    </div>
                </form>
            </div>
        </TenantLayout>
    );
}
