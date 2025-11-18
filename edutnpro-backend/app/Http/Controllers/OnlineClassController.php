<?php

namespace App\Http\Controllers;

use App\Models\OnlineClass;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnlineClassController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $classes = OnlineClass::where('school_id', $user->school_id)
            ->with('teacher')
            ->when($user->hasRole('teacher'), function ($query) use ($user) {
                $query->where('teacher_id', $user->teacher->id);
            })
            ->orderBy('scheduled_at', 'desc')
            ->paginate(20);

        return view('online-classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('online-classes.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'subject' => 'nullable|string|max:255',
            'class_level' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'platform' => 'required|in:zoom,teams,meet,webex,jitsi,other',
            'meeting_url' => 'required|url',
            'meeting_id' => 'nullable|string|max:255',
            'meeting_password' => 'nullable|string|max:255',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'nullable|in:scheduled,live,completed,cancelled',
            'attachments' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_recorded' => 'nullable|boolean',
            'auto_admit' => 'nullable|boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['teacher_id'] = $validated['teacher_id'] ?? auth()->user()->teacher->id ?? null;
        $validated['status'] = $validated['status'] ?? 'scheduled';
        $validated['participants_count'] = 0;
        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['auto_admit'] = $request->boolean('auto_admit', true);

        $class = OnlineClass::create($validated);

        return redirect()->route('online-classes.show', $class)
            ->with('success', __('messages.online_class_created'));
    }

    public function show(OnlineClass $onlineClass)
    {
        $this->authorize('view', $onlineClass);

        $onlineClass->load('teacher');

        return view('online-classes.show', compact('onlineClass'));
    }

    public function edit(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('online-classes.edit', compact('onlineClass', 'teachers'));
    }

    public function update(Request $request, OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);

        $validated = $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'subject' => 'nullable|string|max:255',
            'class_level' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'platform' => 'required|in:zoom,teams,meet,webex,jitsi,other',
            'meeting_url' => 'required|url',
            'meeting_id' => 'nullable|string|max:255',
            'meeting_password' => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'nullable|in:scheduled,live,completed,cancelled',
            'recording_url' => 'nullable|url',
            'attachments' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_recorded' => 'nullable|boolean',
            'auto_admit' => 'nullable|boolean',
        ]);

        $validated['is_recorded'] = $request->boolean('is_recorded');
        $validated['auto_admit'] = $request->boolean('auto_admit');

        $onlineClass->update($validated);

        return redirect()->route('online-classes.show', $onlineClass)
            ->with('success', __('messages.online_class_updated'));
    }

    public function destroy(OnlineClass $onlineClass)
    {
        $this->authorize('delete', $onlineClass);

        $onlineClass->delete();

        return redirect()->route('online-classes.index')
            ->with('success', __('messages.online_class_deleted'));
    }

    public function start(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);

        if ($onlineClass->status !== 'scheduled') {
            return back()->with('error', __('messages.class_already_started_or_completed'));
        }

        $onlineClass->update([
            'status' => 'live',
            'started_at' => now(),
        ]);

        return redirect($onlineClass->meeting_url);
    }

    public function end(OnlineClass $onlineClass)
    {
        $this->authorize('update', $onlineClass);

        if ($onlineClass->status !== 'live') {
            return back()->with('error', __('messages.class_not_live'));
        }

        $onlineClass->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return redirect()->route('online-classes.show', $onlineClass)
            ->with('success', __('messages.class_ended'));
    }

    public function join(OnlineClass $onlineClass)
    {
        $this->authorize('view', $onlineClass);

        if ($onlineClass->status === 'cancelled') {
            return back()->with('error', __('messages.class_cancelled'));
        }

        if ($onlineClass->status === 'completed') {
            return back()->with('error', __('messages.class_already_completed'));
        }

        // Increment participants count
        $onlineClass->increment('participants_count');

        return redirect($onlineClass->meeting_url);
    }

    public function upcoming()
    {
        $user = auth()->user();

        $classes = OnlineClass::where('school_id', $user->school_id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->with('teacher')
            ->when($user->hasRole('teacher'), function ($query) use ($user) {
                $query->where('teacher_id', $user->teacher->id);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('online-classes.upcoming', compact('classes'));
    }

    public function calendar()
    {
        $user = auth()->user();

        $classes = OnlineClass::where('school_id', $user->school_id)
            ->where('status', '!=', 'cancelled')
            ->when($user->hasRole('teacher'), function ($query) use ($user) {
                $query->where('teacher_id', $user->teacher->id);
            })
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'title' => $class->title,
                    'start' => $class->scheduled_at->format('Y-m-d H:i:s'),
                    'end' => $class->scheduled_at->addMinutes($class->duration_minutes)->format('Y-m-d H:i:s'),
                    'backgroundColor' => $this->getStatusColor($class->status),
                    'url' => route('online-classes.show', $class),
                ];
            });

        return response()->json($classes);
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'scheduled' => '#3788d8',
            'live' => '#22c55e',
            'completed' => '#6b7280',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }
}
