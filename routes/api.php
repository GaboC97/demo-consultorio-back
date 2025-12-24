<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PathologyController;
use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // CRUDs principales en español
    Route::apiResource('pacientes', PatientController::class);
    Route::apiResource('consultas', ConsultationController::class);
    Route::apiResource('patologias', PathologyController::class);
    Route::apiResource('alergias', AllergyController::class);
    Route::apiResource('turnos', TurnoController::class);
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/turnos/{turno}/aceptar', [TurnoController::class, 'aceptarDerivacion']);
    Route::post('pacientes/{paciente}/background', [PatientController::class, 'updateBackground']);
    Route::get('consultas/{id}/pdf', [PdfController::class, 'imprimirConsulta']);
    Route::put('/perfil', [AuthController::class, 'updateProfile']);
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStats']);
    Route::delete('/turnos/adjuntos/{adjunto}', [TurnoController::class, 'destroyAdjunto']);
    // Salida
    Route::post('/logout', [AuthController::class, 'logout']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);

    Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);

    // Messages
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);

    Route::post('/conversations/{conversationId}/messages/read', [MessageController::class, 'markAsRead']);


    // Ruta específica para el historial de un paciente
    // Nota: El parámetro {patient} puede quedar así, es un nombre de variable interna
    Route::get('pacientes/{patient}/historial', [ConsultationController::class, 'history']);
});
