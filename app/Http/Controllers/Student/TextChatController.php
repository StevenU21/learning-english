<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\OpenAITextChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

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

    public function sendMessage(Request $request): JsonResponse|SymfonyStreamedResponse
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
            $stream = $this->chatService->streamReply(
                messages: $validated['messages'],
                level: $validated['level'] ?? null,
                temperature: isset($validated['temperature']) ? (float) $validated['temperature'] : null,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudo iniciar la respuesta en streaming con la IA en este momento.',
            ], 503);
        }

        return response()->stream(function () use ($stream) {
            // Try to flush any server-side buffers so events arrive immediately.
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }

            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', 'off');
            @ini_set('implicit_flush', '1');

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            echo ":ready\n\n";
            flush();

            foreach ($stream as $event) {
                if (function_exists('connection_aborted') && connection_aborted()) {
                    break;
                }

                $encoded = json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($encoded === false) {
                    continue;
                }

                $eventName = is_array($event) && isset($event['type']) ? (string) $event['type'] : 'message';

                echo 'event: ' . $eventName . "\n";
                echo 'data: ' . $encoded . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
