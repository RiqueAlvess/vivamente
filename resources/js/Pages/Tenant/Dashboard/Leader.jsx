import { router } from '@inertiajs/react';
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
} from 'chart.js';
import { Radar, Doughnut } from 'react-chartjs-2';

Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend, ArcElement);

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

export default function LeaderDashboard({
    campaigns = [],
    hierarchies = [],
    stats = {},
    chartData = null,
    selectedCampaign = null,
}) {
    function handleCampaignChange(e) {
        const val = e.target.value;
        if (val) {
            router.get(route('tenant.dashboard'), { campaign: val }, { preserveState: false });
        } else {
            router.get(route('tenant.dashboard'), {}, { preserveState: false });
        }
    }

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
                      borderColor: [
                          '#16a34a',
                          '#d97706',
                          '#dc2626',
                          '#2563eb',
                      ],
                      borderWidth: 1,
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

    const selectedId = typeof selectedCampaign === 'object' && selectedCampaign !== null
        ? String(selectedCampaign.id ?? selectedCampaign)
        : String(selectedCampaign ?? '');

    return (
        <TenantLayout title="Dashboard Líder">
            {/* Campaign selector */}
            <div className="mb-6 flex flex-wrap items-center gap-4">
                <label htmlFor="campaign-select" className="text-sm font-medium text-gray-700">
                    Campanha:
                </label>
                <select
                    id="campaign-select"
                    className="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    value={selectedId}
                    onChange={handleCampaignChange}
                >
                    <option value="">Todas as campanhas</option>
                    {campaigns.map((c) => (
                        <option key={c.id} value={String(c.id)}>
                            {c.name}
                        </option>
                    ))}
                </select>

                {selectedCampaign && typeof selectedCampaign === 'object' && (
                    <span
                        className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${
                            selectedCampaign.status === 'ativa'
                                ? 'bg-green-100 text-green-700 ring-green-200'
                                : selectedCampaign.status === 'encerrada'
                                ? 'bg-slate-100 text-slate-700 ring-slate-200'
                                : 'bg-gray-100 text-gray-600 ring-gray-200'
                        }`}
                    >
                        {selectedCampaign.status}
                    </span>
                )}
            </div>

            {/* Hierarchies section */}
            {hierarchies.length > 0 && (
                <section className="mb-8">
                    <h2 className="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Minhas Hierarquias
                    </h2>
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {hierarchies.map((h, i) => (
                            <div
                                key={i}
                                className="flex flex-col rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 shadow-sm"
                            >
                                <span className="text-xs font-semibold uppercase tracking-wide text-indigo-400">
                                    Unidade
                                </span>
                                <span className="mt-0.5 text-sm font-semibold text-indigo-900">
                                    {h.unidade ?? '—'}
                                </span>
                                <span className="mt-2 text-xs font-semibold uppercase tracking-wide text-indigo-400">
                                    Setor
                                </span>
                                <span className="mt-0.5 text-sm text-indigo-800">
                                    {h.setor ?? '—'}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* Stat cards */}
            <div className="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
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
            ) : (
                <div className="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center text-sm text-gray-500">
                    Nenhum dado de gráfico disponível para a seleção atual.
                </div>
            )}
        </TenantLayout>
    );
}
