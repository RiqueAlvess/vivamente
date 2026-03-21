export default function AuthLayout({ children, title, subtitle }) {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 to-primary-900 px-4">
            <div className="w-full max-w-md">
                <div className="text-center mb-8">
                    <h1 className="text-3xl font-bold text-white">Plataforma NR1</h1>
                    {subtitle && <p className="text-slate-300 mt-2 text-sm">{subtitle}</p>}
                </div>
                <div className="bg-white rounded-2xl shadow-2xl p-8">
                    {title && <h2 className="text-xl font-semibold text-gray-900 mb-6">{title}</h2>}
                    {children}
                </div>
            </div>
        </div>
    );
}
