<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_support_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 160);
            $table->string('status', 20)->default('open');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'last_message_at']);
            $table->index(['status', 'last_message_at']);
        });

        Schema::create('platform_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('platform_support_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('author_side', 20);
            $table->text('body');
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });

        Schema::create('platform_support_thread_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('platform_support_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->unique(['thread_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_support_thread_reads');
        Schema::dropIfExists('platform_support_messages');
        Schema::dropIfExists('platform_support_threads');
    }
};
