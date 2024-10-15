<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\PedidosYaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScanntechController;
use App\Http\Controllers\PosController;



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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp Webhook
Route::get('/webhook', [WhatsAppController::class, 'webhook']);
Route::post('/webhook', [WhatsAppController::class, 'recibe']);

// WhatsApp
Route::post('/send-message', [WhatsAppController::class, 'send'])->name('api.send.messages');

// MercadoPago WebHooks
Route::post('/mpagohook', [MercadoPagoController::class, 'webhooks'])->name('mpagohook');

// Pedidos Ya
Route::post('/pedidos-ya/estimate-order', [PedidosYaController::class, 'estimateOrder'])->name('api.pedidos-ya.estimate-order');
Route::post('/pedidos-ya/confirm-order', [PedidosYaController::class, 'confirmOrder'])->name('api.pedidos-ya.confirm-order');
Route::get('/get-pedidosya-key/{store_id}', [PedidosYaController::class, 'getApiKey']);


// Scanntech
Route::get('/scanntech/scanntech_responses', [ScanntechController::class, 'getScanntechResponses']);
Route::get('/scanntech/token', [ScanntechController::class, 'getToken']);
Route::post('/scanntech/purchase', [ScanntechController::class, 'postPurchase']);
Route::post('/scanntech/transaction-state', [ScanntechController::class, 'getTransactionState']);

// Pos
Route::get('/pos/token', [PosController::class, 'getPosToken']);
Route::get('/pos/responses', [PosController::class, 'getPosResponses']);
Route::post('/pos/process-transaction', [PosController::class, 'processTransaction']);
Route::post('/pos/check-transaction-status', [PosController::class, 'checkTransactionStatus']);



Route::post('/payment/process', [PaymentController::class, 'processPayment']);





