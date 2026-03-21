import { Head, Link } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function Show({ importJob }) {
    const statusLabel = { processando: 'Processando', concluido: 'Concluído', com_erros: 'Com Erros' };
    const statusColor = { processando: 'bg-yellow-100 text-yellow-800', concluido: 'bg-green-100 text-green-800', com_erros: 'bg-red-100 text-red-800' };

    return (
        <TenantLayout title="Detalhe da Importação">
            <Head title="Importação" />
            <div className="max-w-3xl space-y-6">
                <div className="card p-6">
                    <div className="flex justify-between items-start mb-4">
                        <div>
                            <h3 className="font-semibold text-gray-900">{importJob.filename}</h3>
                            <p className="text-sm text-gray-500 mt-1">{new Date(importJob.created_at).toLocaleString('pt-BR')}</p>
                        </div>
                        <span className={`inline-flex px-3 py-1 rounded-full text-sm font-medium ${statusColor[importJob.status]}`}>
                            {statusLabel[importJob.status]}
                        </span>
                    </div>
                    <div className="grid grid-cols-3 gap-4">
                        {[
                            { label: 'Total de Linhas', value: importJob.total_rows, color: 'text-gray-900' },
                            { label: 'Importados', value: importJob.imported_rows, color: 'text-green-700' },
                            { label: 'Com Erro', value: importJob.error_rows, color: 'text-red-700' },
                        ].map(s => (
                            <div key={s.label} className="bg-gray-50 rounded-lg p-4 text-center">
                                <p className={`text-2xl font-bold ${s.color}`}>{s.value}</p>
                                <p className="text-xs text-gray-500 mt-1">{s.label}</p>
                            </div>
                        ))}
                    </div>
                </div>

                {importJob.errors && importJob.errors.length > 0 && (
                    <div className="card overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200 bg-red-50">
                            <h3 className="font-semibold text-red-800">Erros ({importJob.errors.length})</h3>
                        </div>
                        <div className="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            {importJob.errors.map((err, i) => (
                                <div key={i} className="px-6 py-3 flex gap-4 text-sm">
                                    <span className="text-gray-500 min-w-[60px]">Linha {err.row}</span>
                                    <span className="text-red-700">{err.message}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="flex gap-3">
                    <Link href={route('tenant.imports.index')} className="btn-secondary">← Voltar</Link>
                    {importJob.status === 'processando' && (
                        <button onClick={() => window.location.reload()} className="btn-primary">Atualizar Status</button>
                    )}
                </div>
            </div>
        </TenantLayout>
    );
}
