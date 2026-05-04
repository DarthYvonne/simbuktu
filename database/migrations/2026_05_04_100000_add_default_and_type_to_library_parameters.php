<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_parameters', function (Blueprint $t) {
            $t->boolean('default_in_new_blueprints')->default(false);
            $t->string('type', 20)->default('personality');
        });
    }

    public function down(): void
    {
        Schema::table('library_parameters', function (Blueprint $t) {
            $t->dropColumn(['default_in_new_blueprints', 'type']);
        });
    }
};
