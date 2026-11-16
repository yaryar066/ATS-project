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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
    $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('job_id')->constrained('job_openings')->onDelete('cascade');
    $table->text('resume_text')->nullable(); 
    $table->integer('ai_match_score')->nullable(); 
    $table->enum('status', ['applied', 'shortlisted', 'interviewing', 'offered', 'rejected'])->default('applied');
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
