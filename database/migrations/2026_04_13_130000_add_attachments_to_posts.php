<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->string('image_path')->nullable()->after('body');
            $t->string('link_url')->nullable()->after('image_path');
            $t->string('link_title')->nullable()->after('link_url');
            $t->text('link_description')->nullable()->after('link_title');
            $t->string('link_image')->nullable()->after('link_description');
            $t->string('link_site_name')->nullable()->after('link_image');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            $t->dropColumn(['image_path', 'link_url', 'link_title', 'link_description', 'link_image', 'link_site_name']);
        });
    }
};
