<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            if (!Schema::hasColumn('courses', 'manager_name')) {
                $t->string('manager_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('courses', 'manager_email')) {
                $t->string('manager_email')->nullable()->after('manager_name');
            }
            if (!Schema::hasColumn('courses', 'manager_phone')) {
                $t->string('manager_phone')->nullable()->after('manager_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $t) {
            $t->dropColumn(['manager_name', 'manager_email', 'manager_phone']);
        });
    }
};
