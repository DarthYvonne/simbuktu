<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->text('image_description')->nullable()->after('image_path');
            $t->json('reactions')->nullable()->after('reach');
            $t->unsignedInteger('shares')->default(0)->after('reactions');
        });

        Schema::table('comments', function (Blueprint $t) {
            $t->json('reactions')->nullable()->after('likes');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropColumn(['image_description', 'reactions', 'shares']);
        });
        Schema::table('comments', function (Blueprint $t) {
            $t->dropColumn('reactions');
        });
    }
};
