<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Testes de autenticação para o contexto tenant (subdomínio) e central.
 *
 * Estratégia de isolamento de tenancy:
 * - Testes de rotas tenant que exigem DB de tenant usam withoutMiddleware()
 *   para pular InitializeTenancyByDomain e PreventAccessFromCentralDomains,
 *   mantendo apenas o middleware 'web' para que a sessão esteja disponível.
 * - Testes de bloqueio de domínio central mantêm PreventAccessFromCentralDomains
 *   intacto para verificar o comportamento real.
 */
class TenantAuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Sessão em rotas tenant ───────────────────────────────────────────────

    /**
     * POST /login em demo.localhost deve ter sessão disponível.
     * Antes da correção, lançava RuntimeException: "Session store not set on request"
     * porque as rotas tenant não tinham o middleware 'web'.
     */
    public function test_tenant_login_post_has_session_and_does_not_throw_500(): void
    {
        // Ignora middlewares de tenancy (precisam de banco tenant),
        // mantém web (e portanto a sessão) ativo.
        $response = $this->withoutMiddleware([
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ])->post('http://demo.localhost/login', [
            'email' => 'usuario@demo.com',
            'password' => 'senhaerrada',
        ]);

        // Sem sessão → 500. Com sessão → 302 (redirect de volta com erros de validação).
        $response->assertStatus(302);
    }

    /**
     * GET /login em demo.localhost deve retornar 200 (não 500).
     */
    public function test_tenant_login_page_loads_without_500(): void
    {
        $response = $this->withoutMiddleware([
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ])->get('http://demo.localhost/login');

        $response->assertStatus(200);
    }

    // ─── Isolamento de domínio central vs tenant ──────────────────────────────

    /**
     * Rotas que existem apenas em tenant.php devem retornar 403 quando
     * acessadas a partir de localhost (domínio central).
     * Garante que PreventAccessFromCentralDomains está ativo e funcional.
     */
    public function test_central_domain_cannot_access_tenant_only_routes(): void
    {
        // /collaborators só existe em routes/tenant.php
        $response = $this->get('http://localhost/collaborators');
        $response->assertStatus(403);
    }

    /**
     * O domínio central não deve conseguir acessar a rota de surveys do tenant.
     */
    public function test_central_domain_cannot_access_tenant_survey_route(): void
    {
        $response = $this->get('http://localhost/pesquisa/qualquer-token');
        $response->assertStatus(403);
    }

    // ─── Login central (localhost) ────────────────────────────────────────────

    /**
     * Login central com credenciais válidas deve autenticar e redirecionar
     * para o dashboard central.
     */
    public function test_central_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email'          => 'admin@central.com',
            'password'       => Hash::make('senha-segura-123'),
            'role'           => 'global_admin',
            'is_active'      => true,
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        $response = $this->post('http://localhost/login', [
            'email'    => 'admin@central.com',
            'password' => 'senha-segura-123',
        ]);

        $response->assertRedirect(route('central.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Login central com credenciais inválidas deve redirecionar de volta
     * com erros, nunca retornar 500.
     */
    public function test_central_login_with_invalid_credentials_returns_302_not_500(): void
    {
        $response = $this->post('http://localhost/login', [
            'email'    => 'naoexiste@central.com',
            'password' => 'senhaerrada',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /**
     * Login central com senha errada para usuário existente deve incrementar
     * login_attempts no banco de dados.
     */
    public function test_central_login_invalid_password_increments_login_attempts(): void
    {
        $user = User::factory()->create([
            'email'          => 'admin2@central.com',
            'password'       => Hash::make('senha-correta'),
            'role'           => 'global_admin',
            'is_active'      => true,
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        $this->post('http://localhost/login', [
            'email'    => 'admin2@central.com',
            'password' => 'senha-errada',
        ]);

        $this->assertSame(1, $user->fresh()->login_attempts);
    }

    /**
     * Conta central com locked_until no futuro deve rejeitar login mesmo
     * com senha correta.
     */
    public function test_central_login_blocked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email'          => 'bloqueado@central.com',
            'password'       => Hash::make('senha-correta'),
            'role'           => 'global_admin',
            'is_active'      => true,
            'login_attempts' => 5,
            'locked_until'   => now()->addMinutes(10),
        ]);

        $response = $this->post('http://localhost/login', [
            'email'    => 'bloqueado@central.com',
            'password' => 'senha-correta',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── Página de login central ──────────────────────────────────────────────

    /**
     * GET /login em localhost deve retornar 200 (nunca 500).
     */
    public function test_central_login_page_loads(): void
    {
        $response = $this->get('http://localhost/login');
        $response->assertStatus(200);
    }

    /**
     * Rota raiz em localhost sem autenticação deve redirecionar para login.
     */
    public function test_central_root_redirects_unauthenticated(): void
    {
        $response = $this->get('http://localhost/');
        $response->assertRedirect();
    }
}
