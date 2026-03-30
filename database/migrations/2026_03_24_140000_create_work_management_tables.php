<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('category');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->index()->constrained('workflows')->cascadeOnDelete();
            $table->foreignId('from_status_id')->index()->constrained('workflow_statuses')->cascadeOnDelete();
            $table->foreignId('to_status_id')->index()->constrained('workflow_statuses')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('requires_resolution')->default(false);
            $table->boolean('requires_all_subtasks_done')->default(false);
            $table->timestamps();
            $table->unique(['workflow_id', 'from_status_id', 'to_status_id'], 'workflow_unique_transition');
        });

        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->integer('weight')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('default_workflow_id')->nullable()->index()->constrained('workflows')->nullOnDelete();
            $table->foreignId('lead_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'key']);
        });

        Schema::create('project_workflow_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->index()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('workflow_status_id')->index()->constrained('workflow_statuses')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'workflow_status_id'], 'project_workflow_status_unique');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->index()->constrained('projects')->cascadeOnDelete();
            $table->integer('task_number');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->foreignId('status_id')->index()->constrained('workflow_statuses')->cascadeOnDelete();
            $table->foreignId('priority_id')->index()->constrained('task_priorities')->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->index()->constrained('tasks')->nullOnDelete();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('estimate_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['project_id', 'task_number']);
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->index()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('author_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->longText('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('task_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->index()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'user_id'], 'task_watcher_unique');
        });

        Schema::create('task_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('task_id_from')->index()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('task_id_to')->index()->constrained('tasks')->cascadeOnDelete();
            $table->string('relationship_type')->default('related');
            $table->timestamps();
            $table->unique(['task_id_from', 'task_id_to', 'relationship_type'], 'task_link_unique');
        });

        Schema::create('task_asset_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->index()->constrained('tasks')->cascadeOnDelete();
            $table->string('asset_type')->default('asset');
            $table->unsignedBigInteger('asset_id')->index();
            $table->string('relationship_type')->default('linked');
            $table->timestamps();
            $table->unique(['task_id', 'asset_type', 'asset_id', 'relationship_type'], 'task_asset_link_unique');
        });

        Schema::create('task_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->index()->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('event_type')->index();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_activity_log');
        Schema::dropIfExists('task_asset_links');
        Schema::dropIfExists('task_links');
        Schema::dropIfExists('task_watchers');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_workflow_statuses');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('task_priorities');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflows');
        Schema::dropIfExists('workflow_statuses');
    }
};
