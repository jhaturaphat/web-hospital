<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_staff')->default(false);
        });
        Schema::create('news_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['is_published', 'created_at']);
        });
        Schema::create('news_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('name');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
        // Set up navigation without inventing hospital announcements.
        foreach (['ประชาสัมพันธ์', 'ข่าวรับสมัครงาน', 'ข่าวทั่วไป', 'ข่าวจัดซื้อจัดจ้าง', 'เปิดเผยข้อมูลการเงิน', 'หลักสูตรการเรียนรู้'] as $name) {
            DB::table('news_categories')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news_attachments');
        Schema::dropIfExists('news_posts');
        Schema::dropIfExists('news_categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_staff');
        });
    }
};