<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('duration')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->decimal('pricing_baseline', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            // Module Review
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('approval_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('risk_level');
            $table->index('is_active');
            $table->index('review_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
