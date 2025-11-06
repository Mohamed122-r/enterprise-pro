<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getUserConversations(User $user)
    {
        return $user->conversations()
            ->with(['participants', 'lastMessage', 'lastMessage.user'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function createConversation(User $user, array $userIds, ?string $name = null)
    {
        return DB::transaction(function () use ($user, $userIds, $name) {
            // Add the current user to participants
            $participantIds = array_merge($userIds, [$user->id]);
            $participantIds = array_unique($participantIds);

            // Check if a direct conversation already exists
            if (count($participantIds) === 2 && !$name) {
                $existingConversation = $this->findDirectConversation($participantIds);
                if ($existingConversation) {
                    return $existingConversation;
                }
            }

            $conversation = Conversation::create([
                'name' => $name,
                'type' => count($participantIds) > 2 ? 'group' : 'direct',
                'created_by' => $user->id
            ]);

            $conversation->participants()->sync($participantIds);

            return $conversation->load(['participants', 'lastMessage']);
        });
    }

    public function getConversationMessages($conversationId, User $user)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is participant
        if (!$conversation->participants->contains($user->id)) {
            throw new \Exception('Access denied to this conversation');
        }

        // Mark messages as read
        Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }

    public function sendMessage($conversationId, User $user, array $data)
    {
        return DB::transaction(function () use ($conversationId, $user, $data) {
            $conversation = Conversation::findOrFail($conversationId);

            // Check if user is participant
            if (!$conversation->participants->contains($user->id)) {
                throw new \Exception('Access denied to this conversation');
            }

            $message = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'content' => $data['content'],
                'type' => $data['type'] ?? 'text'
            ]);

            // Update conversation timestamp
            $conversation->touch();

            // TODO: Broadcast message via WebSocket
            // broadcast(new MessageSent($message))->toOthers();

            return $message->load('user');
        });
    }

    private function findDirectConversation(array $userIds)
    {
        return Conversation::where('type', 'direct')
            ->whereHas('participants', function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            }, '=', count($userIds))
            ->first();
    }
}