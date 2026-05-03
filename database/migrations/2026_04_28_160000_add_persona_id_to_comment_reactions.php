<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comment_reactions', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable()->change();
            $t->string('persona_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('comment_reactions', function (Blueprint $t) {
            $t->dropColumn('persona_id');
            $t->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
