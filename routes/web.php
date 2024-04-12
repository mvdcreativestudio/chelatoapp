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

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('content.dashboard.dashboard-mvd');
    })->name('dashboard');
    Route::get('/clients/datatable', [ClientController::class, 'datatable'])->name('clients.datatable');
    Route::get('/products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
    Route::get('/product-categories/datatable', [ProductCategoryController::class, 'datatable'])->name('product-categories.datatable');

    // Tiendas / Franquicias
    Route::resource('stores', StoreController::class);
    Route::group(['prefix' => 'stores'], function () {
      Route::get('/{store}/manage-users', [StoreController::class, 'manageUsers'])->name('stores.manageUsers');
      Route::post('/{store}/associate-user', [StoreController::class, 'associateUser'])->name('stores.associateUser');
      Route::post('/{store}/disassociate-user', [StoreController::class, 'disassociateUser'])->name('stores.disassociateUser');
    });

    // Roles
    Route::resource('/roles', RoleController::class);
    Route::group(['prefix' => 'roles'], function () {
      Route::get('/{role}/manage-users', [RoleController::class, 'manageUsers'])->name('roles.manageUsers');
      Route::post('/{role}/associate-user', [RoleController::class, 'associateUser'])->name('roles.associateUser');
      Route::post('/{role}/disassociate-user', [RoleController::class, 'disassociateUser'])->name('roles.disassociateUser');
      Route::get('/{role}/manage-permissions', [RoleController::class, 'managePermissions'])->name('roles.managePermissions');
      Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');
    });

    // Materias Primas
    Route::resource('raw-materials', RawMaterialController::class);

    // Proveedores
    Route::resource('suppliers', SupplierController::class);

    // Ordenes de Compra
    Route::resource('supplier-orders', SupplierOrderController::class);
    Route::group(['prefix' => 'supplier-orders'], function () {
      Route::get('/{id}/pdf', [SupplierOrderController::class, 'generatePdf'])->name('supplier-orders.generatePdf');
    });

    // Omnicanalidad
    Route::group(['prefix' => 'omnichannel'], function () {
      // Configuración de WhatsApp
      Route::post('/update-meta-business-id', [OmnichannelController::class, 'updateMetaBusinessId'])->name('omnichannel.update.meta.business.id');
      Route::post('/update-admin-token', [OmnichannelController::class, 'updateMetaAdminToken'])->name('omnichannel.update.admin.token');

      // Asociar / Desasociar números de teléfono
      Route::post('/associate-phone', [OmnichannelController::class, 'associatePhoneNumberToStore'])->name('omnichannel.associate.phone');
      Route::post('/disassociate/{phone_id}', [OmnichannelController::class, 'disassociatePhoneNumberFromStore'])->name('omnichannel.disassociate');

      // Configuración
      Route::get('/settings', [OmnichannelController::class, 'settings'])->name('omnichannel.settings');

      // Chat
      Route::get('/', [OmnichannelController::class, 'chats'])->name('omnichannel.chat');
      Route::get('/fetch-messages', [WhatsAppController::class, 'fetchMessages'])->name('omnichannel.fetch.messages');
    });

});


// Clients
Route::resource('clients', ClientController::class);


// E-Commerce
Route::get('shop', [EcommerceController::class, 'index'])->name('shop');
Route::get('store', [EcommerceController::class, 'store'])->name('store');

// Cart
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');

Route::get('/session/clear', [CartController::class, 'clearSession'])->name('session.clear');

// Checkout
Route::resource('checkout', CheckoutController::class);


// E-Commerce - Backoffice

// E-Commerce - Products
Route::resource('products', ProductController::class);

// E-Commerce - Categories
Route::resource('product-categories', ProductCategoryController::class);

// E-Commerce - Orders
Route::resource('orders', OrderController::class);

Route::get('/ecommerce/marketing', [EcommerceController::class, 'marketing'])->name('marketing');
Route::get('/ecommerce/settings', [EcommerceController::class, 'settings'])->name('settings');

// CRM
Route::get('crm', [CrmController::class, 'index'])->name('crm');

// Contabilidad
Route::get('receipts', [AccountingController::class, 'receipts'])->name('receipts');
Route::get('entries', [AccountingController::class, 'entries'])->name('entries');
Route::get('entrie', [AccountingController::class, 'entrie'])->name('entrie');


 // Invoices
 Route::resource('invoices', InvoiceController::class);
