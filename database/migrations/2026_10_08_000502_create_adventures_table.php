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
        Schema::create('adventures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('title', 160);
            $table->jsonb('story');
            $table->unsignedSmallInteger('story_format_version')
                ->default(1);
            $table->string('location', 200)
                ->nullable();
            $table->date('travel_start_date')
                ->nullable();
            $table->date('travel_end_date')
                ->nullable();
            $table->unsignedInteger('version')
                ->default(1);
            $table->uuid('creation_key');
            $table->char('request_fingerprint', 64);
            $table->timestampTz('published_at')
                ->nullable();
            $table->timestampTz('first_published_at')
                ->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['user_id', 'creation_key']);
            $table->index(['user_id', 'published_at', 'deleted_at', 'updated_at', 'id'], 'adventures_owner_drafts_index');
            $table->index(['published_at', 'deleted_at', 'first_published_at', 'id'], 'adventures_public_index');
            $table->index(['deleted_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adventures');
    }
};
