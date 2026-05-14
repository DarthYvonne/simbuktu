<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $t) {
            if (!Schema::hasColumn('cms_pages', 'hero_image')) {
                $t->string('hero_image')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $t) {
            $t->dropColumn('hero_image');
        });
    }
};
