<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests para as rotas centrais (painel admin global).
 *
 * Estes testes usam SQLite in-memory. Rotas de autenticação central
 * devem retornar 200 ou redirecionar para login (302), nunca 500.
 *
 * NOTA: Como o projeto usa Supabase/PostgreSQL em produção, estes testes
 * rodam com SQLite apenas para validação estrutural das rotas e controllers.
 * Para validação completa com PostgreSQL, configure DB_* no .env.testing.
 */
class CentralRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Página de login central deve retornar 200.
     */
    public function test_central_login_page_loads(): void
    {
        $response = $this->get('http://localhost/login');
        $response->assertStatus(200);
    }

    /**
     * Rota raiz sem autenticação deve redirecionar para login.
     */
    public function test_central_root_redirects_unauthenticated(): void
    {
        $response = $this->get('http://localhost/');
        // Deve redirecionar (302) para login, não retornar 500
        $response->assertRedirect();
    }

    /**
     * Rota de tenants sem autenticação deve redirecionar para login.
     */
    public function test_central_tenants_index_redirects_unauthenticated(): void
    {
        $response = $this->get('http://localhost/tenants');
        $response->assertRedirect();
    }

    /**
     * Rota de usuários sem autenticação deve redirecionar para login.
     */
    public function test_central_users_index_redirects_unauthenticated(): void
    {
        $response = $this->get('http://localhost/users');
        $response->assertRedirect();
    }

    /**
     * Página de forgot password central deve retornar 200.
     */
    public function test_central_forgot_password_page_loads(): void
    {
        $response = $this->get('http://localhost/forgot-password');
        $response->assertStatus(200);
    }

    /**
     * POST login com credenciais inválidas deve retornar 422 ou redirecionar com erros.
     */
    public function test_central_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('http://localhost/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Deve redirecionar de volta com erros, não retornar 500
        $response->assertStatus(302);
    }
}
