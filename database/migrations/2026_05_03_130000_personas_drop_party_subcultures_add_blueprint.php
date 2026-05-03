<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $t) {
            $t->dropIndex(['party']);
            $t->dropColumn(['party', 'subcultures']);
            $t->foreignId('blueprint_id')->nullable()->after('population_id')->constrained('blueprints')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $t) {
            $t->dropConstrainedForeignId('blueprint_id');
            $t->string('party')->after('region');
            $t->json('subcultures')->after('party');
            $t->index('party');
        });
    }
};
