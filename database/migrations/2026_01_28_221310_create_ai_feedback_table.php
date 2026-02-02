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
        Schema::create('ai_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Quem deu o feedback

            // Feedback Type
            $table->enum('type', ['thumbs_up', 'thumbs_down', 'flag', 'comment', 'correction']);

            // Rating
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->enum('quality', ['excellent', 'good', 'fair', 'poor', 'very_poor'])->nullable();

            // Feedback Details
            $table->text('comment')->nullable();
            $table->text('corrected_response')->nullable(); // Sugestão de correção
            $table->json('issues')->nullable(); // ['inaccurate', 'inappropriate', 'incomplete', 'off_topic']

            // Context
            $table->enum('feedback_source', ['user', 'customer', 'automated'])->default('user');
            $table->boolean('is_actionable')->default(false); // Requer ação
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'type']);
            $table->index(['ai_message_id', 'type']);
            $table->index(['is_actionable', 'is_resolved']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feedback');
    }
};
