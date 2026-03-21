import { Head, Link, router } from '@inertiajs/react';
import CentralLayout from '@/Layouts/CentralLayout';

function StatusBadge({ active }) {
    return active ? (
        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Ativo
        </span>
    ) : (
        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            Inativo
        </span>
    );
}

function formatDate(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('pt-BR');
}

export default function Index({ tenants }) {
    function handleDelete(tenant) {
        if (!confirm(`Tem certeza que deseja excluir o tenant "${tenant.name}"?`)) return;
        router.delete(route('central.tenants.destroy', tenant.id));
    }

    return (
        <CentralLayout title="Tenants">
            <Head title="Tenants" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <p className="text-sm text-gray-500">
                        {tenants.data.length} tenant(s) encontrado(s)
                    </p>
                    <Link
                        href={route('central.tenants.create')}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        + Novo Tenant
                    </Link>
                </div>

                {/* Table */}
                <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 border-b border-gray-200">
                                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Nome
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Slug
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Criado em
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {tenants.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-12 text-center text-gray-400">
                                            Nenhum tenant encontrado.
                                        </td>
                                    </tr>
                                ) : (
                                    tenants.data.map((tenant) => (
                                        <tr key={tenant.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4 font-medium text-gray-900">
                                                {tenant.name}
                                            </td>
                                            <td className="px-6 py-4 text-gray-500 font-mono text-xs">
                                                {tenant.slug}
                                            </td>
                                            <td className="px-6 py-4">
                                                <StatusBadge active={tenant.is_active} />
                                            </td>
                                            <td className="px-6 py-4 text-gray-500">
                                                {formatDate(tenant.created_at)}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center justify-end gap-3">
                                                    <Link
                                                        href={route('central.tenants.show', tenant.id)}
                                                        className="text-blue-600 hover:text-blue-800 font-medium transition-colors"
                                                    >
                                                        Ver
                                                    </Link>
                                                    <Link
                                                        href={route('central.tenants.edit', tenant.id)}
                                                        className="text-gray-600 hover:text-gray-900 font-medium transition-colors"
                                                    >
                                                        Editar
                                                    </Link>
                                                    <button
                                                        onClick={() => handleDelete(tenant)}
                                                        className="text-red-600 hover:text-red-800 font-medium transition-colors"
                                                    >
                                                        Excluir
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {tenants.links && tenants.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-gray-100 flex items-center justify-center gap-1">
                            {tenants.links.map((link, index) => (
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 text-sm rounded-md transition-colors ${
                                            link.active
                                                ? 'bg-blue-600 text-white font-semibold'
                                                : 'text-gray-600 hover:bg-gray-100'
                                        }`}
                                    />
                                ) : (
                                    <span
                                        key={index}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className="px-3 py-1.5 text-sm text-gray-400 cursor-default"
                                    />
                                )
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </CentralLayout>
    );
}
