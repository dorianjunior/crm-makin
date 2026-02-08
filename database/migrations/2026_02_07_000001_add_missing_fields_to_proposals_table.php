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
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('number')->unique()->after('id');
            $table->text('notes')->nullable()->after('status');
            $table->date('valid_until')->nullable()->after('notes');
            $table->timestamp('sent_at')->nullable()->after('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['number', 'notes', 'valid_until', 'sent_at']);
        });
    }
};
