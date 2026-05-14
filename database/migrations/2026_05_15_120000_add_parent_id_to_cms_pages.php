<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $t) {
            if (!Schema::hasColumn('cms_pages', 'parent_id')) {
                $t->foreignId('parent_id')->nullable()->after('id')
                    ->constrained('cms_pages')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $t) {
            $t->dropConstrainedForeignId('parent_id');
        });
    }
};
