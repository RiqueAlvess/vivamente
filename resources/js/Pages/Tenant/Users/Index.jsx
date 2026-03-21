import { Head, Link, router } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Index({ users }) {
    function toggleBlock(id) {
        if (confirm('Alterar status do usuário?')) {
            router.post(route('tenant.users.toggle-block', id));
        }
    }

    function destroy(id) {
        if (confirm('Remover este usuário?')) {
            router.delete(route('tenant.users.destroy', id));
        }
    }

    return (
        <TenantLayout title="Usuários">
            <Head title="Usuários" />
            <div className="flex justify-between items-center mb-6">
                <p className="text-sm text-gray-600">{users.total} usuário(s)</p>
                <Link href={route('tenant.users.create')} className="btn-primary text-sm">+ Novo Usuário</Link>
            </div>
            <div className="card overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            {['Nome', 'E-mail', 'Role', 'Status', 'Hierarquias', 'Ações'].map(h => (
                                <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-100">
                        {users.data.length === 0 ? (
                            <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-500">Nenhum usuário.</td></tr>
                        ) : users.data.map(u => (
                            <tr key={u.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium">{u.name}</td>
                                <td className="px-4 py-3 text-gray-600">{u.email}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${u.role === 'rh' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}`}>
                                        {u.role === 'rh' ? 'RH' : 'Líder'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${u.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                        {u.is_active ? 'Ativo' : 'Bloqueado'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-xs text-gray-500">
                                    {u.hierarchies?.length > 0
                                        ? u.hierarchies.map(h => `${h.unidade}/${h.setor}`).join(', ')
                                        : '—'}
                                </td>
                                <td className="px-4 py-3 space-x-2">
                                    <Link href={route('tenant.users.edit', u.id)} className="text-xs text-primary-600 hover:underline">Editar</Link>
                                    <button onClick={() => toggleBlock(u.id)} className="text-xs text-amber-600 hover:underline">
                                        {u.is_active ? 'Bloquear' : 'Desbloquear'}
                                    </button>
                                    <button onClick={() => destroy(u.id)} className="text-xs text-red-600 hover:underline">Remover</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </TenantLayout>
    );
}
