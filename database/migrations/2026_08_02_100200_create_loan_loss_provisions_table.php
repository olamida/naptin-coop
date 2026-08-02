<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_loss_provisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7);
            $table->decimal('outstanding', 15, 2);
            $table->unsignedInteger('days_past_due')->default(0);
            $table->string('classification'); // Performing, Pass & Watch, Substandard, Doubtful, Lost
            $table->decimal('rate', 5, 2);
            $table->decimal('provision_amount', 15, 2);
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->timestamps();

            $table->unique(['loan_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_loss_provisions');
    }
};
