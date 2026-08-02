<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_assets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('recommended_size')->nullable();
            $table->string('file_path');
            $table->string('file_type')->default('image/jpeg');
            $table->json('usage_locations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->json('branding_json')->nullable()->after('secondary_color');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_assets');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('branding_json');
        });
    }
};
