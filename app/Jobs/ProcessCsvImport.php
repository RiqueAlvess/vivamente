<?php

namespace App\Jobs;

use App\Models\Collaborator;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        private readonly int $importJobId,
        private readonly string $filename,
        private readonly int $companyId,
    ) {}

    public function handle(): void
    {
        $importJob = ImportJob::withoutGlobalScopes()->find($this->importJobId);

        if (! $importJob) {
            return;
        }

        $content = Storage::get($this->filename);

        if (! $content) {
            $importJob->update(['status' => 'com_erros', 'errors' => [['row' => 0, 'message' => 'Arquivo não encontrado.']]]);
            return;
        }

        $lines = array_filter(explode("\n", trim($content)));
        $header = null;
        $errors = [];
        $importedRows = 0;
        $errorRows = 0;
        $totalRows = 0;

        $requiredColumns = ['nome', 'email', 'unidade', 'setor', 'cargo'];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $row = str_getcsv($line);

            if ($header === null) {
                $header = array_map('trim', array_map('strtolower', $row));

                $missing = array_diff($requiredColumns, $header);
                if (! empty($missing)) {
                    $importJob->update([
                        'status' => 'com_erros',
                        'errors' => [['row' => 1, 'message' => 'Colunas obrigatórias ausentes: ' . implode(', ', $missing)]],
                    ]);
                    return;
                }
                continue;
            }

            $totalRows++;
            $data = array_combine($header, array_pad($row, count($header), null));

            $rowErrors = [];
            foreach ($requiredColumns as $col) {
                if (empty($data[$col])) {
                    $rowErrors[] = "Campo '{$col}' é obrigatório.";
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['row' => $lineNumber + 1, 'message' => implode(' ', $rowErrors)];
                $errorRows++;
                continue;
            }

            if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $lineNumber + 1, 'message' => "E-mail inválido: {$data['email']}"];
                $errorRows++;
                continue;
            }

            try {
                // updateOrCreate sem global scope para evitar conflito de company_id no where
                $existing = Collaborator::withoutGlobalScopes()
                    ->where('company_id', $this->companyId)
                    ->where('email', trim($data['email']))
                    ->first();

                $attributes = [
                    'name' => trim($data['nome']),
                    'unidade' => trim($data['unidade']),
                    'setor' => trim($data['setor']),
                    'cargo' => trim($data['cargo']),
                    'genero' => isset($data['genero']) ? trim($data['genero']) : null,
                    'faixa_etaria' => isset($data['faixa_etaria']) ? trim($data['faixa_etaria']) : null,
                    'is_active' => true,
                ];

                if ($existing) {
                    $existing->update($attributes);
                } else {
                    Collaborator::withoutGlobalScopes()->create(array_merge($attributes, [
                        'company_id' => $this->companyId,
                        'email' => trim($data['email']),
                    ]));
                }

                $importedRows++;
            } catch (\Exception $e) {
                $errors[] = ['row' => $lineNumber + 1, 'message' => $e->getMessage()];
                $errorRows++;
            }
        }

        $status = $errorRows > 0 ? 'com_erros' : 'concluido';

        $importJob->update([
            'status' => $status,
            'total_rows' => $totalRows,
            'imported_rows' => $importedRows,
            'error_rows' => $errorRows,
            'errors' => $errors,
        ]);
    }
}
