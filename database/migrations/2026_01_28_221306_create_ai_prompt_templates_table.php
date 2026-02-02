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
        Schema::create('ai_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete(); // null = template global

            // Template Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['customer_service', 'sales', 'support', 'faq', 'general', 'custom']);

            // Prompt Content
            $table->text('system_prompt');
            $table->text('user_prompt_template')->nullable(); // Template com variáveis {{variable}}
            $table->json('variables')->nullable(); // Lista de variáveis disponíveis
            $table->json('examples')->nullable(); // Exemplos de input/output

            // Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_global')->default(false); // Template disponível para todas empresas
            $table->integer('usage_count')->default(0);

            // AI Parameters Override
            $table->decimal('temperature', 3, 2)->nullable();
            $table->integer('max_output_tokens')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['category', 'is_active']);
            $table->index('is_global');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_prompt_templates');
    }
};
