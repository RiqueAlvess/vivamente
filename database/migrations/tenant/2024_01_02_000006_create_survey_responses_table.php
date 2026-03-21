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
            $table->foreignId('survey_invite_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('genero')->nullable();
            $table->string('faixa_etaria')->nullable();
            // 35 respostas HSE-IT (0-4)
            $table->tinyInteger('q1')->nullable();
            $table->tinyInteger('q2')->nullable();
            $table->tinyInteger('q3')->nullable();
            $table->tinyInteger('q4')->nullable();
            $table->tinyInteger('q5')->nullable();
            $table->tinyInteger('q6')->nullable();
            $table->tinyInteger('q7')->nullable();
            $table->tinyInteger('q8')->nullable();
            $table->tinyInteger('q9')->nullable();
            $table->tinyInteger('q10')->nullable();
            $table->tinyInteger('q11')->nullable();
            $table->tinyInteger('q12')->nullable();
            $table->tinyInteger('q13')->nullable();
            $table->tinyInteger('q14')->nullable();
            $table->tinyInteger('q15')->nullable();
            $table->tinyInteger('q16')->nullable();
            $table->tinyInteger('q17')->nullable();
            $table->tinyInteger('q18')->nullable();
            $table->tinyInteger('q19')->nullable();
            $table->tinyInteger('q20')->nullable();
            $table->tinyInteger('q21')->nullable();
            $table->tinyInteger('q22')->nullable();
            $table->tinyInteger('q23')->nullable();
            $table->tinyInteger('q24')->nullable();
            $table->tinyInteger('q25')->nullable();
            $table->tinyInteger('q26')->nullable();
            $table->tinyInteger('q27')->nullable();
            $table->tinyInteger('q28')->nullable();
            $table->tinyInteger('q29')->nullable();
            $table->tinyInteger('q30')->nullable();
            $table->tinyInteger('q31')->nullable();
            $table->tinyInteger('q32')->nullable();
            $table->tinyInteger('q33')->nullable();
            $table->tinyInteger('q34')->nullable();
            $table->tinyInteger('q35')->nullable();
            // Scores calculados
            $table->decimal('score_demandas', 4, 2)->nullable();
            $table->decimal('score_controle', 4, 2)->nullable();
            $table->decimal('score_apoio_chefia', 4, 2)->nullable();
            $table->decimal('score_apoio_colegas', 4, 2)->nullable();
            $table->decimal('score_relacionamentos', 4, 2)->nullable();
            $table->decimal('score_cargo_funcao', 4, 2)->nullable();
            $table->decimal('score_comunicacao_mudancas', 4, 2)->nullable();
            $table->decimal('igrp', 5, 2)->nullable();
            $table->string('risco_classificacao')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
