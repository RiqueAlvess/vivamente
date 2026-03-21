import { Link, router } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';
import {
    Chart,
    RadialLinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';
import { Radar, Doughnut, Bar } from 'react-chartjs-2';

Chart.register(
    RadialLinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
);

/* ------------------------------------------------------------------ */
/* Helpers                                                              */
/* ------------------------------------------------------------------ */

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
            className={`inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 ring-inset ${cls}`}
        >
            {STATUS_LABEL[status] ?? status}
        </span>
    );
}

function StatCard({ label, value, sub, color = 'indigo' }) {
    const colors = {
        indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        green:  'bg-green-50  text-green-700  border-green-200',
        amber:  'bg-amber-50  text-amber-700  border-amber-200',
        red:    'bg-red-50    text-red-700    border-red-200',
        blue:   'bg-blue-50   text-blue-700   border-blue-200',
        slate:  'bg-slate-50  text-slate-700  border-slate-200',
    };
    return (
        <div className={`rounded-xl border p-5 ${colors[color] ?? colors.indigo}`}>
            <p className="text-xs font-semibold uppercase tracking-wide opacity-70">{label}</p>
            <p className="mt-1 text-3xl font-bold">{value ?? '—'}</p>
            {sub && <p className="mt-1 text-xs opacity-60">{sub}</p>}
        </div>
    );
}

function InfoRow({ label, value }) {
    return (
        <div className="flex justify-between py-2.5 text-sm border-b border-gray-100 last:border-0">
            <span className="font-medium text-gray-500">{label}</span>
            <span className="text-gray-900">{value ?? '—'}</span>
        </div>
    );
}

/* ------------------------------------------------------------------ */
/* Action buttons                                                       */
/* ------------------------------------------------------------------ */

function ActionButton({ label, onClick, color = 'gray', disabled = false }) {
    const colors = {
        green:  'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        red:    'bg-red-600   hover:bg-red-700   text-white focus:ring-red-500',
        blue:   'bg-blue-600  hover:bg-blue-700  text-white focus:ring-blue-500',
        indigo: 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
        gray:   'bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 focus:ring-indigo-500',
    };
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            className={`inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${colors[color]}`}
        >
            {label}
        </button>
    );
}

/* ------------------------------------------------------------------ */
/* Main component                                                       */
/* ------------------------------------------------------------------ */

