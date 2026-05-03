<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            if (!Schema::hasColumn('courses', 'platform_name')) {
                $t->string('platform_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('courses', 'logo_path')) {
                $t->string('logo_path')->nullable()->after('platform_name');
            }
            if (!Schema::hasColumn('courses', 'favicon_path')) {
                $t->string('favicon_path')->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            foreach (['platform_name', 'logo_path', 'favicon_path'] as $col) {
                if (Schema::hasColumn('courses', $col)) $t->dropColumn($col);
            }
        });
    }
};
