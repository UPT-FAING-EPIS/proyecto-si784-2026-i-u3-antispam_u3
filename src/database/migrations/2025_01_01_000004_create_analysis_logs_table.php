<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 30)->default('web');
            $table->string('author', 150)->nullable();
            $table->text('content');
            $table->boolean('is_spam');
            $table->string('reason', 50)->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['channel', 'created_at']);
            $table->index(['channel', 'is_spam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_logs');
    }
};
