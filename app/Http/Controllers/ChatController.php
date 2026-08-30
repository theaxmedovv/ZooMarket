<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $chats = Chat::with(['post.category', 'buyer', 'seller', 'lastMessage.sender'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->latest('updated_at')
            ->get()
            ->map(function (Chat $chat) use ($userId) {
                $chat->unread = $chat->unreadCountFor($userId);
                return $chat;
            });

        return view('chats.index', compact('chats'));
    }

    public function show(Request $request, Chat $chat)
    {
        $user = $request->user();
        abort_unless($chat->involvesUser($user->id), 403);

        $chat->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $chat->load(['post.category', 'buyer', 'seller', 'messages.sender']);

        return view('chats.show', compact('chat'));
    }

    public function store(Request $request, Chat $chat): RedirectResponse
    {
        $user = $request->user();
        abort_unless($chat->involvesUser($user->id), 403);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $chat->messages()->create([
            'sender_id' => $user->id,
            'body'      => $validated['body'],
        ]);

        $chat->touch();

        return redirect()->route('chats.show', $chat)->with('sent', true);
    }
}
