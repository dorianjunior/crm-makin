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
        Schema::table('tasks', function (Blueprint $table) {
            // Renomear user_id para assigned_to
            $table->renameColumn('user_id', 'assigned_to');

            // Adicionar prioridade
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                ->default('medium')
                ->after('status');

            // Adicionar campos de conclusão
            $table->timestamp('completed_at')->nullable()->after('due_date');
            $table->foreignId('completed_by')->nullable()->constrained('users')->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Remover campos de conclusão
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['completed_by', 'completed_at', 'priority']);

            // Renomear de volta
            $table->renameColumn('assigned_to', 'user_id');
        });
    }
};
