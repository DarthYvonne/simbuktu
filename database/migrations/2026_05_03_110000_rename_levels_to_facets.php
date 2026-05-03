<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_parameters', function (Blueprint $t) {
            $t->renameColumn('levels', 'facets');
        });
    }

    public function down(): void
    {
        Schema::table('library_parameters', function (Blueprint $t) {
            $t->renameColumn('facets', 'levels');
        });
    }
};
