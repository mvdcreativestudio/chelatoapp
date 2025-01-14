<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\PedidosYaController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ScanntechController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AccountingController;


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

// Pymo Webhook
Route::post('/pymo/webhook', [AccountingController::class, 'webhook']);

// WhatsApp
Route::post('/send-message', [WhatsAppController::class, 'send'])->name('api.send.messages');

// MercadoPago WebHooks
Route::post('/mpagohook', [MercadoPagoController::class, 'webhooks'])->name('mpagohook');

// Pedidos Ya
Route::post('/pedidos-ya/estimate-order', [PedidosYaController::class, 'estimateOrder'])->name('api.pedidos-ya.estimate-order');
Route::post('/pedidos-ya/confirm-order', [PedidosYaController::class, 'confirmOrder'])->name('api.pedidos-ya.confirm-order');
Route::get('/get-pedidosya-key/{store_id}', [PedidosYaController::class, 'getApiKey']);


// Rutas Api Pos
Route::get('/pos/token', [PosController::class, 'getPosToken']);
Route::get('/pos/responses', [PosController::class, 'getPosResponses']);
Route::get('/pos/get-provider/{store_id}', [PosController::class, 'getProviderByStoreId']);
Route::post('/pos/process-transaction', [PosController::class, 'processTransaction']);
Route::post('/pos/check-transaction-status', [PosController::class, 'checkTransactionStatus']);
Route::post('pos/reverse', [PosController::class, 'reverseTransaction']);
Route::post('/pos/void', [PosController::class, 'voidTransaction']);
Route::post('/pos/poll-void-status', [PosController::class, 'pollVoidStatus']);
Route::get('/pos/get-device-info/{cashRegisterId}', [PosController::class, 'getDeviceInfo']);
Route::post('/pos/devices/sync', [PosController::class, 'sync'])->name('posDevices.sync');
Route::delete('/pos/devices/{id}', [PosController::class, 'delete'])->name('posDevices.delete');
Route::get('/pos-devices', [PosController::class, 'getPosDevices'])->name('pos.devices');
Route::post('/pos/fetchTransactionHistory', [PosController::class, 'fetchTransactionHistory'])->name('pos.fetchTransactionHistory');
Route::get('/pos/devices/{store_id}', [PosController::class, 'getDevicesByStore']);
Route::post('/pos/refund', [PosController::class, 'processRefund'])->name('pos.refund');

// Rutas para consulta de lotes
Route::post('/pos/fetchBatchCloses', [PosController::class, 'fetchBatchCloses'])->name('pos.fetchBatchCloses');
Route::get('/pos/batchTransactions', [PosController::class, 'getBatchTransactions'])->name('pos.batchTransactions');
Route::post('/pos/fetchOpenBatches', [PosController::class, 'fetchOpenBatches'])->name('pos.fetchOpenBatches');








Route::post('/payment/process', [PaymentController::class, 'processPayment']);
