<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leader_hierarchies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('unidade');
            $table->string('setor');
            $table->timestamps();

            $table->unique(['user_id', 'unidade', 'setor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_hierarchies');
    }
};
