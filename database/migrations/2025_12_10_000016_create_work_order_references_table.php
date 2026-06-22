<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('document_name');
            $table->string('document_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_name');
            $table->enum('file_status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->after('file_size');
            $table->timestamp('file_processed_at')->nullable()->after('file_status');
            $table->text('file_error')->nullable()->after('file_processed_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index('module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_references');
    }
};
