<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;

use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\OmnichannelController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\OrderController;

// Cambio de Idioma
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Autenticación y Verificación de Email
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('content.dashboard.dashboard-mvd');
    })->name('dashboard');

    // Dashboard Data Tables
    Route::get('/clients/datatable', [ClientController::class, 'datatable'])->name('clients.datatable');
    Route::get('/products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
    Route::get('/product-categories/datatable', [ProductCategoryController::class, 'datatable'])->name('product-categories.datatable');


    // Tiendas / Franquicias
    Route::resource('stores', StoreController::class);
    Route::get('stores/{store}/manage-users', [StoreController::class, 'manageUsers'])->name('stores.manageUsers');
    Route::post('stores/{store}/associate-user', [StoreController::class, 'associateUser'])->name('stores.associateUser');
    Route::post('stores/{store}/disassociate-user', [StoreController::class, 'disassociateUser'])->name('stores.disassociateUser');

    // Roles
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/manage-users', [RoleController::class, 'manageUsers'])->name('roles.manageUsers');
    Route::post('roles/{role}/associate-user', [RoleController::class, 'associateUser'])->name('roles.associateUser');
    Route::post('roles/{role}/disassociate-user', [RoleController::class, 'disassociateUser'])->name('roles.disassociateUser');
    Route::get('roles/{role}/manage-permissions', [RoleController::class, 'managePermissions'])->name('roles.managePermissions');
    Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');


    // Materias Primas
    Route::resource('raw-materials', RawMaterialController::class);

    // Proveedores
    Route::resource('suppliers', SupplierController::class);

    // Ordenes de Compra
    Route::resource('supplier-orders', SupplierOrderController::class);
});

// Clients
Route::resource('clients', ClientController::class);


// Omnicanalidad
Route::get('omnichannel', [OmnichannelController::class, 'index'])->name('omnichannel');

// E-Commerce
Route::get('shop', [EcommerceController::class, 'index'])->name('shop');
Route::get('store/{storeId}', [EcommerceController::class, 'store'])->name('store');
Route::post('/cart/select-store', [CartController::class, 'selectStore'])->name('cart.selectStore');
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
Route::get('/session/clear', [CartController::class, 'clearSession'])->name('session.clear');
Route::resource('checkout', CheckoutController::class);
Route::get('/checkout/{orderId}/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/success/{orderId}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/pending', [CheckoutController::class, 'pending'])->name('checkout.pending');
Route::get('/failure', [CheckoutController::class, 'failure'])->name('checkout.failure');


// Omnicanalidad (Público)
Route::get('omnichannel', [OmnichannelController::class, 'index'])->name('omnichannel');

// MercadoPago WebHooks
Route::post('/mpagohook', [MercadoPagoController::class, 'webhooks'])->name('mpagohook');
