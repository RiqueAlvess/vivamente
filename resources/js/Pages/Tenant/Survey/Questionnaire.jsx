import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

const SCALE = [
    { value: 0, label: 'Nunca' },
    { value: 1, label: 'Raramente' },
    { value: 2, label: 'Às vezes' },
    { value: 3, label: 'Frequentemente' },
    { value: 4, label: 'Sempre' },
];

const DIMENSION_LABELS = {
    demandas: 'Demandas',
    controle: 'Controle',
    apoio_chefia: 'Apoio da Chefia',
    apoio_colegas: 'Apoio dos Colegas',
    relacionamentos: 'Relacionamentos',
    cargo_funcao: 'Cargo e Função',
    comunicacao_mudancas: 'Comunicação e Mudanças',
};

export default function Questionnaire({ token, questions, collaborator }) {
    const initialAnswers = {};
    questions.forEach(q => { initialAnswers[`q${q.id}`] = ''; });

    const { data, setData, post, processing, errors } = useForm({
        consent_given: true,
        genero: collaborator.genero || '',
        faixa_etaria: collaborator.faixa_etaria || '',
        ...initialAnswers,
    });

    const [currentDimension, setCurrentDimension] = useState(0);

    const dimensions = [...new Set(questions.map(q => q.dimension))];
    const currentDimKey = dimensions[currentDimension];
    const currentQuestions = questions.filter(q => q.dimension === currentDimKey);

    const answered = currentQuestions.filter(q => data[`q${q.id}`] !== '').length;
    const totalAnswered = questions.filter(q => data[`q${q.id}`] !== '').length;
    const progress = Math.round((totalAnswered / questions.length) * 100);

    const canGoNext = answered === currentQuestions.length;
    const isLastDimension = currentDimension === dimensions.length - 1;

    function submit(e) {
        e.preventDefault();
        post(`/pesquisa/${token}/submit`);
    }

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title="Pesquisa HSE-IT" />
            {/* Header */}
            <div className="bg-slate-900 text-white px-4 py-4">
                <div className="max-w-2xl mx-auto flex items-center justify-between">
                    <h1 className="text-lg font-semibold">Pesquisa HSE-IT</h1>
                    <span className="text-sm text-slate-300">{totalAnswered}/{questions.length} respostas</span>
                </div>
                {/* Progress bar */}
                <div className="max-w-2xl mx-auto mt-3 bg-slate-700 rounded-full h-2">
                    <div className="bg-primary-400 h-2 rounded-full transition-all" style={{ width: `${progress}%` }} />
                </div>
            </div>

            <div className="max-w-2xl mx-auto px-4 py-8">
                {/* Demographic section (shown at start) */}
                {currentDimension === 0 && (
                    <div className="card p-6 mb-6">
                        <h3 className="font-semibold text-gray-900 mb-4">Dados Demográficos (opcional)</h3>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="label">Gênero</label>
                                <select className="input" value={data.genero} onChange={e => setData('genero', e.target.value)}>
                                    <option value="">Prefiro não informar</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                    <option value="Não-binário">Não-binário</option>
                                </select>
                            </div>
                            <div>
                                <label className="label">Faixa Etária</label>
                                <select className="input" value={data.faixa_etaria} onChange={e => setData('faixa_etaria', e.target.value)}>
                                    <option value="">Prefiro não informar</option>
                                    <option value="18-24">18-24</option>
                                    <option value="25-34">25-34</option>
                                    <option value="35-44">35-44</option>
                                    <option value="45-54">45-54</option>
                                    <option value="55+">55+</option>
                                </select>
                            </div>
                        </div>
                    </div>
                )}

                {/* Dimension header */}
                <div className="mb-6">
                    <div className="flex items-center gap-2 mb-1">
                        <span className="text-xs text-gray-500">Dimensão {currentDimension + 1} de {dimensions.length}</span>
                    </div>
                    <h2 className="text-xl font-bold text-gray-900">{DIMENSION_LABELS[currentDimKey]}</h2>
                </div>

                {/* Questions */}
                <form onSubmit={submit} className="space-y-6">
                    {currentQuestions.map((q) => (
                        <div key={q.id} className="card p-6">
                            <p className="text-gray-800 mb-4 font-medium">{q.id}. {q.text}</p>
                            <div className="grid grid-cols-5 gap-2">
                                {SCALE.map((option) => {
                                    const selected = data[`q${q.id}`] == option.value;
                                    return (
                                        <button key={option.value} type="button"
                                            onClick={() => setData(`q${q.id}`, option.value)}
                                            className={`py-2 px-1 rounded-lg text-xs font-medium border-2 transition-all ${selected
                                                ? 'border-primary-500 bg-primary-50 text-primary-700'
                                                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'}`}>
                                            <div className="text-lg mb-1">{option.value}</div>
                                            <div className="leading-tight">{option.label}</div>
                                        </button>
                                    );
                                })}
                            </div>
                            {errors[`q${q.id}`] && <p className="text-red-600 text-xs mt-2">{errors[`q${q.id}`]}</p>}
                        </div>
                    ))}

                    <div className="flex justify-between pt-4">
                        <button type="button" disabled={currentDimension === 0}
                            onClick={() => setCurrentDimension(d => d - 1)}
                            className="btn-secondary disabled:opacity-40">
                            ← Anterior
                        </button>

                        {isLastDimension ? (
                            <button type="submit" disabled={!canGoNext || processing}
                                className="btn-primary disabled:opacity-40">
                                {processing ? 'Enviando...' : 'Enviar Pesquisa ✓'}
                            </button>
                        ) : (
                            <button type="button" disabled={!canGoNext}
                                onClick={() => setCurrentDimension(d => d + 1)}
                                className="btn-primary disabled:opacity-40">
                                Próxima →
                            </button>
                        )}
                    </div>
                </form>
            </div>
        </div>
    );
}
