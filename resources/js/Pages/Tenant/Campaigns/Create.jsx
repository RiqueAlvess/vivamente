import { Link, useForm } from '@inertiajs/react';
import TenantLayout from '../../../Layouts/TenantLayout';

export default function CampaignCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        starts_at: '',
        ends_at: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post(route('tenant.campaigns.store'));
    }

    return (
        <TenantLayout title="Nova Campanha">
            <div className="mx-auto max-w-2xl">
                {/* Back link */}
                <div className="mb-6">
                    <Link
                        href={route('tenant.campaigns.index')}
                        className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 transition-colors"
                    >
                        &larr; Voltar para campanhas
                    </Link>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                    <h2 className="mb-6 text-lg font-semibold text-gray-900">
                        Criar Nova Campanha
                    </h2>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Name */}
                        <div>
                            <label
                                htmlFor="name"
                                className="block text-sm font-medium text-gray-700"
                            >
                                Nome <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className={`mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                    errors.name
                                        ? 'border-red-400 focus:ring-red-400'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Ex.: Campanha NR-1 2025"
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        {/* Description */}
                        <div>
                            <label
                                htmlFor="description"
                                className="block text-sm font-medium text-gray-700"
                            >
                                Descrição
                            </label>
                            <textarea
                                id="description"
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className={`mt-1 block w-full resize-none rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                    errors.description
                                        ? 'border-red-400 focus:ring-red-400'
                                        : 'border-gray-300'
                                }`}
                                placeholder="Descreva o objetivo desta campanha..."
                            />
                            {errors.description && (
                                <p className="mt-1 text-xs text-red-600">{errors.description}</p>
                            )}
                        </div>

                        {/* Date range */}
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    htmlFor="starts_at"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Data de Início
                                </label>
                                <input
                                    id="starts_at"
                                    type="date"
                                    value={data.starts_at}
                                    onChange={(e) => setData('starts_at', e.target.value)}
                                    className={`mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                        errors.starts_at
                                            ? 'border-red-400 focus:ring-red-400'
                                            : 'border-gray-300'
                                    }`}
                                />
                                {errors.starts_at && (
                                    <p className="mt-1 text-xs text-red-600">{errors.starts_at}</p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="ends_at"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Data de Encerramento
                                </label>
                                <input
                                    id="ends_at"
                                    type="date"
                                    value={data.ends_at}
                                    onChange={(e) => setData('ends_at', e.target.value)}
                                    className={`mt-1 block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                                        errors.ends_at
                                            ? 'border-red-400 focus:ring-red-400'
                                            : 'border-gray-300'
                                    }`}
                                />
                                {errors.ends_at && (
                                    <p className="mt-1 text-xs text-red-600">{errors.ends_at}</p>
                                )}
                            </div>
                        </div>

                        {/* Form actions */}
                        <div className="flex items-center justify-end gap-3 pt-2">
                            <Link
                                href={route('tenant.campaigns.index')}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
                            >
                                {processing ? 'Salvando...' : 'Criar Campanha'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </TenantLayout>
    );
}
