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
        Schema::create('instagram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('instagram_user_id')->unique();
            $table->string('username');
            $table->string('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('account_type')->nullable(); // BUSINESS, CREATOR, PERSONAL
            $table->string('profile_picture_url')->nullable();
            $table->string('followers_count')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Info adicional da API
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
        Schema::dropIfExists('instagram_accounts');
    }
};
