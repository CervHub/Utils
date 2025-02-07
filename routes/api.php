<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\AwsPollyService;
use App\Services\OpenAiService;

// Ruta para obtener información del usuario
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('user.info');

// Versión 1 de la API
Route::prefix('v1')->group(function () {

    // Ruta para interactuar con el chat de OpenAI
    Route::post('/openai/message', [OpenAiService::class, 'chat'])->name('openai.message');

    // Ruta para interactuar con AWS Polly para sintetizar texto a voz
    Route::post('/aws/polly/text-to-speech', [AwsPollyService::class, 'synthesizeSpeech'])->name('aws.polly.text-to-speech');

    // Nueva ruta para interactuar con el asistente de OpenAI
    Route::post('/openai/assistant', [OpenAiService::class, 'assistant'])->name('openai.assistant');

    // Nueva ruta para listar los asistentes de OpenAI
    Route::get('/openai/assistants', [OpenAiService::class, 'listAssistants'])->name('openai.assistants');

    // Nueva ruta para crear un thread en OpenAI
    Route::post('/openai/thread', [OpenAiService::class, 'createThread'])->name('openai.createThread');

    // Nueva ruta para listar los threads en OpenAI
    Route::get('/openai/threads', [OpenAiService::class, 'listThreads'])->name('openai.listThreads');

     // Nueva ruta para verificar si un mensaje es censurado
    Route::post('/check-message', [MessageController::class, 'checkMessage'])->name('check.message');
    });
});
