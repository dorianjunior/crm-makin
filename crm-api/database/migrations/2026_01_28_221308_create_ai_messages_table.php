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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();

            // Message Info
            $table->enum('role', ['user', 'assistant', 'system']); // user=humano, assistant=IA, system=contexto
            $table->text('content');
            $table->text('original_content')->nullable(); // Conteúdo antes de processamento

            // AI Generation Info
            $table->string('model')->nullable();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->integer('generation_time_ms')->nullable(); // Tempo de geração em ms

            // Quality & Safety
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0-1
            $table->json('safety_ratings')->nullable(); // Content safety scores
            $table->boolean('content_filtered')->default(false);
            $table->string('finish_reason')->nullable(); // STOP, MAX_TOKENS, SAFETY, etc

            // Context Used
            $table->json('context_used')->nullable(); // Quais dados de contexto foram usados
            $table->boolean('used_lead_data')->default(false);
            $table->boolean('used_conversation_history')->default(false);

            // Response Actions
            $table->boolean('was_sent')->default(false); // Se foi enviada automaticamente
            $table->timestamp('sent_at')->nullable();
            $table->boolean('requires_human_review')->default(false);
            $table->boolean('human_edited')->default(false);

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ai_conversation_id', 'role']);
            $table->index(['was_sent', 'requires_human_review']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
