<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hardware', function (Blueprint $table): void {
            $table->dropForeign(['provaider_id']);
        });

        Schema::table('software', function (Blueprint $table): void {
            $table->dropForeign(['provaider_id']);
        });

        Schema::table('periphels', function (Blueprint $table): void {
            $table->dropForeign(['provaider_id']);
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
        });

        Schema::rename('provaiders', 'providers');
        Schema::rename('periphels', 'peripherals');

        DB::statement('ALTER TABLE hardware CHANGE provaider_id provider_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE software CHANGE provaider_id provider_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE peripherals CHANGE provaider_id provider_id BIGINT UNSIGNED NOT NULL');

        Schema::table('hardware', function (Blueprint $table): void {
            $table->foreign('provider_id')->references('id')->on('providers')->cascadeOnDelete();
        });

        Schema::table('software', function (Blueprint $table): void {
            $table->foreign('provider_id')->references('id')->on('providers')->cascadeOnDelete();
        });

        Schema::table('peripherals', function (Blueprint $table): void {
            $table->foreign('provider_id')->references('id')->on('providers')->cascadeOnDelete();
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hardware', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
        });

        Schema::table('software', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
        });

        Schema::table('peripherals', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
        });

        DB::statement('ALTER TABLE hardware CHANGE provider_id provaider_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE software CHANGE provider_id provaider_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE peripherals CHANGE provider_id provaider_id BIGINT UNSIGNED NOT NULL');

        Schema::rename('peripherals', 'periphels');
        Schema::rename('providers', 'provaiders');

        Schema::table('hardware', function (Blueprint $table): void {
            $table->foreign('provaider_id')->references('id')->on('provaiders')->cascadeOnDelete();
        });

        Schema::table('software', function (Blueprint $table): void {
            $table->foreign('provaider_id')->references('id')->on('provaiders')->cascadeOnDelete();
        });

        Schema::table('periphels', function (Blueprint $table): void {
            $table->foreign('provaider_id')->references('id')->on('provaiders')->cascadeOnDelete();
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->foreign('provider_id')->references('id')->on('provaiders')->nullOnDelete();
        });
    }
};
