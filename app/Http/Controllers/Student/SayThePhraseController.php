<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\SayThePhraseService;

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
        $fullPath = Storage::path($audioPath);

        $result = $sayThePhraseService->processAttempt([
            'audio_path' => $fullPath,
            'solution' => $request->input('solution'),
            'language' => $request->input('language', 'en'),
        ]);

        Storage::delete($audioPath);

        return response()->json($result);
    }
}