export default function CampaignShow({ campaign, stats = {}, chartData = null }) {
    function activate() {
        if (confirm('Deseja ativar esta campanha?')) {
            router.post(route('tenant.campaigns.activate', campaign.id));
        }
    }

    function close() {
        if (confirm('Deseja encerrar esta campanha? Esta ação não pode ser desfeita.')) {
            router.post(route('tenant.campaigns.close', campaign.id));
        }
    }

    function sendInvites() {
        if (confirm('Deseja enviar os convites para todos os colaboradores desta campanha?')) {
            router.post(route('tenant.campaigns.send-invites', campaign.id));
        }
    }

    /* Chart datasets */
    const radarData = chartData?.radar
        ? {
              labels: chartData.radar.labels ?? [],
              datasets: [
                  {
                      label: 'IGRP por Dimensão',
                      data: chartData.radar.data ?? [],
                      backgroundColor: 'rgba(99, 102, 241, 0.2)',
                      borderColor: 'rgba(99, 102, 241, 1)',
                      borderWidth: 2,
                      pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                  },
              ],
          }
        : null;

    const doughnutData = chartData?.doughnut
        ? {
              labels: chartData.doughnut.labels ?? [],
              datasets: [
                  {
                      data: chartData.doughnut.data ?? [],
                      backgroundColor: [
                          'rgba(34, 197, 94, 0.8)',
                          'rgba(251, 191, 36, 0.8)',
                          'rgba(239, 68, 68, 0.8)',
                          'rgba(59, 130, 246, 0.8)',
                      ],
                      borderColor: ['#16a34a', '#d97706', '#dc2626', '#2563eb'],
                      borderWidth: 1,
                  },
              ],
          }
        : null;

    const barData = chartData?.bar
        ? {
              labels: chartData.bar.labels ?? [],
              datasets: [
                  {
                      label: chartData.bar.dataset_label ?? 'Colaboradores',
                      data: chartData.bar.data ?? [],
                      backgroundColor: 'rgba(99, 102, 241, 0.75)',
                      borderColor: 'rgba(99, 102, 241, 1)',
                      borderWidth: 1,
                      borderRadius: 4,
                  },
              ],
          }
        : null;

    const radarOptions = {
        maintainAspectRatio: false,
        scales: {
            r: {
                beginAtZero: true,
                max: 100,
                ticks: { stepSize: 20, font: { size: 10 } },
                pointLabels: { font: { size: 11 } },
            },
        },
        plugins: { legend: { display: false } },
    };

    const doughnutOptions = {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 } } },
        },
        cutout: '65%',
    };

    const barOptions = {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                beginAtZero: true,
                ticks: { precision: 0 },
            },
        },
    };

    const isRascunho  = campaign.status === 'rascunho';
    const isAtiva     = campaign.status === 'ativa';
    const isEncerrada = campaign.status === 'encerrada';

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        return new Date(dateStr).toLocaleDateString('pt-BR');
    }

    return (
        <TenantLayout title={campaign.name}>
            {/* Back link */}
            <div className="mb-6">
                <Link
                    href={route('tenant.campaigns.index')}
                    className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 transition-colors"
                >
                    &larr; Voltar para campanhas
                </Link>
            </div>

            {/* Campaign header card */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex-1 min-w-0">
                        <div className="flex flex-wrap items-center gap-3 mb-2">
                            <h1 className="text-xl font-bold text-gray-900 truncate">
                                {campaign.name}
                            </h1>
                            <StatusBadge status={campaign.status} />
                        </div>
                        {campaign.description && (
                            <p className="text-sm text-gray-500 mt-1">{campaign.description}</p>
                        )}
                    </div>

                    {/* Action buttons */}
                    <div className="flex flex-wrap items-center gap-2 shrink-0">
                        {isRascunho && (
                            <ActionButton
                                label="Ativar Campanha"
                                onClick={activate}
                                color="green"
                            />
                        )}
                        {isAtiva && (
                            <>
                                <ActionButton
                                    label="Enviar Convites"
                                    onClick={sendInvites}
                                    color="blue"
                                />
                                <ActionButton
                                    label="Encerrar Campanha"
                                    onClick={close}
                                    color="red"
                                />
                            </>
                        )}
                        {!isEncerrada && (
                            <Link
                                href={route('tenant.campaigns.edit', campaign.id)}
                                className="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Editar
                            </Link>
                        )}
                    </div>
                </div>

                {/* Campaign metadata */}
                <div className="mt-6 grid grid-cols-1 gap-x-8 gap-y-0 divide-y divide-gray-100 sm:grid-cols-2 sm:divide-y-0 sm:[&>*:nth-child(n)]:border-b sm:[&>*:nth-child(n)]:border-gray-100">
                    <div className="py-2.5 sm:py-0 sm:border-b sm:border-gray-100">
                        <InfoRow label="Data de Início" value={formatDate(campaign.starts_at)} />
                    </div>
                    <div className="py-2.5 sm:py-0 sm:border-b sm:border-gray-100">
                        <InfoRow label="Data de Encerramento" value={formatDate(campaign.ends_at)} />
                    </div>
                </div>
            </div>

            {/* Stat cards */}
            <div className="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
                <StatCard
                    label="Total Convites"
                    value={stats.total_invites ?? 0}
                    color="indigo"
                />
                <StatCard
                    label="Respondidos"
                    value={stats.responded ?? 0}
                    sub={stats.total_invites != null ? `de ${stats.total_invites}` : undefined}
                    color="blue"
                />
                <StatCard
                    label="Adesão"
                    value={stats.adesao != null ? `${stats.adesao}%` : '—'}
                    color="green"
                />
                <StatCard
                    label="IGRP"
                    value={stats.igrp ?? '—'}
                    sub="Índice Geral de Risco Psicossocial"
                    color="amber"
                />
                <StatCard
                    label="% Risco Alto"
                    value={stats.perc_risco_alto != null ? `${stats.perc_risco_alto}%` : '—'}
                    color="red"
                />
                <StatCard
                    label="Classificação"
                    value={stats.risco_classificacao ?? '—'}
                    color="slate"
                />
            </div>

            {/* Charts */}
            {chartData ? (
                <div className="space-y-6">
                    {/* Top row: Radar + Doughnut */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {radarData && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-sm font-semibold text-gray-700">
                                    IGRP por Dimensão
                                </h3>
                                <div className="relative h-72">
                                    <Radar data={radarData} options={radarOptions} />
                                </div>
                            </div>
                        )}

                        {doughnutData && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-sm font-semibold text-gray-700">
                                    Distribuição de Risco
                                </h3>
                                <div className="relative h-72">
                                    <Doughnut data={doughnutData} options={doughnutOptions} />
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Bottom row: Bar chart (full width) */}
                    {barData && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-sm font-semibold text-gray-700">
                                {chartData.bar?.title ?? 'Distribuição por Categoria'}
                            </h3>
                            <div className="relative h-64">
                                <Bar data={barData} options={barOptions} />
                            </div>
                        </div>
                    )}
                </div>
            ) : (
                <div className="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-sm text-gray-500">
                    {stats.responded > 0
                        ? 'Processando dados para gráficos...'
                        : 'Nenhuma resposta registrada ainda. Os gráficos aparecerão após as primeiras respostas.'}
                </div>
            )}
        </TenantLayout>
    );
}
