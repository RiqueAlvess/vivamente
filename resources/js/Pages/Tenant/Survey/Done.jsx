import { Head } from '@inertiajs/react';

export default function Done() {
    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 to-green-900 flex items-center justify-center px-4">
            <Head title="Pesquisa Concluída" />
            <div className="max-w-md w-full text-center">
                <div className="bg-white rounded-2xl shadow-2xl p-10">
                    <div className="text-6xl mb-6">✅</div>
                    <h1 className="text-2xl font-bold text-gray-900 mb-3">Pesquisa concluída!</h1>
                    <p className="text-gray-600 mb-2">Obrigado pela sua participação.</p>
                    <p className="text-sm text-gray-500">
                        Suas respostas foram registradas com sucesso e são completamente anônimas.
                        Elas ajudarão a melhorar as condições de trabalho na sua organização.
                    </p>
                    <div className="mt-8 text-xs text-gray-400">
                        Plataforma NR1 — Gestão de Riscos Psicossociais HSE-IT
                    </div>
                </div>
            </div>
        </div>
    );
}
