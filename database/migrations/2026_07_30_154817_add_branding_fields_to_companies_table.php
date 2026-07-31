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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('theme_color', 20)->nullable()->default('#2563eb')->after('logo_path');
            $table->string('secondary_color', 20)->nullable()->default('#059669')->after('theme_color');
            $table->string('banner_path', 255)->nullable()->after('secondary_color');
            $table->text('description')->nullable()->after('website');
            $table->text('short_history')->nullable()->after('description');
            $table->string('facebook', 255)->nullable()->after('short_history');
            $table->string('twitter', 255)->nullable()->after('facebook');
            $table->string('instagram', 255)->nullable()->after('twitter');
            $table->string('linkedin', 255)->nullable()->after('instagram');
            $table->string('opening_hours', 255)->nullable()->after('linkedin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'theme_color',
                'secondary_color',
                'banner_path',
                'description',
                'short_history',
                'facebook',
                'twitter',
                'instagram',
                'linkedin',
                'opening_hours',
            ]);
        });
    }
};
