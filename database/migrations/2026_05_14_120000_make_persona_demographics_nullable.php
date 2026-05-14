<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $t) {
            $t->integer('age')->nullable()->change();
            $t->string('gender')->nullable()->change();
            $t->string('region')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $t) {
            $t->integer('age')->nullable(false)->change();
            $t->string('gender')->nullable(false)->change();
            $t->string('region')->nullable(false)->change();
        });
    }
};
