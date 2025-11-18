<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student achievements and gamification
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->integer('total_xp')->default(0);
            $table->integer('level')->default(1);
            $table->string('rank')->default('bronze'); // bronze, silver, gold, platinum, diamond
            $table->integer('attendance_streak')->default(0);
            $table->integer('assignment_streak')->default(0);
            $table->integer('best_streak')->default(0);
            $table->json('stats')->nullable(); // Additional stats
            $table->timestamps();

            $table->index(['student_id', 'school_id']);
        });

        // Badges/Achievements catalog
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // perfect_attendance_week, grade_master, etc.
            $table->string('name');
            $table->string('name_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->string('icon'); // emoji or image path
            $table->string('category'); // attendance, academic, social, special
            $table->string('rarity'); // common, rare, epic, legendary
            $table->integer('xp_reward')->default(0);
            $table->json('criteria'); // Conditions to earn
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Student earned badges
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->integer('xp_earned')->default(0);
            $table->json('metadata')->nullable(); // Context of earning
            $table->timestamps();

            $table->unique(['student_id', 'badge_id']);
            $table->index('earned_at');
        });

        // XP transactions log
        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('type'); // assignment, attendance, grade, badge, bonus
            $table->integer('amount'); // Can be negative for penalties
            $table->string('source'); // What triggered this XP
            $table->morphs('source_model'); // Polymorphic to assignment, attendance, etc.
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });

        // Leaderboards (cached)
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // class, school, grade, global
            $table->string('period'); // daily, weekly, monthly, all_time
            $table->foreignId('scope_id')->nullable(); // class_id, school_id, etc.
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('rank');
            $table->integer('score'); // XP or other metric
            $table->json('metadata')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();

            $table->index(['type', 'period', 'scope_id', 'rank']);
            $table->index(['student_id', 'period']);
        });

        // Challenges/Quests
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->string('type'); // individual, class, school
            $table->string('category'); // attendance, grades, social, special
            $table->json('goals'); // What needs to be achieved
            $table->integer('xp_reward');
            $table->json('badge_reward')->nullable(); // Optional badge
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // active, completed, expired
            $table->timestamps();

            $table->index(['school_id', 'status', 'start_date']);
        });

        // Student challenge progress
        Schema::create('student_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->json('progress'); // Track progress towards goals
            $table->integer('completion_percentage')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('xp_earned')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'challenge_id']);
            $table->index(['challenge_id', 'completed']);
        });

        // Rewards (real-world rewards)
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->string('type'); // physical, privilege, digital
            $table->integer('xp_cost');
            $table->integer('quantity')->nullable(); // Limited quantity
            $table->integer('claimed')->default(0);
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Claimed rewards
        Schema::create('student_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('reward_id')->constrained()->onDelete('cascade');
            $table->integer('xp_spent');
            $table->string('status')->default('pending'); // pending, approved, delivered, cancelled
            $table->timestamp('claimed_at');
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_rewards');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('student_challenges');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('xp_transactions');
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('student_achievements');
    }
};
