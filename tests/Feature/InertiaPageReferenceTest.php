<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Verifica que todas as páginas referenciadas por Inertia::render() existem
 * como arquivos JSX em resources/js/Pages/.
 *
 * Este teste é puramente estático (analisa o código-fonte PHP) e não requer
 * conexão com banco de dados. Evita que páginas ausentes causem erros em runtime.
 */
class InertiaPageReferenceTest extends TestCase
{
    private string $controllersPath;
    private string $pagesPath;

    protected function setUp(): void
    {
        $this->controllersPath = __DIR__ . '/../../app/Http/Controllers';
        $this->pagesPath = __DIR__ . '/../../resources/js/Pages';
    }

    /**
     * Extrai todas as chamadas Inertia::render('...') dos controllers PHP
     * e verifica que o arquivo JSX correspondente existe.
     */
    public function test_all_inertia_render_pages_exist(): void
    {
        $missing = [];
        $found = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->controllersPath)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            preg_match_all("/Inertia::render\(['\"]([^'\"]+)['\"]/", $content, $matches);

            foreach ($matches[1] as $page) {
                $found++;
                $jsxPath = $this->pagesPath . '/' . $page . '.jsx';
                if (!file_exists($jsxPath)) {
                    $missing[] = sprintf(
                        '  Controller: %s → página ausente: %s.jsx',
                        str_replace($this->controllersPath . '/', '', $file->getPathname()),
                        $page
                    );
                }
            }
        }

        $this->assertGreaterThan(0, $found, 'Nenhuma chamada Inertia::render() encontrada nos controllers');

        $this->assertEmpty(
            $missing,
            "Páginas Inertia referenciadas mas ausentes:\n" . implode("\n", $missing)
        );
    }

    /**
     * Verifica especificamente as páginas críticas de show/edit
     * que historicamente estavam ausentes neste projeto.
     */
    public function test_critical_pages_exist(): void
    {
        $criticalPages = [
            'Central/Users/Index',
            'Central/Users/Create',
            'Central/Users/Edit',
            'Central/Users/Show',
            'Central/Tenants/Index',
            'Central/Tenants/Create',
            'Central/Tenants/Edit',
            'Central/Tenants/Show',
            'Tenant/Users/Index',
            'Tenant/Users/Create',
            'Tenant/Users/Edit',
            'Tenant/Users/Show',
            'Tenant/Collaborators/Index',
            'Tenant/Collaborators/Create',
            'Tenant/Collaborators/Edit',
            'Tenant/Collaborators/Show',
        ];

        $missing = [];
        foreach ($criticalPages as $page) {
            $path = $this->pagesPath . '/' . $page . '.jsx';
            if (!file_exists($path)) {
                $missing[] = $page . '.jsx';
            }
        }

        $this->assertEmpty(
            $missing,
            "Páginas críticas ausentes:\n  " . implode("\n  ", $missing)
        );
    }
}
