<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('populations', function (Blueprint $t) {
            $t->json('graph_settings')->nullable()->after('config_overrides');
        });
    }

    public function down(): void
    {
        Schema::table('populations', function (Blueprint $t) {
            $t->dropColumn('graph_settings');
        });
    }
};
