<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Study Groups
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('image')->nullable();
            $table->enum('privacy', ['public', 'private', 'invite_only'])->default('public');
            $table->integer('max_members')->default(10);
            $table->boolean('is_active')->default(true);
            $table->json('meeting_schedule')->nullable(); // Days, times for regular meetings
            $table->string('meeting_link')->nullable(); // Online meeting URL
            $table->timestamps();
        });

        // Study Group Members
        Schema::create('study_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member');
            $table->enum('status', ['pending', 'active', 'banned'])->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['study_group_id', 'student_id']);
        });

        // Study Group Posts (forum-like discussions)
        Schema::create('study_group_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('study_group_posts')->onDelete('cascade'); // For replies
            $table->text('content');
            $table->json('attachments')->nullable(); // Files, images, links
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_announcement')->default(false);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
        });

        // Post Likes
        Schema::create('study_group_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('study_group_posts')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['post_id', 'student_id']);
        });

        // Tutoring Sessions
        Schema::create('tutor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('students')->onDelete('cascade'); // Student offering help
            $table->foreignId('tutee_id')->constrained('students')->onDelete('cascade'); // Student receiving help
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('topic')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['one_on_one', 'group'])->default('one_on_one');
            $table->enum('mode', ['in_person', 'online'])->default('online');
            $table->dateTime('scheduled_at')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->string('meeting_url')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable(); // Tutor notes after session
            $table->integer('rating')->nullable(); // 1-5 rating by tutee
            $table->text('feedback')->nullable(); // Tutee feedback
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Tutor Profiles (students who offer tutoring)
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->text('bio_ar')->nullable();
            $table->json('subjects')->nullable(); // Array of subject IDs
            $table->json('availability')->nullable(); // Schedule
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_sessions')->default(0);
            $table->integer('total_hours')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subject Forums (discussion boards by subject)
        Schema::create('subject_forums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('moderation_enabled')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'subject_id']);
        });

        // Forum Topics
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained('subject_forums')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->json('tags')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_solved')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('replies_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        // Forum Replies
        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_best_answer')->default(false);
            $table->boolean('is_moderated')->default(false);
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->timestamps();
        });

        // Forum Reply Votes
        Schema::create('forum_reply_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reply_id')->constrained('forum_replies')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('vote_type', ['upvote', 'downvote']);
            $table->timestamps();

            $table->unique(['reply_id', 'student_id']);
        });

        // Resource Sharing
        Schema::create('shared_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('study_group_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('type', ['document', 'video', 'link', 'image', 'audio', 'other']);
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('mime_type')->nullable();
            $table->json('tags')->nullable();
            $table->enum('visibility', ['public', 'group', 'private'])->default('public');
            $table->integer('downloads_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Resource Ratings
        Schema::create('resource_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('shared_resources')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['resource_id', 'student_id']);
        });

        // Peer Help Requests (Q&A system)
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('question');
            $table->json('attachments')->nullable();
            $table->enum('urgency', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'answered', 'closed'])->default('open');
            $table->foreignId('answered_by')->nullable()->constrained('students')->onDelete('set null');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        // Help Answers
        Schema::create('help_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->text('answer');
            $table->json('attachments')->nullable();
            $table->boolean('is_accepted')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
        });

        // Collaborative Notes
        Schema::create('collaborative_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('students')->onDelete('cascade');
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('content')->nullable(); // Markdown/HTML content
            $table->json('contributors')->nullable(); // Array of student IDs who edited
            $table->enum('access_level', ['public', 'class', 'group', 'private'])->default('class');
            $table->foreignId('class_id')->nullable()->constrained('class_rooms')->onDelete('cascade');
            $table->foreignId('study_group_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('version')->default(1);
            $table->timestamps();
        });

        // Note Revisions (version control)
        Schema::create('note_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('collaborative_notes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('version');
            $table->text('content');
            $table->text('changes_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_revisions');
        Schema::dropIfExists('collaborative_notes');
        Schema::dropIfExists('help_answers');
        Schema::dropIfExists('help_requests');
        Schema::dropIfExists('resource_ratings');
        Schema::dropIfExists('shared_resources');
        Schema::dropIfExists('forum_reply_votes');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('subject_forums');
        Schema::dropIfExists('tutor_profiles');
        Schema::dropIfExists('tutor_sessions');
        Schema::dropIfExists('study_group_post_likes');
        Schema::dropIfExists('study_group_posts');
        Schema::dropIfExists('study_group_members');
        Schema::dropIfExists('study_groups');
    }
};
