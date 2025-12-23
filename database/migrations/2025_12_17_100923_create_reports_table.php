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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->text('description');
            $table->json('attachments')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('category')->nullable();
            $table->tinyInteger('severity')->nullable();
            $table->tinyInteger('probability')->nullable();
            $table->tinyInteger('risk_score')->nullable();
            $table->string('risk_level')->nullable();
            $table->enum('status', [
                'draft','submitted','reviewed','approved','closed'
            ])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
