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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->unique(); // WhatsApp message ID
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact', 'sticker', 'template', 'interactive']);
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->string('media_id')->nullable(); // WhatsApp media ID
            $table->enum('status', ['sent', 'delivered', 'read', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('from_phone', 20);
            $table->string('to_phone', 20);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // Context, reply info, etc
            $table->timestamps();
            $table->softDeletes();

            $table->index(['whatsapp_conversation_id', 'created_at']);
            $table->index(['status', 'direction']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
