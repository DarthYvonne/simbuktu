<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $t) {
            $t->id();
            // Always stored with persona_id_1 < persona_id_2 (UUID lexicographic) to dedupe.
            $t->string('persona_id_1', 36);
            $t->string('persona_id_2', 36);
            $t->float('similarity')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->unique(['persona_id_1', 'persona_id_2']);
            $t->index('persona_id_1');
            $t->index('persona_id_2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
