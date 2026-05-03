<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->string('author_name')->default('Anonym');
            $t->text('body');
            $t->string('status')->default('draft'); // draft | active | finished
            $t->unsignedInteger('round')->default(0);
            $t->json('algorithm_config')->nullable();
            $t->unsignedInteger('reach')->default(0);
            $t->unsignedInteger('virality_score')->default(0);
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->timestamps();
        });

        Schema::create('comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $t->string('persona_id'); // uuid from JSON dataset
            $t->string('persona_name');
            $t->text('body');
            $t->unsignedInteger('round');
            $t->unsignedInteger('likes')->default(0);
            $t->timestamps();
            $t->index(['post_id', 'round']);
            $t->index('persona_id');
        });

        Schema::create('post_exposures', function (Blueprint $t) {
            $t->id();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->string('persona_id');
            $t->unsignedInteger('round');
            $t->string('action'); // scroll | like | comment | reply | share
            $t->timestamps();
            $t->unique(['post_id', 'persona_id']);
            $t->index(['post_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_exposures');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('posts');
    }
};
