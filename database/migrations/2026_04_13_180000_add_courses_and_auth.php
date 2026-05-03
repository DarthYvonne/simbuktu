<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $t->boolean('is_admin')->default(false)->after('password');
            }
            if (!Schema::hasColumn('users', 'active_course_id')) {
                $t->foreignId('active_course_id')->nullable()->after('is_admin');
            }
        });

        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();
                $t->text('description')->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->string('invite_token', 64)->unique();
                $t->json('algorithm_config')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('course_memberships')) {
            Schema::create('course_memberships', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->foreignId('course_id')->constrained()->cascadeOnDelete();
                $t->string('role')->default('student');
                $t->string('title')->nullable();
                $t->text('bio')->nullable();
                $t->string('linkedin')->nullable();
                $t->string('image_path')->nullable();
                $t->string('poster_name')->nullable();
                $t->string('poster_image_path')->nullable();
                $t->timestamps();
                $t->unique(['user_id', 'course_id']);
            });
        }

        Schema::table('posts', function (Blueprint $t) {
            if (!Schema::hasColumn('posts', 'course_id')) {
                $t->foreignId('course_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('posts', 'user_id')) {
                $t->foreignId('user_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $t) {
            if (Schema::hasColumn('posts', 'course_id')) { $t->dropForeign(['course_id']); $t->dropColumn('course_id'); }
            if (Schema::hasColumn('posts', 'user_id')) { $t->dropForeign(['user_id']); $t->dropColumn('user_id'); }
        });
        Schema::dropIfExists('course_memberships');
        Schema::dropIfExists('courses');
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'active_course_id')) $t->dropColumn('active_course_id');
            if (Schema::hasColumn('users', 'is_admin')) $t->dropColumn('is_admin');
        });
    }
};
