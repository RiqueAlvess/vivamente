import { Head, Link, router, useForm } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Index({ collaborators, unidades, setores, filters }) {
    const { data, setData, get } = useForm({
        search: filters.search || '',
        unidade: filters.unidade || '',
        setor: filters.setor || '',
    });

    function search(e) {
        e.preventDefault();
        get(route('tenant.collaborators.index'), { preserveState: true });
    }

    function destroy(id) {
        if (confirm('Remover este colaborador?')) {
            router.delete(route('tenant.collaborators.destroy', id));
        }
    }

    return (
        <TenantLayout title="Colaboradores">
            <Head title="Colaboradores" />
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-sm text-gray-600">{collaborators.total} colaborador(es)</h3>
                <div className="flex gap-2">
                    <Link href={route('tenant.imports.index')} className="btn-secondary text-sm">Importar CSV</Link>
                    <Link href={route('tenant.collaborators.create')} className="btn-primary text-sm">+ Novo</Link>
                </div>
            </div>

            <form onSubmit={search} className="card p-4 mb-4 flex gap-3 flex-wrap">
                <input className="input max-w-xs" placeholder="Buscar nome ou e-mail..." value={data.search}
                    onChange={e => setData('search', e.target.value)} />
                <select className="input max-w-xs" value={data.unidade} onChange={e => setData('unidade', e.target.value)}>
                    <option value="">Todas as Unidades</option>
                    {unidades.map(u => <option key={u} value={u}>{u}</option>)}
                </select>
                <select className="input max-w-xs" value={data.setor} onChange={e => setData('setor', e.target.value)}>
                    <option value="">Todos os Setores</option>
                    {setores.map(s => <option key={s} value={s}>{s}</option>)}
                </select>
                <button type="submit" className="btn-primary text-sm">Filtrar</button>
            </form>

            <div className="card overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            {['Nome', 'E-mail', 'Unidade', 'Setor', 'Cargo', 'Status', 'Ações'].map(h => (
                                <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-100">
                        {collaborators.data.length === 0 ? (
                            <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-500">Nenhum colaborador encontrado.</td></tr>
                        ) : collaborators.data.map(c => (
                            <tr key={c.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium">{c.name}</td>
                                <td className="px-4 py-3 text-gray-600">{c.email}</td>
                                <td className="px-4 py-3">{c.unidade}</td>
                                <td className="px-4 py-3">{c.setor}</td>
                                <td className="px-4 py-3">{c.cargo}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${c.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}`}>
                                        {c.is_active ? 'Ativo' : 'Inativo'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 space-x-2">
                                    <Link href={route('tenant.collaborators.edit', c.id)} className="text-xs text-primary-600 hover:underline">Editar</Link>
                                    <button onClick={() => destroy(c.id)} className="text-xs text-red-600 hover:underline">Remover</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {collaborators.links && (
                <div className="mt-4 flex gap-1">
                    {collaborators.links.map((link, i) => (
                        <button key={i} disabled={!link.url}
                            onClick={() => link.url && router.get(link.url)}
                            className={`px-3 py-1 text-sm rounded ${link.active ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'} disabled:opacity-40`}
                            dangerouslySetInnerHTML={{ __html: link.label }} />
                    ))}
                </div>
            )}
        </TenantLayout>
    );
}
