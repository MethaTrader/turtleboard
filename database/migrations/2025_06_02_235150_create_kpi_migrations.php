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
        // Turtles table - represents the user's virtual pet/progress indicator
        Schema::create('kpi_turtles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('name')->default('Shelly');
            $table->integer('level')->default(1);
            $table->integer('love_points')->default(0);
            $table->integer('total_love_earned')->default(0); // Historical total
            $table->integer('experience')->default(0);
            $table->timestamp('last_fed_at')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->json('attributes')->nullable(); // For storing customizations
            $table->json('achievements')->nullable(); // Achievements unlocked
            $table->timestamps();
        });

        // Task definitions - system-defined tasks that can be completed
        Schema::create('kpi_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('type'); // e.g., 'daily', 'weekly', 'achievement', 'one-time'
            $table->string('category')->default('general'); // e.g., 'account_creation', 'engagement'
            $table->integer('love_reward'); // Love points earned for completion
            $table->integer('experience_reward')->default(0); // XP earned for completion
            $table->json('requirements')->nullable(); // JSON with task requirements
            $table->json('metadata')->nullable(); // Additional task data
            $table->boolean('active')->default(true);
            $table->boolean('is_milestone')->default(false); // Special milestone tasks
            $table->timestamps();
        });

        // User task completions - tracks which tasks users have completed
        Schema::create('kpi_user_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_task_id')->constrained()->onDelete('cascade');
            $table->integer('progress')->default(0); // For multi-step tasks
            $table->integer('target')->default(1); // Number required to complete
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable(); // Additional completion data
            $table->timestamps();

            // User can have a task only once (for one-time tasks)
            $table->unique(['user_id', 'kpi_task_id']);
        });

        // User rewards/love history - tracks points history
        Schema::create('kpi_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_task_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('love_points');
            $table->integer('experience_points')->default(0);
            $table->string('reason'); // Human-readable description
            $table->string('source_type')->nullable(); // Polymorphic relation
            $table->unsignedBigInteger('source_id')->nullable(); // Polymorphic relation
            $table->timestamps();

            // Index for polymorphic relation
            $table->index(['source_type', 'source_id']);
        });

        // KPI Targets - Admin-defined performance targets
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('metric_type'); // e.g., 'mexc_accounts', 'email_accounts'
            $table->integer('target_value');
            $table->integer('love_reward');
            $table->integer('experience_reward')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'custom']);
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // User Target Progress - Tracks user progress toward KPI targets
        Schema::create('kpi_user_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_target_id')->constrained('kpi_targets')->onDelete('cascade');
            $table->integer('current_value')->default(0);
            $table->boolean('achieved')->default(false);
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();

            // User can have a specific target only once
            $table->unique(['user_id', 'kpi_target_id']);
        });

        // Turtle Customizations - Items/accessories for turtles
        Schema::create('kpi_turtle_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('type'); // e.g., 'background', 'accessory', 'shell'
            $table->integer('love_cost');
            $table->string('image_path');
            $table->json('attributes')->nullable();
            $table->boolean('available')->default(true);
            $table->integer('required_level')->default(1);
            $table->timestamps();
        });

        // User Turtle Items - Items owned by user turtles
        Schema::create('kpi_user_turtle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_turtle_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_turtle_item_id')->constrained()->onDelete('cascade');
            $table->boolean('equipped')->default(false);
            $table->timestamp('purchased_at');
            $table->timestamps();

            // A turtle can have a specific item only once
            $table->unique(['kpi_turtle_id', 'kpi_turtle_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_user_turtle_items');
        Schema::dropIfExists('kpi_turtle_items');
        Schema::dropIfExists('kpi_user_targets');
        Schema::dropIfExists('kpi_targets');
        Schema::dropIfExists('kpi_rewards');
        Schema::dropIfExists('kpi_user_tasks');
        Schema::dropIfExists('kpi_tasks');
        Schema::dropIfExists('kpi_turtles');
    }
};