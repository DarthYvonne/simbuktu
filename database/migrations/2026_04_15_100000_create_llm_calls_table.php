<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('llm_calls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $t->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $t->string('persona_id')->nullable();
            $t->string('prompt_key')->index();
            $t->string('provider');
            $t->string('model');
            $t->unsignedInteger('input_tokens')->default(0);
            $t->unsignedInteger('output_tokens')->default(0);
            $t->decimal('cost_usd', 10, 6)->default(0);
            $t->unsignedInteger('latency_ms')->nullable();
            $t->timestamp('created_at')->index();

            $t->index(['course_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_calls');
    }
};
