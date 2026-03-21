import { Head, useForm } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Edit({ campaign }) {
    const { data, setData, put, processing, errors } = useForm({
        name: campaign.name,
        description: campaign.description || '',
        starts_at: campaign.starts_at ? campaign.starts_at.substring(0, 16) : '',
        ends_at: campaign.ends_at ? campaign.ends_at.substring(0, 16) : '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('tenant.campaigns.update', campaign.id));
    }

    return (
        <TenantLayout title="Editar Campanha">
            <Head title="Editar Campanha" />
            <div className="max-w-2xl">
                <form onSubmit={submit} className="card p-6 space-y-4">
                    <div>
                        <label className="label">Nome da Campanha *</label>
                        <input className="input" value={data.name} onChange={e => setData('name', e.target.value)} required />
                        {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                    </div>
                    <div>
                        <label className="label">Descrição</label>
                        <textarea className="input" rows={4} value={data.description}
                            onChange={e => setData('description', e.target.value)} />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="label">Início</label>
                            <input type="datetime-local" className="input" value={data.starts_at}
                                onChange={e => setData('starts_at', e.target.value)} />
                        </div>
                        <div>
                            <label className="label">Término</label>
                            <input type="datetime-local" className="input" value={data.ends_at}
                                onChange={e => setData('ends_at', e.target.value)} />
                        </div>
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <a href={route('tenant.campaigns.show', campaign.id)} className="btn-secondary">Cancelar</a>
                        <button type="submit" disabled={processing} className="btn-primary">
                            {processing ? 'Salvando...' : 'Salvar Alterações'}
                        </button>
                    </div>
                </form>
            </div>
        </TenantLayout>
    );
}
