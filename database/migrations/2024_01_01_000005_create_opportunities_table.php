<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('contact_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('stage', ['prospect', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospect');
            $table->integer('probability')->default(0);
            $table->date('expected_close_date');
            $table->foreignUuid('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};