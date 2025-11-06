<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->enum('status', ['lead', 'prospect', 'customer', 'partner'])->default('lead');
            $table->enum('source', ['website', 'referral', 'social_media', 'event', 'other'])->default('website');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted')->nullable();
            $table->timestamps();

            $table->unique(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};