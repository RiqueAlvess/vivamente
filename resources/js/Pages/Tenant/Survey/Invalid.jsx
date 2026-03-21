import { Head } from '@inertiajs/react';

export default function Invalid({ reason }) {
    const messages = {
        link_invalido: {
            icon: '🔗',
            title: 'Link inválido',
            text: 'Este link de pesquisa é inválido, expirou ou a campanha não está mais disponível.',
        },
        ja_respondido: {
            icon: '✅',
            title: 'Pesquisa já respondida',
            text: 'Você já respondeu esta pesquisa anteriormente. Cada link é de uso único.',
        },
    };

    const msg = messages[reason] || messages.link_invalido;

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-700 flex items-center justify-center px-4">
            <Head title="Link Inválido" />
            <div className="max-w-md w-full text-center">
                <div className="bg-white rounded-2xl shadow-2xl p-10">
                    <div className="text-6xl mb-6">{msg.icon}</div>
                    <h1 className="text-2xl font-bold text-gray-900 mb-3">{msg.title}</h1>
                    <p className="text-gray-600">{msg.text}</p>
                    <p className="text-sm text-gray-400 mt-6">
                        Se você acredita que isso é um erro, entre em contato com o setor de RH da sua organização.
                    </p>
                    <div className="mt-8 text-xs text-gray-400">
                        Plataforma NR1 — Gestão de Riscos Psicossociais HSE-IT
                    </div>
                </div>
            </div>
        </div>
    );
}
