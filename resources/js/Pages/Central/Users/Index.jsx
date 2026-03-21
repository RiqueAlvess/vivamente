import { Head, Link, router } from '@inertiajs/react';
import CentralLayout from '../../../Layouts/CentralLayout';

export default function Index({ users, tenants }) {
    function toggleBlock(tenantId, userId) {
        if (confirm('Alterar status do usuário?')) {
            router.post(`/users/${tenantId}/${userId}/toggle-block`);
        }
    }

    function destroy(tenantId, userId) {
        if (confirm('Remover este usuário?')) {
            router.delete(`/users/${tenantId}/${userId}`);
        }
    }

    return (
        <CentralLayout title="Usuários do Sistema">
            <Head title="Usuários" />
            <div className="flex justify-between items-center mb-6">
                <p className="text-sm text-gray-600">{users.length} usuário(s) encontrado(s)</p>
                <Link href={route('central.users.create')} className="btn-primary">
                    + Novo Usuário
                </Link>
            </div>
            <div className="card overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            {['Nome', 'E-mail', 'Role', 'Tenant', 'Status', 'Ações'].map(h => (
                                <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-100">
                        {users.length === 0 ? (
                            <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-500">Nenhum usuário encontrado.</td></tr>
                        ) : users.map((user) => (
                            <tr key={`${user.tenant_id}-${user.id}`} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium text-gray-900">{user.name}</td>
                                <td className="px-4 py-3 text-gray-600">{user.email}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${user.role === 'rh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}`}>
                                        {user.role === 'rh' ? 'RH' : 'Líder'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-gray-600">{user.tenant_name}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                        {user.is_active ? 'Ativo' : 'Bloqueado'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 space-x-2">
                                    <button onClick={() => toggleBlock(user.tenant_id, user.id)}
                                        className="text-xs text-amber-600 hover:underline">
                                        {user.is_active ? 'Bloquear' : 'Desbloquear'}
                                    </button>
                                    <button onClick={() => destroy(user.tenant_id, user.id)}
                                        className="text-xs text-red-600 hover:underline">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </CentralLayout>
    );
}
