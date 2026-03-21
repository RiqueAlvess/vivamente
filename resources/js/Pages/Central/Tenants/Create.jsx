import { Head, Link, useForm } from '@inertiajs/react';
import CentralLayout from '@/Layouts/CentralLayout';

function slugify(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        slug: '',
    });

    function handleNameChange(e) {
        const name = e.target.value;
        setData((prev) => ({
            ...prev,
            name,
            slug: slugify(name),
        }));
    }

    function handleSlugChange(e) {
        setData('slug', e.target.value);
    }

    function handleSubmit(e) {
        e.preventDefault();
        post(route('central.tenants.store'));
    }

    return (
        <CentralLayout title="Novo Tenant">
            <Head title="Novo Tenant" />

            <div className="max-w-xl">
                <div className="bg-white rounded-xl border border-gray-200 p-6">
                    <h2 className="text-base font-semibold text-gray-900 mb-6">Informações do Tenant</h2>

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
                                onChange={handleNameChange}
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

                        {/* Slug */}
                        <div>
                            <label htmlFor="slug" className="block text-sm font-medium text-gray-700 mb-1">
                                Slug <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="slug"
                                type="text"
                                value={data.slug}
                                onChange={handleSlugChange}
                                placeholder="identificador-unico"
                                className={`w-full px-3 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition ${
                                    errors.slug ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                }`}
                            />
                            {errors.slug ? (
                                <p className="mt-1 text-xs text-red-600">{errors.slug}</p>
                            ) : (
                                <p className="mt-1 text-xs text-gray-500">
                                    Gerado automaticamente a partir do nome. Usado como identificador único.
                                </p>
                            )}
                        </div>

                        {/* Actions */}
                        <div className="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                            >
                                {processing ? 'Criando...' : 'Criar Tenant'}
                            </button>
                            <Link
                                href={route('central.tenants.index')}
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
