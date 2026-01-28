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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_prompt_template_id')->nullable()->constrained()->nullOnDelete();

            // Context References
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->nullableMorphs('conversationable'); // WhatsAppConversation, InstagramConversation, etc

            // Conversation Info
            $table->string('conversation_id')->unique(); // ID único da conversa
            $table->string('channel')->nullable(); // whatsapp, instagram, web, api
            $table->enum('status', ['active', 'completed', 'failed', 'abandoned'])->default('active');

            // AI Settings Used
            $table->string('provider')->default('gemini');
            $table->string('model');
            $table->text('system_prompt')->nullable();

            // Statistics
            $table->integer('message_count')->default(0);
            $table->integer('total_input_tokens')->default(0);
            $table->integer('total_output_tokens')->default(0);
            $table->decimal('total_cost', 10, 6)->default(0); // Custo estimado

            // Quality Metrics
            $table->integer('user_satisfaction_rating')->nullable(); // 1-5
            $table->boolean('lead_converted')->default(false);
            $table->timestamp('first_message_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Metadata
            $table->json('context_data')->nullable(); // Dados adicionais de contexto
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['lead_id', 'channel']);
            $table->index('first_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
