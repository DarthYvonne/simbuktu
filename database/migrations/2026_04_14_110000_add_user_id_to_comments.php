<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $t) {
            $t->foreignId('user_id')->nullable()->after('parent_id')->constrained('users')->nullOnDelete();
            $t->string('persona_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $t) {
            $t->dropForeign(['user_id']);
            $t->dropColumn('user_id');
            $t->string('persona_id')->nullable(false)->change();
        });
    }
};
