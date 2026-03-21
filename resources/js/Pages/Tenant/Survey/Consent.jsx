import { Head, useForm } from '@inertiajs/react';

export default function Consent({ token, campaign }) {
    const { data, setData, post, processing, errors } = useForm({ accepted: false });

    function submit(e) {
        e.preventDefault();
        post(`/pesquisa/${token}/consent`);
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 to-primary-900 flex items-center justify-center px-4 py-12">
            <Head title="Pesquisa HSE-IT — Consentimento" />
            <div className="max-w-2xl w-full">
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-white">Plataforma NR1</h1>
                    <p className="text-slate-300 mt-2">Pesquisa de Saúde e Bem-Estar no Trabalho</p>
                </div>
                <div className="bg-white rounded-2xl shadow-2xl p-8">
                    <h2 className="text-xl font-semibold text-gray-900 mb-2">{campaign.name}</h2>
                    <div className="text-sm text-gray-600 mb-6 space-y-3">
                        <p className="font-semibold text-gray-800">Termo de Consentimento Livre e Esclarecido (LGPD)</p>
                        <p>Esta pesquisa tem como objetivo avaliar as condições psicossociais de trabalho com base no modelo HSE-IT (Health and Safety Executive — Indicadores de Trabalho). Suas respostas são totalmente <strong>confidenciais e anônimas</strong>.</p>
                        <p>Os dados coletados serão utilizados exclusivamente para fins de diagnóstico organizacional e melhoria das condições de trabalho. Nenhuma resposta individual será identificada ou divulgada.</p>
                        <p>A participação é voluntária. Ao prosseguir, você concorda com a coleta e tratamento dos seus dados conforme descrito, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).</p>
                        <p>Esta pesquisa leva aproximadamente <strong>5 a 10 minutos</strong> para ser concluída.</p>
                    </div>
                    <form onSubmit={submit}>
                        <label className="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer hover:border-primary-400 transition-colors"
                            style={{ borderColor: data.accepted ? '#0284c7' : '#e5e7eb' }}>
                            <input type="checkbox" className="mt-0.5 w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                checked={data.accepted}
                                onChange={e => setData('accepted', e.target.checked)} />
                            <span className="text-sm text-gray-700">
                                Li e concordo com os termos acima. Autorizo o uso dos meus dados para os fins descritos.
                            </span>
                        </label>
                        {errors.accepted && <p className="text-red-600 text-xs mt-2">{errors.accepted}</p>}
                        <button type="submit" disabled={!data.accepted || processing}
                            className="btn-primary w-full mt-6 py-3 text-base">
                            {processing ? 'Carregando...' : 'Concordar e Iniciar Pesquisa →'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
