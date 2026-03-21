import { Head, Link } from '@inertiajs/react';
import CentralLayout from '@/Layouts/CentralLayout';

function StatCard({ label, value, color }) {
    const colors = {
        blue: 'bg-blue-50 border-blue-200 text-blue-700',
        green: 'bg-green-50 border-green-200 text-green-700',
        purple: 'bg-purple-50 border-purple-200 text-purple-700',
    };

    return (
        <div className={`border rounded-xl p-6 ${colors[color] ?? colors.blue}`}>
            <p className="text-sm font-medium opacity-75">{label}</p>
            <p className="text-4xl font-bold mt-2">{value ?? 0}</p>
        </div>
    );
}

export default function Dashboard({ stats }) {
    return (
        <CentralLayout title="Dashboard">
            <Head title="Dashboard" />

            <div className="space-y-8">
                {/* Stats grid */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <StatCard
                        label="Total de Tenants"
                        value={stats?.total_tenants}
                        color="blue"
                    />
                    <StatCard
                        label="Tenants Ativos"
                        value={stats?.active_tenants}
                        color="green"
                    />
                    <StatCard
                        label="Total de Usuários"
                        value={stats?.total_users}
                        color="purple"
                    />
                </div>

                {/* Quick links */}
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 className="text-base font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                    <div className="flex flex-wrap gap-3">
                        <Link
                            href={route('central.tenants.create')}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            + Novo Tenant
                        </Link>
                        <Link
                            href={route('central.users.index')}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Ver Usuários
                        </Link>
                        <Link
                            href={route('central.tenants.index')}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Ver Tenants
                        </Link>
                    </div>
                </div>
            </div>
        </CentralLayout>
    );
}
