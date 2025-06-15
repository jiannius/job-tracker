<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_trackers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('uuid')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('filename')->nullable();
            $table->text('path')->nullable();
            $table->integer('progress')->nullable()->default(0);
            $table->integer('attempts')->nullable()->default(0);
            $table->json('messages')->nullable();
            $table->json('errors')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_downloadable')->nullable()->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_trackers');
    }
};
