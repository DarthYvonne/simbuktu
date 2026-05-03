<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('cms_pages')->insert([
            ['title' => 'Velkommen',    'slug' => '',             'content' => null,                              'sort_order' => 1, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Simulationer', 'slug' => 'simulationer', 'content' => '<p>Indhold kommer snart.</p>',    'sort_order' => 2, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Cases',        'slug' => 'cases',        'content' => '<p>Indhold kommer snart.</p>',    'sort_order' => 3, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Om',           'slug' => 'om',           'content' => '<p>Indhold kommer snart.</p>',    'sort_order' => 4, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Kontakt',      'slug' => 'kontakt',      'content' => '<p>Indhold kommer snart.</p>',    'sort_order' => 5, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_pages');
    }
};
