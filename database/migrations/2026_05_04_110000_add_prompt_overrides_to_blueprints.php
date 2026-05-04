<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprints', function (Blueprint $t) {
            $t->json('prompt_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $t) {
            $t->dropColumn('prompt_overrides');
        });
    }
};
