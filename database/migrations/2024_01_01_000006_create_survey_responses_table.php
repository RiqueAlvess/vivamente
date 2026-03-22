<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('survey_invite_id')->constrained('survey_invites')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('genero')->nullable();
            $table->string('faixa_etaria')->nullable();
            $table->boolean('consent_given')->default(false);

            // 35 questões HSE-IT (escala 0-4)
            for ($i = 1; $i <= 35; $i++) {
                $table->tinyInteger("q{$i}")->nullable();
            }

            // Scores por dimensão
            $table->decimal('score_demandas', 4, 2)->nullable();
            $table->decimal('score_controle', 4, 2)->nullable();
            $table->decimal('score_apoio_chefia', 4, 2)->nullable();
            $table->decimal('score_apoio_colegas', 4, 2)->nullable();
            $table->decimal('score_relacionamentos', 4, 2)->nullable();
            $table->decimal('score_cargo_funcao', 4, 2)->nullable();
            $table->decimal('score_comunicacao_mudancas', 4, 2)->nullable();

            // Índice Geral de Risco Psicossocial
            $table->decimal('igrp', 5, 2)->nullable();
            $table->string('risco_classificacao')->nullable(); // baixo | moderado | alto

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
