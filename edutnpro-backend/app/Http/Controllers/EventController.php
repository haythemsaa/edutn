<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $events = Event::where('school_id', $user->school_id)
            ->with('organizer')
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('is_public', true)
                    ->orWhere('organizer_id', $user->id);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'event_type' => 'required|in:academic,sports,cultural,meeting,holiday,exam,parent_meeting,trip,ceremony,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'in:students,teachers,parents,staff,all',
            'max_participants' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
            'requires_registration' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,cancelled,completed',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['organizer_id'] = auth()->id();
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['requires_registration'] = $request->boolean('requires_registration');
        $validated['status'] = $validated['status'] ?? 'published';
        $validated['registered_count'] = 0;

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('events', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.event_created'));
    }

    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $event->load('organizer');

        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'event_type' => 'required|in:academic,sports,cultural,meeting,holiday,exam,parent_meeting,trip,ceremony,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'location_ar' => 'nullable|string|max:255',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'in:students,teachers,parents,staff,all',
            'max_participants' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
            'requires_registration' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,cancelled,completed',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string',
        ]);

        $validated['is_public'] = $request->boolean('is_public');
        $validated['requires_registration'] = $request->boolean('requires_registration');

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', __('messages.event_updated'));
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', __('messages.event_deleted'));
    }

    public function calendar()
    {
        $user = auth()->user();

        $events = Event::where('school_id', $user->school_id)
            ->where('status', 'published')
            ->when(!$user->hasRole('admin'), function ($query) {
                $query->where('is_public', true);
            })
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->format('Y-m-d H:i:s'),
                    'end' => $event->end_date->format('Y-m-d H:i:s'),
                    'backgroundColor' => $this->getEventColor($event->event_type),
                    'url' => route('events.show', $event),
                ];
            });

        return response()->json($events);
    }

    private function getEventColor($type)
    {
        return match ($type) {
            'academic' => '#3788d8',
            'sports' => '#22c55e',
            'cultural' => '#a855f7',
            'meeting' => '#f59e0b',
            'holiday' => '#ef4444',
            'exam' => '#dc2626',
            'parent_meeting' => '#14b8a6',
            'trip' => '#06b6d4',
            'ceremony' => '#8b5cf6',
            default => '#6b7280',
        };
    }
}
