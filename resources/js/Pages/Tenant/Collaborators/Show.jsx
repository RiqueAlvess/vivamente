import { Head, Link } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Show({ collaborator }) {
    return (
        <TenantLayout title="Detalhes do Colaborador">
            <Head title="Colaborador" />
            <div className="max-w-2xl">
                <div className="card p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <p className="label">Nome</p>
                            <p className="text-gray-900 font-medium">{collaborator.name}</p>
                        </div>
                        <div className="col-span-2">
                            <p className="label">E-mail</p>
                            <p className="text-gray-700">{collaborator.email}</p>
                        </div>
                        <div>
                            <p className="label">Unidade</p>
                            <p className="text-gray-700">{collaborator.unidade}</p>
                        </div>
                        <div>
                            <p className="label">Setor</p>
                            <p className="text-gray-700">{collaborator.setor}</p>
                        </div>
                        <div>
                            <p className="label">Cargo</p>
                            <p className="text-gray-700">{collaborator.cargo}</p>
                        </div>
                        <div>
                            <p className="label">Gênero</p>
                            <p className="text-gray-700">{collaborator.genero || '—'}</p>
                        </div>
                        <div>
                            <p className="label">Faixa Etária</p>
                            <p className="text-gray-700">{collaborator.faixa_etaria || '—'}</p>
                        </div>
                        <div>
                            <p className="label">Status</p>
                            <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${collaborator.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}`}>
                                {collaborator.is_active ? 'Ativo' : 'Inativo'}
                            </span>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 pt-4 border-t">
                        <Link href={route('tenant.collaborators.index')} className="btn-secondary">
                            Voltar
                        </Link>
                        <Link href={route('tenant.collaborators.edit', collaborator.id)} className="btn-primary">
                            Editar
                        </Link>
                    </div>
                </div>
            </div>
        </TenantLayout>
    );
}
