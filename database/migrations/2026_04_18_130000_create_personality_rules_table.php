<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personality_rules', function (Blueprint $t) {
            $t->id();
            $t->string('category');
            $t->string('attribute');
            $t->string('value');
            $t->text('rule_text')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->unique(['attribute', 'value']);
            $t->index(['category', 'attribute', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_rules');
    }
};
