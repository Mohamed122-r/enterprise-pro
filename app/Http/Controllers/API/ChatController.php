<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function conversations(Request $request): JsonResponse
    {
        try {
            $conversations = $this->chatService->getUserConversations($request->user());
            
            return response()->json([
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createConversation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'name' => 'nullable|string|max:255'
            ]);

            $conversation = $this->chatService->createConversation(
                $request->user(),
                $request->user_ids,
                $request->name
            );
            
            return response()->json([
                'message' => 'Conversation created successfully',
                'data' => $conversation
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function messages(Request $request, $conversationId): JsonResponse
    {
        try {
            $messages = $this->chatService->getConversationMessages($conversationId, $request->user());
            
            return response()->json([
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request, $conversationId): JsonResponse
    {
        try {
            $request->validate([
                'content' => 'required|string',
                'type' => 'nullable|string|in:text,image,file'
            ]);

            $message = $this->chatService->sendMessage(
                $conversationId,
                $request->user(),
                $request->all()
            );
            
            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}