import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../../Layouts/AuthLayout';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    function submit(e) {
        e.preventDefault();
        post('/forgot-password');
    }

    return (
        <AuthLayout title="Recuperar Senha" subtitle="Painel do Administrador Global">
            <Head title="Recuperar Senha" />

            {status && (
                <div className="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                    {status}
                </div>
            )}

            <p className="text-sm text-gray-600 mb-6">
                Informe seu e-mail cadastrado e enviaremos um link para redefinir sua senha.
            </p>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <label className="label">E-mail</label>
                    <input
                        type="email"
                        className="input"
                        value={data.email}
                        onChange={e => setData('email', e.target.value)}
                        required
                        autoFocus
                    />
                    {errors.email && <p className="text-red-600 text-xs mt-1">{errors.email}</p>}
                </div>

                <button type="submit" disabled={processing} className="btn-primary w-full">
                    {processing ? 'Enviando...' : 'Enviar link de recuperação'}
                </button>

                <div className="text-center">
                    <a href="/login" className="text-sm text-primary-600 hover:underline">
                        Voltar ao login
                    </a>
                </div>
            </form>
        </AuthLayout>
    );
}
