<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // AI Provider Settings
            $table->string('provider')->default('gemini'); // gemini, openai, claude
            $table->string('model')->default('gemini-1.5-flash');
            $table->boolean('is_active')->default(true);

            // Generation Parameters
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_output_tokens')->default(1024);
            $table->decimal('top_p', 3, 2)->default(0.95);
            $table->integer('top_k')->default(40);

            // Auto-response Settings
            $table->boolean('auto_response_enabled')->default(false);
            $table->integer('response_delay_seconds')->default(5); // Delay antes de responder
            $table->json('auto_response_channels')->nullable(); // ['whatsapp', 'instagram']

            // Context Settings
            $table->integer('conversation_context_messages')->default(10); // Número de mensagens de histórico
            $table->boolean('use_lead_data')->default(true); // Incluir dados do lead no contexto
            $table->boolean('use_product_catalog')->default(false); // Incluir catálogo de produtos

            // Usage Limits
            $table->integer('daily_message_limit')->nullable();
            $table->integer('monthly_token_limit')->nullable();

            // System Prompt
            $table->text('system_prompt')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('company_id');
            $table->index(['is_active', 'auto_response_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
