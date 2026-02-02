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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // lead_assigned, task_due, etc
            $table->string('channel'); // email, whatsapp, push, sms
            $table->string('subject')->nullable(); // For email
            $table->text('body_template');
            $table->json('variables')->nullable(); // Available variables
            $table->json('default_data')->nullable(); // Default values
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['type', 'channel']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
