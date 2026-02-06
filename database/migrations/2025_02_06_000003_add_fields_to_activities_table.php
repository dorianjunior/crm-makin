<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('description');
            $table->integer('duration')->nullable()->comment('Duration in minutes')->after('notes');
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['notes', 'duration', 'company_id']);
        });
    }
};
