<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\OpenAIRealtimeService;
use App\Http\Requests\VoiceChatSessionRequest;
use Inertia\Inertia;
use Inertia\Response;

class VoiceChatController extends Controller
{
    public function __construct(private readonly OpenAIRealtimeService $realtimeService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Student/VoiceChat/Index', [
            'defaultVoice' => config('openai.realtime_voice', 'alloy'),
            'defaultModel' => config('openai.realtime_model', 'gpt-realtime-mini-2025-10-06'),
            'sessionDuration' => 120,
        ]);
    }

    public function createSession(VoiceChatSessionRequest $request)
    {
        $validated = $request->validated();
        try {
            $session = $this->realtimeService->createVoiceSession(
                voice: $validated['voice'] ?? null,
                instructions: $validated['instructions'] ?? null,
                model: null,
                expiresIn: 120,
                conversationLevel: $validated['level'] ?? null,
            );
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'No se pudo iniciar la sesión de voz con el servicio de IA en este momento.',
            ], 503);
        }
        if (!($session['client_secret'] ?? null)) {
            return response()->json([
                'message' => 'El servicio de IA no devolvió las credenciales necesarias para iniciar la sesión.',
            ], 502);
        }
        return response()->json($session);
    }
}
