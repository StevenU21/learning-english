<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\TextChatMessageRequest;
use App\Services\OpenAITextChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TextChatController extends Controller
{
    /**
     * Obtener configuraciones iniciales de chat de texto.
     */
    public function index(): JsonResponse
    {
        $levels = [
            [
                'value' => 'basico',
                'label' => 'Básico',
                'description' => 'Usa vocabulario sencillo, frases cortas y ejemplos claros. Ideal para quienes empiezan a conversar en inglés.',
            ],
            [
                'value' => 'intermedio',
                'label' => 'Intermedio',
                'description' => 'Permite ampliar vocabulario y profundizar en temas cotidianos con retroalimentación ligera.',
            ],
            [
                'value' => 'avanzado',
                'label' => 'Avanzado',
                'description' => 'Fomenta respuestas elaboradas, ideas complejas y expresiones naturales del inglés.',
            ],
        ];

        $defaultLevel = 'intermedio';
        $starterPrompts = [
            'Can you help me talk about my last vacation?',
            'Let\'s practice how to order food at a restaurant.',
            'I want to improve my answers for job interviews.',
            'Teach me some phrasal verbs for daily routines.',
        ];

        return response()->json([
            'levels' => $levels,
            'defaultLevel' => $defaultLevel,
            'starterPrompts' => $starterPrompts,
        ], 200);
    }

    /**
     * Enviar mensaje al servicio de chat y obtener respuesta.
     */
    public function sendMessage(TextChatMessageRequest $request, OpenAITextChatService $chatService)
    {
        $validated = $request->validated();

        if (!config('openai.api_key')) {
            return response()->json([
                'message' => 'El servicio de IA no está configurado. Contacta al equipo de soporte.',
            ], 503);
        }

        try {
            $streamCallback = $chatService->generateStreamedReply(
                $validated['messages'],
                $validated['level'] ?? null,
                data_get($validated, 'temperature'),
            );

            return response()->stream($streamCallback, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
                'Content-Encoding' => 'none',
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'No se pudo obtener la respuesta de la IA en este momento.',
            ], 503);
        }
    }
}
