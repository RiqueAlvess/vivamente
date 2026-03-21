import { Head, Link, router } from '@inertiajs/react';
import CentralLayout from '@/Layouts/CentralLayout';

function DetailRow({ label, children }) {
    return (
        <div className="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4 py-4 border-b border-gray-100 last:border-0">
            <dt className="w-40 flex-shrink-0 text-sm font-medium text-gray-500">{label}</dt>
            <dd className="text-sm text-gray-900 flex-1">{children}</dd>
        </div>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="bg-gray-50 rounded-lg px-5 py-4">
            <p className="text-xs font-medium text-gray-500 uppercase tracking-wide">{label}</p>
            <p className="text-2xl font-bold text-gray-900 mt-1">{value ?? 0}</p>
        </div>
    );
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Show({ tenant, stats }) {
    function handleDelete() {
        if (!confirm(`Tem certeza que deseja excluir o tenant "${tenant.name}"? Esta ação não pode ser desfeita.`)) return;
        router.delete(route('central.tenants.destroy', tenant.id));
    }

    return (
        <CentralLayout title={tenant.name}>
            <Head title={tenant.name} />

            <div className="space-y-6 max-w-3xl">
                {/* Breadcrumb + actions */}
                <div className="flex items-center gap-3">
                    <Link
                        href={route('central.tenants.index')}
                        className="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        &larr; Tenants
                    </Link>
                    <span className="text-gray-300">/</span>
                    <span className="text-sm text-gray-700 font-medium">{tenant.name}</span>
                    <div className="ml-auto flex items-center gap-2">
                        <Link
                            href={route('central.tenants.edit', tenant.id)}
                            className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Editar
                        </Link>
                        <button
                            onClick={handleDelete}
                            className="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Excluir
                        </button>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-2 gap-4">
                    <StatCard label="Usuários" value={stats?.user_count} />
                    <StatCard label="Colaboradores" value={stats?.collaborator_count} />
                </div>

                {/* Details */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="text-base font-semibold text-gray-900 mb-2">Detalhes</h3>
                    <dl>
                        <DetailRow label="Nome">
                            <span className="font-medium">{tenant.name}</span>
                        </DetailRow>

                        <DetailRow label="Slug">
                            <code className="text-xs bg-gray-100 px-2 py-0.5 rounded">{tenant.slug}</code>
                        </DetailRow>

                        <DetailRow label="Status">
                            {tenant.is_active ? (
                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            ) : (
                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            )}
                        </DetailRow>

                        <DetailRow label="Criado em">
                            {formatDate(tenant.created_at)}
                        </DetailRow>

                        <DetailRow label="Atualizado em">
                            {formatDate(tenant.updated_at)}
                        </DetailRow>
                    </dl>
                </div>

                {/* Domains */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="text-base font-semibold text-gray-900 mb-4">Domínios</h3>
                    {tenant.domains && tenant.domains.length > 0 ? (
                        <ul className="space-y-2">
                            {tenant.domains.map((domain) => (
                                <li key={domain.id ?? domain.domain} className="flex items-center gap-2">
                                    <span className="w-2 h-2 rounded-full bg-green-500 flex-shrink-0" />
                                    <span className="text-sm font-mono text-gray-700">{domain.domain}</span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-gray-400">Nenhum domínio configurado.</p>
                    )}
                </div>
            </div>
        </CentralLayout>
    );
}
