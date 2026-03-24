<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('asset_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->boolean('is_terminal')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('asset_service_task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();
        });

        \DB::table('asset_service_task_statuses')->insert([
            [
                'name' => 'Open',
                'code' => 'open',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'In Progress',
                'code' => 'in_progress',
                'is_terminal' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Completed',
                'code' => 'completed',
                'is_terminal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->index()->constrained('asset_categories')->cascadeOnDelete();
            $table->foreignId('status_id')->index()->constrained('asset_statuses')->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->index()->constrained('provaiders')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('asset_tag')->nullable();
            $table->string('serial')->nullable();
            $table->date('purchased_at')->nullable();
            $table->date('retired_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'asset_tag']);
        });

        Schema::create('asset_service_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->index()->constrained('assets')->cascadeOnDelete();
            $table->string('name');
            $table->integer('service_interval_days');
            $table->integer('reminder_days_before')->default(7);
            $table->foreignId('default_assigned_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('next_due_at')->nullable()->index();
            $table->timestamp('last_completed_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_service_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->index()->constrained('assets')->cascadeOnDelete();
            $table->foreignId('service_plan_id')->nullable()->index()->constrained('asset_service_plans')->nullOnDelete();
            $table->foreignId('status_id')->index()->constrained('asset_service_task_statuses')->cascadeOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_service_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('service_task_id')->index()->constrained('asset_service_tasks')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('remind_at')->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_service_reminders');
        Schema::dropIfExists('asset_service_tasks');
        Schema::dropIfExists('asset_service_plans');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_service_task_statuses');
        Schema::dropIfExists('asset_statuses');
        Schema::dropIfExists('asset_categories');
    }
};
