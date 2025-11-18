<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['participants.user', 'lastMessage'])
            ->withCount(['messages', 'unreadMessages' => function ($query) use ($user) {
                $query->whereDoesntHave('reads', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }])
            ->latest('updated_at')
            ->paginate(20);

        return view('conversations.index', compact('conversations'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())
            ->where('school_id', auth()->user()->school_id)
            ->whereHas('roles', function ($query) {
                if (auth()->user()->hasRole('parent')) {
                    $query->whereIn('name', ['admin', 'teacher']);
                } elseif (auth()->user()->hasRole('teacher')) {
                    $query->whereIn('name', ['admin', 'parent', 'teacher']);
                }
            })
            ->get();

        return view('conversations.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
            'message' => 'required|string',
            'type' => 'nullable|in:parent_admin,parent_teacher,group,announcement',
        ]);

        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'subject' => $validated['subject'],
                'type' => $validated['type'] ?? 'parent_admin',
                'school_id' => auth()->user()->school_id,
                'created_by' => auth()->id(),
            ]);

            // Add creator as participant
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
            ]);

            // Add other participants
            foreach ($validated['participants'] as $userId) {
                if ($userId != auth()->id()) {
                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            // Create first message
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => $validated['message'],
                'type' => 'text',
            ]);

            DB::commit();

            return redirect()->route('conversations.show', $conversation)
                ->with('success', __('messages.conversation_created'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('messages.conversation_error'));
        }
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        // Mark messages as read
        $conversation->messages()
            ->whereDoesntHave('reads', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('sender_id', '!=', auth()->id())
            ->get()
            ->each(function ($message) {
                $message->reads()->create(['user_id' => auth()->id()]);
            });

        $messages = $conversation->messages()
            ->with(['sender', 'reads'])
            ->latest()
            ->paginate(50);

        return view('conversations.show', compact('conversation', 'messages'));
    }

    public function messages(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with(['sender', 'reads'])
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('participate', $conversation);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|in:text,file,image,video,audio,document',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('messages', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'text',
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        }

        return back()->with('success', __('messages.message_sent'));
    }

    public function destroy(Conversation $conversation)
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return redirect()->route('conversations.index')
            ->with('success', __('messages.conversation_deleted'));
    }
}
