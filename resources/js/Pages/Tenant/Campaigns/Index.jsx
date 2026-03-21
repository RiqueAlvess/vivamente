import { Link } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

const STATUS_BADGE = {
    rascunho:  'bg-gray-100 text-gray-700 ring-gray-200',
    ativa:     'bg-green-100 text-green-700 ring-green-200',
    encerrada: 'bg-slate-100 text-slate-700 ring-slate-200',
};

const STATUS_LABEL = {
    rascunho:  'Rascunho',
    ativa:     'Ativa',
    encerrada: 'Encerrada',
};

function StatusBadge({ status }) {
    const cls = STATUS_BADGE[status] ?? 'bg-gray-100 text-gray-700 ring-gray-200';
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${cls}`}
        >
            {STATUS_LABEL[status] ?? status}
        </span>
    );
}

export default function CampaignsIndex({ campaigns = [] }) {
    return (
        <TenantLayout title="Campanhas">
            {/* Toolbar */}
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <p className="text-sm text-gray-500">
                    {campaigns.length}{' '}
                    campanha{campaigns.length !== 1 ? 's' : ''}{' '}
                    encontrada{campaigns.length !== 1 ? 's' : ''}
                </p>
                <Link
                    href={route('tenant.campaigns.create')}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                >
                    + Nova Campanha
                </Link>
            </div>

            {/* Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Nome
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Status
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Total Convites
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Respondidos
                            </th>
                            <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 bg-white">
                        {campaigns.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={5}
                                    className="px-6 py-12 text-center text-sm text-gray-400"
                                >
                                    Nenhuma campanha encontrada.{' '}
                                    <Link
                                        href={route('tenant.campaigns.create')}
                                        className="font-medium text-indigo-600 hover:underline"
                                    >
                                        Crie a primeira campanha.
                                    </Link>
                                </td>
                            </tr>
                        ) : (
                            campaigns.map((campaign) => (
                                <tr
                                    key={campaign.id}
                                    className="hover:bg-gray-50 transition-colors"
                                >
                                    <td className="px-6 py-4">
                                        <Link
                                            href={route('tenant.campaigns.show', campaign.id)}
                                            className="font-medium text-gray-900 hover:text-indigo-600"
                                        >
                                            {campaign.name}
                                        </Link>
                                        {campaign.description && (
                                            <p className="mt-0.5 text-xs text-gray-400 line-clamp-1">
                                                {campaign.description}
                                            </p>
                                        )}
                                    </td>
                                    <td className="px-6 py-4">
                                        <StatusBadge status={campaign.status} />
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm text-gray-700">
                                        {campaign.total_invites ?? 0}
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm text-gray-700">
                                        {campaign.responded ?? 0}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            <Link
                                                href={route('tenant.campaigns.show', campaign.id)}
                                                className="rounded-md px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors"
                                            >
                                                Ver
                                            </Link>
                                            <Link
                                                href={route('tenant.campaigns.edit', campaign.id)}
                                                className="rounded-md px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                                            >
                                                Editar
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </TenantLayout>
    );
}
