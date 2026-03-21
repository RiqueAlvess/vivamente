import { Head, Link, useForm } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Index({ imports }) {
    const { data, setData, post, processing, errors, progress } = useForm({ file: null });

    function submit(e) {
        e.preventDefault();
        post(route('tenant.imports.store'), { forceFormData: true });
    }

    const statusLabel = { processando: 'Processando', concluido: 'Concluído', com_erros: 'Com Erros' };
    const statusColor = { processando: 'bg-yellow-100 text-yellow-800', concluido: 'bg-green-100 text-green-800', com_erros: 'bg-red-100 text-red-800' };

    return (
        <TenantLayout title="Importar Colaboradores">
            <Head title="Importações" />
            <div className="max-w-3xl space-y-6">
                <div className="card p-6">
                    <h3 className="font-semibold text-gray-900 mb-2">Importar CSV</h3>
                    <p className="text-sm text-gray-600 mb-4">
                        O arquivo CSV deve conter as colunas: <code className="bg-gray-100 px-1 rounded">nome, email, unidade, setor, cargo, genero, faixa_etaria</code>
                    </p>
                    <form onSubmit={submit} className="flex items-end gap-3">
                        <div className="flex-1">
                            <label className="label">Arquivo CSV</label>
                            <input type="file" accept=".csv,.txt"
                                onChange={e => setData('file', e.target.files[0])}
                                className="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                            {errors.file && <p className="text-red-600 text-xs mt-1">{errors.file}</p>}
                        </div>
                        <button type="submit" disabled={processing || !data.file} className="btn-primary">
                            {processing ? 'Enviando...' : 'Enviar'}
                        </button>
                    </form>
                    {progress && (
                        <div className="mt-2 bg-gray-200 rounded-full h-2">
                            <div className="bg-primary-600 h-2 rounded-full transition-all" style={{ width: `${progress.percentage}%` }} />
                        </div>
                    )}
                </div>

                <div className="card overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="font-semibold text-gray-900">Histórico de Importações</h3>
                    </div>
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                        <thead className="bg-gray-50">
                            <tr>
                                {['Arquivo', 'Status', 'Total', 'Importados', 'Erros', 'Data', 'Detalhe'].map(h => (
                                    <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-100">
                            {imports.data.length === 0 ? (
                                <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-500">Nenhuma importação encontrada.</td></tr>
                            ) : imports.data.map(imp => (
                                <tr key={imp.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-3 font-medium truncate max-w-xs">{imp.filename}</td>
                                    <td className="px-4 py-3">
                                        <span className={`inline-flex px-2 py-0.5 rounded text-xs font-medium ${statusColor[imp.status]}`}>
                                            {statusLabel[imp.status]}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-center">{imp.total_rows}</td>
                                    <td className="px-4 py-3 text-center text-green-700">{imp.imported_rows}</td>
                                    <td className="px-4 py-3 text-center text-red-700">{imp.error_rows}</td>
                                    <td className="px-4 py-3 text-gray-500 text-xs">{new Date(imp.created_at).toLocaleString('pt-BR')}</td>
                                    <td className="px-4 py-3">
                                        <Link href={route('tenant.imports.show', imp.id)} className="text-xs text-primary-600 hover:underline">Ver</Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </TenantLayout>
    );
}
