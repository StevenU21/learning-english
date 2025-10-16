<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\OpenAITextChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TextChatController extends Controller
{
    public function __construct(private readonly OpenAITextChatService $chatService)
    {
    }

    public function index(): Response
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

        return Inertia::render('Student/TextChat/Index', [
            'levels' => $levels,
            'defaultLevel' => 'intermedio',
            'starterPrompts' => [
                'Can you help me talk about my last vacation?',
                'Let\'s practice how to order food at a restaurant.',
                'I want to improve my answers for job interviews.',
                'Teach me some phrasal verbs for daily routines.',
            ],
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
            'level' => ['nullable', 'in:basico,intermedio,avanzado'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
        ]);

        if (!config('openai.api_key')) {
            return response()->json([
                'message' => 'El servicio de IA no está configurado. Contacta al equipo de soporte.',
            ], 503);
        }

        try {
            $result = $this->chatService->generateReply(
                messages: $validated['messages'],
                level: $validated['level'] ?? null,
                temperature: isset($validated['temperature']) ? (float) $validated['temperature'] : null,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo obtener la respuesta de la IA en este momento.',
            ], 503);
        }

        if (empty($result['reply'])) {
            return response()->json([
                'message' => 'La IA no envió una respuesta válida. Intenta de nuevo.',
            ], 502);
        }

        return response()->json([
            'reply' => $result['reply'],
            'vocabulary' => $result['vocabulary'],
            'grammarTips' => $result['grammar_tips'],
            'followUpQuestions' => $result['follow_up_questions'],
        ]);
    }
}
