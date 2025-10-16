<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\OpenAITextChatService;
use App\Http\Requests\TextChatMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

class TextChatController extends Controller
{
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

    public function sendMessage(TextChatMessageRequest $request, OpenAITextChatService $chatService): JsonResponse
    {
        $validated = $request->validated();

        if (!config('openai.api_key')) {
            return response()->json([
                'message' => 'El servicio de IA no está configurado. Contacta al equipo de soporte.',
            ], 503);
        }

        try {
            $reply = $chatService->generateReply(
                $validated['messages'],
                $validated['level'] ?? null,
                data_get($validated, 'temperature'),
            );
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'No se pudo obtener la respuesta de la IA en este momento.',
            ], 503);
        }

        return response()->json($reply);
    }
}
