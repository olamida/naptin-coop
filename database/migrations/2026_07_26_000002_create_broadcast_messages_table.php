<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category')->default('general');
            $table->string('priority')->default('normal');
            $table->foreignId('sent_by')->constrained('users');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamps();

            $table->index('created_at');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_messages');
    }
};
