import { Head, Link, useForm } from '@inertiajs/react';
import CentralLayout from '@/Layouts/CentralLayout';

export default function Edit({ tenant }) {
    const { data, setData, put, processing, errors } = useForm({
        name: tenant.name ?? '',
        is_active: tenant.is_active ?? true,
    });

    function handleSubmit(e) {
        e.preventDefault();
        put(route('central.tenants.update', tenant.id));
    }

    return (
        <CentralLayout title={`Editar: ${tenant.name}`}>
            <Head title={`Editar: ${tenant.name}`} />

            <div className="max-w-xl">
                {/* Breadcrumb */}
                <div className="flex items-center gap-2 mb-6 text-sm">
                    <Link
                        href={route('central.tenants.index')}
                        className="text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Tenants
                    </Link>
                    <span className="text-gray-300">/</span>
                    <Link
                        href={route('central.tenants.show', tenant.id)}
                        className="text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        {tenant.name}
                    </Link>
                    <span className="text-gray-300">/</span>
                    <span className="text-gray-700 font-medium">Editar</span>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 className="text-base font-semibold text-gray-900 mb-6">Editar Tenant</h2>

                    <form onSubmit={handleSubmit} className="space-y-5">
                        {/* Name */}
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
                                Nome <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Nome da organização"
                                autoFocus
                                className={`w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition ${
                                    errors.name ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                }`}
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">{errors.name}</p>
                            )}
                        </div>

                        {/* Slug (read-only) */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Slug
                            </label>
                            <input
                                type="text"
                                value={tenant.slug}
                                readOnly
                                disabled
                                className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono bg-gray-50 text-gray-500 cursor-not-allowed"
                            />
                            <p className="mt-1 text-xs text-gray-400">O slug não pode ser alterado após a criação.</p>
                        </div>

                        {/* Status */}
                        <div>
                            <span className="block text-sm font-medium text-gray-700 mb-2">Status</span>
                            <div className="flex items-center gap-6">
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="1"
                                        checked={data.is_active === true || data.is_active === 1}
                                        onChange={() => setData('is_active', true)}
                                        className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    />
                                    <span className="text-sm text-gray-700">Ativo</span>
                                </label>
                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="is_active"
                                        value="0"
                                        checked={data.is_active === false || data.is_active === 0}
                                        onChange={() => setData('is_active', false)}
                                        className="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    />
                                    <span className="text-sm text-gray-700">Inativo</span>
                                </label>
                            </div>
                            {errors.is_active && (
                                <p className="mt-1 text-xs text-red-600">{errors.is_active}</p>
                            )}
                        </div>

                        {/* Actions */}
                        <div className="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                            >
                                {processing ? 'Salvando...' : 'Salvar Alterações'}
                            </button>
                            <Link
                                href={route('central.tenants.show', tenant.id)}
                                className="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </CentralLayout>
    );
}
