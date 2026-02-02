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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', [
                'leads',
                'sales',
                'activities',
                'tasks',
                'proposals',
                'pipeline',
                'users',
                'custom',
            ]);
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->json('grouping')->nullable();
            $table->json('sorting')->nullable();
            $table->json('chart_config')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('last_executed_at')->nullable();
            $table->integer('execution_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['user_id', 'is_favorite']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
