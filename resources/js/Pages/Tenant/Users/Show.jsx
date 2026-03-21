import { Head, Link } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Show({ user }) {
    return (
        <TenantLayout title="Detalhes do Usuário">
            <Head title="Usuário" />
            <div className="max-w-2xl">
                <div className="card p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <p className="label">Nome</p>
                            <p className="text-gray-900 font-medium">{user.name}</p>
                        </div>
                        <div className="col-span-2">
                            <p className="label">E-mail</p>
                            <p className="text-gray-700">{user.email}</p>
                        </div>
                        <div>
                            <p className="label">Role</p>
                            <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${user.role === 'rh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}`}>
                                {user.role === 'rh' ? 'RH' : 'Líder'}
                            </span>
                        </div>
                        <div>
                            <p className="label">Status</p>
                            <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                {user.is_active ? 'Ativo' : 'Bloqueado'}
                            </span>
                        </div>
                    </div>

                    {user.role === 'leader' && user.hierarchies?.length > 0 && (
                        <div>
                            <p className="label mb-2">Hierarquias</p>
                            <div className="space-y-2">
                                {user.hierarchies.map((h, i) => (
                                    <div key={i} className="flex items-center gap-2 bg-purple-50 border border-purple-200 px-3 py-2 rounded-lg text-sm">
                                        <span className="font-medium text-purple-800">{h.unidade}</span>
                                        <span className="text-gray-400">/</span>
                                        <span className="text-purple-700">{h.setor}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end gap-3 pt-4 border-t">
                        <Link href={route('tenant.users.index')} className="btn-secondary">
                            Voltar
                        </Link>
                        <Link href={route('tenant.users.edit', user.id)} className="btn-primary">
                            Editar
                        </Link>
                    </div>
                </div>
            </div>
        </TenantLayout>
    );
}
