import { Link, usePage, router } from '@inertiajs/react';

export default function TenantLayout({ children, title }) {
    const { auth, flash, tenant } = usePage().props;
    const user = auth?.user;
    const isRh = user?.role === 'rh';

    const nav = [
        { label: 'Dashboard', href: route('tenant.dashboard'), icon: '⊞', show: true },
        { label: 'Colaboradores', href: route('tenant.collaborators.index'), icon: '👤', show: isRh },
        { label: 'Importar CSV', href: route('tenant.imports.index'), icon: '📂', show: isRh },
        { label: 'Campanhas', href: route('tenant.campaigns.index'), icon: '📋', show: isRh },
        { label: 'Usuários', href: route('tenant.users.index'), icon: '👥', show: isRh },
    ].filter(n => n.show);

    function logout() {
        router.post(route('tenant.logout'));
    }

    return (
        <div className="min-h-screen flex bg-gray-50">
            <aside className="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 z-50 lg:static">
                <div className="p-6 border-b border-slate-700">
                    <h1 className="text-xl font-bold text-white">Plataforma NR1</h1>
                    <p className="text-xs text-slate-400 mt-1 truncate">{tenant?.name}</p>
                </div>
                <nav className="flex-1 p-4 space-y-1">
                    {nav.map((item) => (
                        <Link key={item.href} href={item.href}
                            className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                            <span>{item.icon}</span>
                            {item.label}
                        </Link>
                    ))}
                </nav>
                <div className="p-4 border-t border-slate-700">
                    <div className="text-xs text-slate-500 mb-1 capitalize">{user?.role}</div>
                    <div className="text-sm text-slate-400 mb-2 truncate">{user?.email}</div>
                    <button onClick={logout}
                        className="w-full text-left text-sm text-slate-400 hover:text-white transition-colors py-1">
                        Sair
                    </button>
                </div>
            </aside>

            <div className="flex-1 flex flex-col min-w-0">
                <header className="bg-white border-b border-gray-200 px-6 py-4">
                    <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
                </header>

                {flash?.success && (
                    <div className="mx-6 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                        {flash.error}
                    </div>
                )}
                {flash?.info && (
                    <div className="mx-6 mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                        {flash.info}
                    </div>
                )}

                <main className="flex-1 p-6">{children}</main>
            </div>
        </div>
    );
}
