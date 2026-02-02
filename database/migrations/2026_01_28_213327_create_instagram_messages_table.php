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
        Schema::create('instagram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->string('message_id')->unique(); // ID da mensagem na API do Instagram
            $table->string('conversation_id'); // Thread ID
            $table->string('sender_id'); // Instagram User ID do remetente
            $table->string('sender_username')->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('type', ['text', 'image', 'video', 'story_mention', 'story_reply', 'reel_share']);
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // Dados extras da API
            $table->timestamps();
            $table->softDeletes();

            $table->index(['instagram_account_id', 'conversation_id']);
            $table->index(['lead_id', 'created_at']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_messages');
    }
};
