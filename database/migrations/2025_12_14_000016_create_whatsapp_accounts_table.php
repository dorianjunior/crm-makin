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
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number_id')->unique();
            $table->string('business_account_id');
            $table->string('phone_number', 20);
            $table->string('display_name')->nullable();
            $table->text('access_token'); // Encrypted
            $table->string('verify_token');
            $table->enum('account_type', ['STANDARD', 'OFFICIAL', 'VERIFIED'])->default('STANDARD');
            $table->string('quality_rating')->nullable(); // GREEN, YELLOW, RED
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};
