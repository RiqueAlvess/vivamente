import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../../Layouts/AuthLayout';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors } = useForm({
        token: token,
        email: email ?? '',
        password: '',
        password_confirmation: '',
    });

    function submit(e) {
        e.preventDefault();
        post('/reset-password');
    }

    return (
        <AuthLayout title="Redefinir Senha" subtitle="Painel do Administrador Global">
            <Head title="Redefinir Senha" />

            <form onSubmit={submit} className="space-y-4">
                <input type="hidden" value={data.token} />

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

                <div>
                    <label className="label">Nova senha</label>
                    <input
                        type="password"
                        className="input"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        required
                    />
                    {errors.password && <p className="text-red-600 text-xs mt-1">{errors.password}</p>}
                </div>

                <div>
                    <label className="label">Confirmar nova senha</label>
                    <input
                        type="password"
                        className="input"
                        value={data.password_confirmation}
                        onChange={e => setData('password_confirmation', e.target.value)}
                        required
                    />
                    {errors.password_confirmation && (
                        <p className="text-red-600 text-xs mt-1">{errors.password_confirmation}</p>
                    )}
                </div>

                <button type="submit" disabled={processing} className="btn-primary w-full">
                    {processing ? 'Redefinindo...' : 'Redefinir senha'}
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
