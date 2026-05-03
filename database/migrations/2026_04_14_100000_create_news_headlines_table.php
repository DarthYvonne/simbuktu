<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_headlines', function (Blueprint $t) {
            $t->id();
            $t->string('source', 30);
            $t->string('url')->unique();
            $t->string('title');
            $t->timestamp('published_at')->nullable();
            $t->timestamp('fetched_at');
            $t->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_headlines');
    }
};
