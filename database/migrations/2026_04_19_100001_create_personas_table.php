<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('population_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->text('bio')->nullable();
            $t->string('image_file')->nullable();
            $t->unsignedSmallInteger('age');
            $t->string('gender');
            $t->string('region');
            $t->string('party');
            $t->json('subcultures');
            $t->json('persona_data');
            $t->timestamps();

            $t->index('population_id');
            $t->index('party');
            $t->index('region');
            $t->index('age');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
