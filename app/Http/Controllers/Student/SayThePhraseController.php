<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\SayThePhraseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SayThePhraseController extends Controller
{
    public function attempt(Request $request, SayThePhraseService $sayThePhraseService)
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:webm,ogg', 'max:460'],
            'solution' => ['required', 'string'],
            'exercise_id' => ['required', 'integer'],
            'language' => ['nullable', 'string'],
        ]);

        $audioFile = $request->file('audio');
        $audioPath = $audioFile->store('temp-audio');

        $result = $sayThePhraseService->processAttempt([
            'audio_path' => $audioPath, // pasar la ruta relativa
            'solution' => $request->input('solution'),
            'language' => $request->input('language', 'en'),
        ]);

        Storage::delete($audioPath);

        return response()->json($result);
    }
}
