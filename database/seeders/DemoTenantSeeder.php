<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Tenant\Collaborator;
use App\Models\Tenant\LeaderHierarchy;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $centralDomain = config('tenancy.central_domains')[0];

        // Create demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'id' => Str::uuid(),
                'name' => 'Empresa Demo',
                'slug' => 'demo',
                'is_active' => true,
            ]
        );

        // Create domain for tenant
        $domain = 'demo.' . $centralDomain;
        $tenant->domains()->firstOrCreate(['domain' => $domain]);

        $this->command->info("Tenant demo criado: {$domain}");

        // Initialize tenant context and create users
        tenancy()->initialize($tenant);

        // Create collaborators (including the hierarchy for the leader)
        $collaborators = [
            ['nome' => 'Ana Silva', 'email' => 'ana.silva@demo.com', 'unidade' => 'Matriz', 'setor' => 'TI', 'cargo' => 'Analista', 'genero' => 'Feminino', 'faixa_etaria' => '25-34'],
            ['nome' => 'Carlos Souza', 'email' => 'carlos.souza@demo.com', 'unidade' => 'Matriz', 'setor' => 'TI', 'cargo' => 'Desenvolvedor', 'genero' => 'Masculino', 'faixa_etaria' => '25-34'],
            ['nome' => 'Mariana Costa', 'email' => 'mariana.costa@demo.com', 'unidade' => 'Matriz', 'setor' => 'Financeiro', 'cargo' => 'Analista Financeiro', 'genero' => 'Feminino', 'faixa_etaria' => '35-44'],
            ['nome' => 'Pedro Lima', 'email' => 'pedro.lima@demo.com', 'unidade' => 'Filial SP', 'setor' => 'Vendas', 'cargo' => 'Vendedor', 'genero' => 'Masculino', 'faixa_etaria' => '18-24'],
            ['nome' => 'Fernanda Rocha', 'email' => 'fernanda.rocha@demo.com', 'unidade' => 'Filial SP', 'setor' => 'Vendas', 'cargo' => 'Coordenador', 'genero' => 'Feminino', 'faixa_etaria' => '35-44'],
        ];

        foreach ($collaborators as $c) {
            Collaborator::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['nome'],
                    'unidade' => $c['unidade'],
                    'setor' => $c['setor'],
                    'cargo' => $c['cargo'],
                    'genero' => $c['genero'],
                    'faixa_etaria' => $c['faixa_etaria'],
                    'is_active' => true,
                ]
            );
        }

        // Create RH user
        TenantUser::firstOrCreate(
            ['email' => 'rh@demo.com'],
            [
                'name' => 'Usuário RH Demo',
                'password' => Hash::make('rh@123'),
                'role' => 'rh',
                'is_active' => true,
            ]
        );

        // Create Leader user
        $leader = TenantUser::firstOrCreate(
            ['email' => 'lider@demo.com'],
            [
                'name' => 'Líder Demo',
                'password' => Hash::make('lider@123'),
                'role' => 'leader',
                'is_active' => true,
            ]
        );

        // Assign hierarchy Matriz/TI to leader
        LeaderHierarchy::firstOrCreate(
            ['user_id' => $leader->id, 'unidade' => 'Matriz', 'setor' => 'TI'],
        );

        tenancy()->end();

        $this->command->info("Tenant demo populado com sucesso.");
        $this->command->info("  RH:    rh@demo.com / rh@123");
        $this->command->info("  Líder: lider@demo.com / lider@123 (Hierarquia: Matriz/TI)");
    }
}
