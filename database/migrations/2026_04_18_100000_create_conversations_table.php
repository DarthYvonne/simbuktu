<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('persona_id');
            $t->string('persona_name');
            $t->timestamp('last_message_at')->nullable();
            $t->timestamps();
            $t->unique(['course_id', 'user_id', 'persona_id']);
            $t->index(['user_id', 'last_message_at']);
        });

        Schema::create('conversation_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->string('role', 16); // user | persona
            $t->text('body');
            $t->timestamps();
            $t->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};
