import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '../../Layouts/AuthLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <AuthLayout title="Acesso ao Sistema" subtitle="Bem-vindo">
            <Head title="Login" />
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
                <div>
                    <label className="label">Senha</label>
                    <input
                        type="password"
                        className="input"
                        value={data.password}
                        onChange={e => setData('password', e.target.value)}
                        required
                    />
                    {errors.password && <p className="text-red-600 text-xs mt-1">{errors.password}</p>}
                </div>
                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={e => setData('remember', e.target.checked)}
                        />
                        Lembrar-me
                    </label>
                    <a href="/forgot-password" className="text-sm text-primary-600 hover:underline">
                        Esqueci a senha
                    </a>
                </div>
                <button type="submit" disabled={processing} className="btn-primary w-full">
                    {processing ? 'Entrando...' : 'Entrar'}
                </button>
            </form>
        </AuthLayout>
    );
}
