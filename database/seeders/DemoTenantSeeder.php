<?php

namespace Database\Seeders;

use App\Models\Collaborator;
use App\Models\Company;
use App\Models\LeaderHierarchy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Criar empresa demo
        $company = Company::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Empresa Demo',
                'slug' => 'demo',
                'is_active' => true,
            ]
        );

        $this->command->info("Empresa demo criada: {$company->name} (ID: {$company->id})");

        // Criar colaboradores
        $collaborators = [
            ['nome' => 'Ana Silva',       'email' => 'ana.silva@demo.com',       'unidade' => 'Matriz',    'setor' => 'TI',         'cargo' => 'Analista',            'genero' => 'Feminino',  'faixa_etaria' => '25-34'],
            ['nome' => 'Carlos Souza',    'email' => 'carlos.souza@demo.com',    'unidade' => 'Matriz',    'setor' => 'TI',         'cargo' => 'Desenvolvedor',       'genero' => 'Masculino', 'faixa_etaria' => '25-34'],
            ['nome' => 'Mariana Costa',   'email' => 'mariana.costa@demo.com',   'unidade' => 'Matriz',    'setor' => 'Financeiro', 'cargo' => 'Analista Financeiro', 'genero' => 'Feminino',  'faixa_etaria' => '35-44'],
            ['nome' => 'Pedro Lima',      'email' => 'pedro.lima@demo.com',      'unidade' => 'Filial SP', 'setor' => 'Vendas',     'cargo' => 'Vendedor',            'genero' => 'Masculino', 'faixa_etaria' => '18-24'],
            ['nome' => 'Fernanda Rocha',  'email' => 'fernanda.rocha@demo.com',  'unidade' => 'Filial SP', 'setor' => 'Vendas',     'cargo' => 'Coordenador',         'genero' => 'Feminino',  'faixa_etaria' => '35-44'],
        ];

        foreach ($collaborators as $c) {
            Collaborator::firstOrCreate(
                ['company_id' => $company->id, 'email' => $c['email']],
                [
                    'company_id' => $company->id,
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

        // Criar usuário RH
        User::firstOrCreate(
            ['email' => 'rh@demo.com'],
            [
                'company_id' => $company->id,
                'name' => 'Usuário RH Demo',
                'password' => Hash::make('rh@123'),
                'role' => 'rh',
                'is_active' => true,
            ]
        );

        // Criar usuário Líder
        $leader = User::firstOrCreate(
            ['email' => 'lider@demo.com'],
            [
                'company_id' => $company->id,
                'name' => 'Líder Demo',
                'password' => Hash::make('lider@123'),
                'role' => 'leader',
                'is_active' => true,
            ]
        );

        // Atribuir hierarquia Matriz/TI ao líder
        LeaderHierarchy::firstOrCreate(
            ['user_id' => $leader->id, 'unidade' => 'Matriz', 'setor' => 'TI'],
            ['company_id' => $company->id]
        );

        $this->command->info("Empresa demo populada com sucesso.");
        $this->command->info("  RH:    rh@demo.com / rh@123");
        $this->command->info("  Líder: lider@demo.com / lider@123 (Hierarquia: Matriz/TI)");
    }
}
