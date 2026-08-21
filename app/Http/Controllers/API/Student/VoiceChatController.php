<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoiceChatSessionRequest;
use App\Services\OpenAIRealtimeService;
use Illuminate\Http\JsonResponse;

class VoiceChatController extends Controller
{
    /**
     * Get initial voice chat configuration.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'defaultVoice' => config('openai.realtime_voice', 'marin'),
            'defaultModel' => config('openai.realtime_model', 'gpt-realtime-2.1'),
            'sessionDuration' => config('openai.realtime_session_duration', 120),
        ], 200);
    }

    /**
     * Create a new voice chat session.
     */
    public function createSession(VoiceChatSessionRequest $request, OpenAIRealtimeService $realtimeService): JsonResponse
    {
        $validated = $request->validated();

        try {
            $session = $realtimeService->createVoiceSession(
                voice: $validated['voice'] ?? null,
                instructions: $validated['instructions'] ?? null,
                model: $validated['model'] ?? null,
                expiresIn: $validated['expires_in'] ?? config('openai.realtime_session_duration', 120),
                conversationLevel: $validated['level'] ?? null,
                userId: (string) $request->user()->id,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo iniciar la sesión de voz con el servicio de IA en este momento.',
            ], 503);
        }

        if (empty($session['client_secret'])) {
            return response()->json([
                'message' => 'El servicio de IA no devolvió las credenciales necesarias para iniciar la sesión.',
            ], 502);
        }

        return response()->json($session, 200);
    }
}
