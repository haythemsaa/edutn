<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudyGroup;
use App\Models\TutorSession;
use App\Models\TutorProfile;
use App\Models\SubjectForum;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Models\SharedResource;
use App\Models\HelpRequest;
use App\Models\HelpAnswer;
use App\Models\CollaborativeNote;
use App\Models\StudyGroupPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SocialLearningController extends Controller
{
    // ==========================================
    // STUDY GROUPS
    // ==========================================

    /**
     * Get all study groups for student's school
     */
    public function getStudyGroups(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $groups = StudyGroup::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->when($request->get('subject_id'), fn($q, $subjectId) =>
                $q->where('subject_id', $subjectId)
            )
            ->when($request->get('privacy'), fn($q, $privacy) =>
                $q->where('privacy', $privacy)
            )
            ->with(['creator', 'subject', 'members'])
            ->withCount('members')
            ->paginate(20);

        return response()->json($groups);
    }

    /**
     * Get student's joined study groups
     */
    public function getMyStudyGroups(Request $request)
    {
        $studentId = $request->user()->userable_id;

        $groups = StudyGroup::whereHas('members', fn($q) =>
            $q->where('student_id', $studentId)
              ->where('status', 'active')
        )
        ->with(['creator', 'subject', 'members'])
        ->withCount('members')
        ->get();

        return response()->json(['study_groups' => $groups]);
    }

    /**
     * Create new study group
     */
    public function createStudyGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'privacy' => 'required|in:public,private,invite_only',
            'max_members' => 'nullable|integer|min:2|max:50',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $group = StudyGroup::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'creator_id' => $student->id,
        ]));

        // Auto-join creator as admin
        $group->members()->attach($student->id, [
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Award XP for creating study group
        if ($student->achievement) {
            $student->achievement->addXP(
                25,
                \App\Models\XpTransaction::TYPE_EARNED,
                'social',
                $group,
                "Groupe d'étude créé: {$group->name}"
            );
        }

        return response()->json(['study_group' => $group], 201);
    }

    /**
     * Join study group
     */
    public function joinStudyGroup(Request $request, StudyGroup $studyGroup)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        if (!$studyGroup->canJoin($student)) {
            return response()->json(['error' => 'Cannot join this group'], 403);
        }

        $studyGroup->members()->attach($student->id, [
            'role' => 'member',
            'status' => $studyGroup->privacy === 'invite_only' ? 'pending' : 'active',
            'joined_at' => $studyGroup->privacy !== 'invite_only' ? now() : null,
        ]);

        return response()->json(['message' => 'Joined successfully']);
    }

    /**
     * Get study group posts (forum)
     */
    public function getGroupPosts(StudyGroup $studyGroup)
    {
        $posts = StudyGroupPost::where('study_group_id', $studyGroup->id)
            ->whereNull('parent_id')
            ->with(['student', 'replies.student'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Create study group post
     */
    public function createGroupPost(Request $request, StudyGroup $studyGroup)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        if (!$studyGroup->isMember($student)) {
            return response()->json(['error' => 'Not a member'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:study_group_posts,id',
            'attachments' => 'nullable|array',
        ]);

        $post = StudyGroupPost::create(array_merge($validated, [
            'study_group_id' => $studyGroup->id,
            'student_id' => $student->id,
        ]));

        return response()->json(['post' => $post], 201);
    }

    // ==========================================
    // TUTORING
    // ==========================================

    /**
     * Get available tutors
     */
    public function getTutors(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $tutors = TutorProfile::where('is_active', true)
            ->where('is_verified', true)
            ->whereHas('student', fn($q) => $q->where('school_id', $student->school_id))
            ->when($request->get('subject_id'), function($q, $subjectId) {
                $q->whereJsonContains('subjects', [(int)$subjectId]);
            })
            ->with('student')
            ->orderBy('average_rating', 'desc')
            ->paginate(20);

        return response()->json($tutors);
    }

    /**
     * Request tutoring session
     */
    public function requestTutoring(Request $request)
    {
        $validated = $request->validate([
            'tutor_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic' => 'nullable|string',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:30|max:180',
            'mode' => 'required|in:in_person,online',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $session = TutorSession::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'tutee_id' => $student->id,
            'type' => 'one_on_one',
            'status' => 'pending',
        ]));

        return response()->json(['session' => $session], 201);
    }

    /**
     * Complete tutoring session
     */
    public function completeTutoringSession(Request $request, TutorSession $session)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        $session->complete(
            $validated['notes'] ?? null,
            $validated['rating'] ?? null,
            $validated['feedback'] ?? null
        );

        return response()->json(['session' => $session]);
    }

    // ==========================================
    // SUBJECT FORUMS
    // ==========================================

    /**
     * Get subject forums
     */
    public function getSubjectForums(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $forums = SubjectForum::where('school_id', $student->school_id)
            ->where('is_active', true)
            ->with('subject')
            ->withCount('topics')
            ->get();

        return response()->json(['forums' => $forums]);
    }

    /**
     * Get forum topics
     */
    public function getForumTopics(SubjectForum $forum)
    {
        $topics = ForumTopic::where('forum_id', $forum->id)
            ->with('student')
            ->withCount('replies')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_activity_at', 'desc')
            ->paginate(20);

        return response()->json($topics);
    }

    /**
     * Create forum topic
     */
    public function createForumTopic(Request $request, SubjectForum $forum)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $topic = ForumTopic::create(array_merge($validated, [
            'forum_id' => $forum->id,
            'student_id' => $student->id,
            'last_activity_at' => now(),
        ]));

        return response()->json(['topic' => $topic], 201);
    }

    /**
     * Reply to forum topic
     */
    public function replyToTopic(Request $request, ForumTopic $topic)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $reply = ForumReply::create(array_merge($validated, [
            'topic_id' => $topic->id,
            'student_id' => $student->id,
        ]));

        // Update topic
        $topic->increment('replies_count');
        $topic->update(['last_activity_at' => now()]);

        return response()->json(['reply' => $reply], 201);
    }

    /**
     * Mark reply as best answer
     */
    public function markBestAnswer(Request $request, ForumTopic $topic, ForumReply $reply)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        // Only topic creator can mark best answer
        if ($topic->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $topic->markAsSolved($reply);

        return response()->json(['topic' => $topic]);
    }

    // ==========================================
    // SHARED RESOURCES
    // ==========================================

    /**
     * Get shared resources
     */
    public function getSharedResources(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $resources = SharedResource::where('school_id', $student->school_id)
            ->where(function($q) use ($student) {
                $q->where('visibility', 'public')
                  ->orWhere('student_id', $student->id);
            })
            ->when($request->get('subject_id'), fn($q, $subjectId) =>
                $q->where('subject_id', $subjectId)
            )
            ->when($request->get('type'), fn($q, $type) =>
                $q->where('type', $type)
            )
            ->with('student', 'subject')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($resources);
    }

    /**
     * Upload shared resource
     */
    public function uploadResource(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'required|exists:subjects,id',
            'type' => 'required|in:document,video,link,image,audio,other',
            'file' => 'required_unless:type,link|file|max:51200', // 50MB max
            'file_url' => 'required_if:type,link|url',
            'tags' => 'nullable|array',
            'visibility' => 'required|in:public,group,private',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $resource = SharedResource::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'student_id' => $student->id,
        ]));

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('shared-resources', 'public');
            $resource->update([
                'file_path' => $path,
                'file_size' => $request->file('file')->getSize(),
                'mime_type' => $request->file('file')->getMimeType(),
            ]);
        }

        // Award XP for sharing resource
        if ($student->achievement) {
            $student->achievement->addXP(
                15,
                \App\Models\XpTransaction::TYPE_EARNED,
                'social',
                $resource,
                "Ressource partagée: {$resource->title}"
            );
        }

        return response()->json(['resource' => $resource], 201);
    }

    // ==========================================
    // HELP REQUESTS (Q&A)
    // ==========================================

    /**
     * Get help requests
     */
    public function getHelpRequests(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $requests = HelpRequest::where('school_id', $student->school_id)
            ->when($request->get('status'), fn($q, $status) =>
                $q->where('status', $status)
            )
            ->when($request->get('subject_id'), fn($q, $subjectId) =>
                $q->where('subject_id', $subjectId)
            )
            ->with(['student', 'subject', 'answers'])
            ->orderBy('urgency', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    /**
     * Create help request
     */
    public function createHelpRequest(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'urgency' => 'required|in:low,medium,high',
            'attachments' => 'nullable|array',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $helpRequest = HelpRequest::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'student_id' => $student->id,
        ]));

        return response()->json(['help_request' => $helpRequest], 201);
    }

    /**
     * Answer help request
     */
    public function answerHelpRequest(Request $request, HelpRequest $helpRequest)
    {
        $validated = $request->validate([
            'answer' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $answer = HelpAnswer::create(array_merge($validated, [
            'help_request_id' => $helpRequest->id,
            'student_id' => $student->id,
        ]));

        $helpRequest->update(['status' => 'in_progress']);

        return response()->json(['answer' => $answer], 201);
    }

    /**
     * Accept help answer
     */
    public function acceptAnswer(Request $request, HelpRequest $helpRequest, HelpAnswer $answer)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        // Only requester can accept answer
        if ($helpRequest->student_id !== $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $helpRequest->markAsAnswered($answer);

        return response()->json(['help_request' => $helpRequest]);
    }

    // ==========================================
    // COLLABORATIVE NOTES
    // ==========================================

    /**
     * Get collaborative notes
     */
    public function getCollaborativeNotes(Request $request)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        $notes = CollaborativeNote::where('school_id', $student->school_id)
            ->where(function($q) use ($student) {
                $q->where('access_level', 'public')
                  ->orWhere(function($q) use ($student) {
                      $q->where('access_level', 'class')
                        ->where('class_id', $student->class_id);
                  })
                  ->orWhere('creator_id', $student->id);
            })
            ->with(['creator', 'subject'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($notes);
    }

    /**
     * Create collaborative note
     */
    public function createCollaborativeNote(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'access_level' => 'required|in:public,class,group,private',
            'study_group_id' => 'nullable|exists:study_groups,id',
        ]);

        $student = Student::findOrFail($request->user()->userable_id);

        $note = CollaborativeNote::create(array_merge($validated, [
            'school_id' => $student->school_id,
            'creator_id' => $student->id,
            'class_id' => $validated['access_level'] === 'class' ? $student->class_id : null,
            'version' => 1,
        ]));

        return response()->json(['note' => $note], 201);
    }

    /**
     * Edit collaborative note
     */
    public function editCollaborativeNote(Request $request, CollaborativeNote $note)
    {
        $student = Student::findOrFail($request->user()->userable_id);

        if (!$note->canEdit($student)) {
            return response()->json(['error' => 'Cannot edit this note'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'changes_summary' => 'nullable|string',
        ]);

        $note->updateContent($student, $validated['content'], $validated['changes_summary'] ?? null);

        return response()->json(['note' => $note]);
    }
}
