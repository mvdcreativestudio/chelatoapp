# Contexto de Sesión - Chelato App

## Resumen General

ChelatoApp es un sistema de gestión comercial (POS + e-commerce) en Laravel. Durante esta sesión se realizaron múltiples mejoras centradas en facturación electrónica (SICFE + PyMo), PDV y gestión de órdenes.

---

## Repositorios Relacionados

- **ChelatoApp**: `/home/mvd/chelatoapp` (este repo)
- **Penfer**: `/home/mvd/penfer` (base similar, se usó como referencia para SICFE)
- **Sumeria**: `/home/mvd/sumeria` (se usó como referencia para impresión 80mm)

---

## Cambios Realizados en Esta Sesión

### 1. Integración SICFE (Facturación Electrónica)

Se trajo toda la lógica de facturación SICFE desde Penfer. Chelato ahora soporta **PyMo y SICFE** como proveedores de facturación electrónica.

**Archivos clave creados:**
- `app/Services/Billing/BillingServiceInterface.php` - Interface común
- `app/Services/Billing/BillingServiceResolver.php` - Resuelve PyMo vs SICFE según store
- `app/Services/Billing/SicfeBillingService.php` - Implementación SICFE
- `app/Services/Billing/PymoBillingService.php` - Wrapper PyMo
- `app/Services/Billing/Sicfe/SicfeSoapClient.php` - Cliente SOAP
- `app/Services/Billing/Sicfe/SicfeDtoBuilder.php` - Builder de DTOs
- `app/Builders/Sicfe/FacturaXmlBuilder.php` - Generador XML
- `app/Repositories/SicfeRepository.php` - Repositorio SICFE
- `app/Dtos/Sicfe/*.php` - DTOs (FacturaDto, CfeDto, IdDocDto, EmisorDto, ReceptorDto, TotalesDto, ItemDto, CaeDataDto)
- `app/Models/BillingProvider.php`, `app/Models/BillingCredential.php`
- `resources/assets/js/app-integration-sicfe.js` - JS para config SICFE en edición de tienda
- Migraciones para billing_providers, billing_credentials, billing_provider_id en stores

**Archivos modificados:**
- `app/Models/Store.php` - Relaciones billingProvider, billingCredentials, campo billing_provider_id, auto_print_ticket, business_name
- `app/Http/Controllers/AccountingController.php` - Usa BillingServiceResolver para todas las operaciones
- `app/Repositories/OrderRepository.php` - emitCFE usa BillingServiceResolver
- `routes/web.php` - Rutas SICFE config, conexión, descarga PDF, impresión 80mm
- `resources/views/stores/edit.blade.php` - UI para config SICFE, toggles facturación automática y auto-impresión

### 2. Impresión Ticket 80mm

Se creó una vista local para generar tickets 80mm (no depende del PDF de SICFE en A4).

**Archivos clave:**
- `resources/views/invoices/pdf/cfe_80mm.blade.php` - Vista del ticket con:
  - Encabezado: business_name (razón social), RUT, dirección del store
  - Tipo de CFE con códigos numéricos (101=e-Ticket, 102=NC e-Ticket, 111=e-Factura, 112=NC e-Factura, etc.)
  - Productos de la orden (parseados del JSON de orders.products)
  - Totales con descuentos y cupones
  - Datos del cliente (CI/RUT)
  - Datos CAE (serie, número, rango, hash, QR)
  - Referencia al CFE original si es nota de crédito/débito
- Ruta: `GET /admin/invoices/print80mm/{id}` → `AccountingController::printCfePdf`

### 3. Campo business_name (Razón Social) en Stores

- Migración: `2025_07_10_000001_add_business_name_to_stores_table.php`
- Se usa en el ticket 80mm como encabezado fiscal (en vez del nombre de la tienda)
- Campo en la vista de edición de tienda

### 4. Client_id Nullable - Consumidor Final

Se eliminó el cliente "NA" hardcodeado. Ahora las órdenes sin cliente tienen `client_id = null`.

**Cambios:**
- Migración: `2025_07_10_000002_make_client_id_nullable_in_orders_table.php`
- `app/Repositories/OrderRepository.php` - `store()` permite client_id null
- `app/Repositories/OrderRepository.php` - `getOrdersForDataTable()` usa LEFT JOIN + COALESCE para mostrar "Consumidor Final"
- `app/Repositories/OrderRepository.php` - `getClientOrdersCount()` acepta `?int`
- `app/Http/Requests/StoreOrderRequest.php` - address, phone, email son nullable
- `resources/assets/js/pdvCheckout.js` - Envía null en vez de datos fake ("N/A", "no@email.com")
- `resources/views/content/e-commerce/backoffice/orders/show-order.blade.php` - Muestra "Consumidor Final" cuando no hay cliente

### 5. Auto-Impresión de Ticket Post-Venta

Configurable por tienda. Si está activo, después de cada venta en el PDV se abre automáticamente la ventana de impresión 80mm.

**Cambios:**
- Migración: `2025_07_10_000003_add_auto_print_ticket_to_stores_table.php`
- `app/Models/Store.php` - Campo `auto_print_ticket` en fillable
- `app/Http/Controllers/OrderController.php` - Response incluye `is_billed`, `invoice_id`, `auto_print_ticket`
- `resources/assets/js/pdvCheckout.js` - Si `auto_print_ticket && is_billed`, abre ventana 80mm y ejecuta print()
- `resources/views/stores/edit.blade.php` - Sección "Configuración de Facturación" con toggles para:
  - Facturación Automática (`automatic_billing`)
  - Impresión automática de ticket (`auto_print_ticket`)

### 6. Notas de Crédito y Débito

Se implementó la emisión de notas de crédito/débito desde la vista de la orden.

**Archivos clave:**
- `resources/views/content/e-commerce/backoffice/orders/modal-emitir-nota.blade.php` - Modal con campos: tipo (crédito/débito), monto, razón, fecha
- `resources/views/content/e-commerce/backoffice/orders/show-order.blade.php`:
  - Botón "Emitir Nota" (visible cuando orden está facturada)
  - Historial de Facturación (tabla con todos los CFEs: facturas + notas)
  - Badge "Reembolsado" cuando payment_status = 'refunded'
  - Cada CFE tiene botones de descarga A4 e impresión 80mm
- `app/Http/Controllers/AccountingController.php` - `emitNote()` usa BillingServiceResolver, soporta AJAX
- `app/Repositories/AccountingRepository.php` - `emitNote()` actualiza balance del CFE original; si queda en 0 marca orden como 'refunded'
- `app/Repositories/SicfeRepository.php` - Mismo comportamiento de refund para SICFE
- Ruta existente: `POST /admin/invoices/{invoice}/emit-note`
- Request: `app/Http/Requests/EmitNoteRequest.php` (noteType, noteAmount, reason)

**Flujo:**
1. Orden facturada → botón "Emitir Nota"
2. Modal → seleccionar tipo, monto, razón
3. Backend resuelve proveedor (PyMo/SICFE) y emite
4. Balance del CFE original se actualiza
5. Si balance = 0 (NC total) → orden pasa a payment_status = 'refunded'
6. La nota aparece en el historial con opciones de descarga/impresión

### 7. Descarga de Factura PDF desde Show Order

- Botón dropdown "Factura" con opciones: Descargar PDF (A4) e Imprimir en 80mm
- Visible solo cuando la orden está facturada
- Ruta: `GET /admin/invoices/download/{id}` → `AccountingController::downloadCfePdf`

---

## Códigos de Tipo CFE (DGI Uruguay)

| Código | Tipo |
|--------|------|
| 101 | e-Ticket |
| 102 | Nota de Crédito de e-Ticket |
| 103 | Nota de Débito de e-Ticket |
| 111 | e-Factura |
| 112 | Nota de Crédito de e-Factura |
| 113 | Nota de Débito de e-Factura |
| 121 | e-Factura de Exportación |
| 122 | NC e-Factura de Exportación |
| 123 | ND e-Factura de Exportación |

**Estados DGI:** AE (Aceptado), BE (Rechazado), CE (Observado), PE (Pendiente), EN (Enviado), RE (Rechazado sobre), NA (No aplica)

---

## Plan Pendiente: Unificación del PDV

Existe un plan detallado en `.claude/plans/sparkling-sniffing-galaxy.md` para unificar el PDV en una sola pantalla:
- Actualmente: 2 páginas (front.blade.php + front2.blade.php) con 2 JS (~2020 líneas)
- Objetivo: 1 pantalla split-panel (productos izq + carrito/checkout der) con 1 JS (~900 líneas)
- Layout, estructura JS, checklist de funcionalidades y pasos están definidos
- **Estado: pendiente de aprobación para implementar**

---

## Estructura de Archivos Clave

```
app/
├── Builders/Sicfe/FacturaXmlBuilder.php
├── Dtos/Sicfe/ (8 archivos DTO)
├── Http/
│   ├── Controllers/
│   │   ├── AccountingController.php (facturación, PDF, notas)
│   │   ├── CashRegisterLogController.php (PDV)
│   │   ├── OrderController.php (órdenes)
│   │   └── PosOrderController.php (órdenes POS)
│   └── Requests/
│       ├── StoreOrderRequest.php
│       ├── StorePosOrderRequest.php
│       └── EmitNoteRequest.php
├── Models/
│   ├── Store.php (billing_provider_id, auto_print_ticket, business_name)
│   ├── Order.php (client_id nullable, invoices() → HasMany CFE)
│   ├── CFE.php (type, serie, nro, balance, main_cfe_id, reason, mainCfe(), relatedCfes())
│   ├── BillingProvider.php
│   └── BillingCredential.php
├── Repositories/
│   ├── OrderRepository.php (store, emitCFE, getOrdersForDataTable con LEFT JOIN)
│   ├── AccountingRepository.php (PyMo: emitNote, getCfePdf, etc.)
│   ├── SicfeRepository.php (SICFE: emitCFE, emitNote, getCfePdf, etc.)
│   └── CashRegisterLogRepository.php
├── Services/Billing/
│   ├── BillingServiceInterface.php
│   ├── BillingServiceResolver.php
│   ├── PymoBillingService.php
│   ├── SicfeBillingService.php
│   └── Sicfe/ (SicfeSoapClient, SicfeDtoBuilder)
resources/
├── assets/js/
│   ├── pdv.js (~800 líneas, productos/carrito)
│   ├── pdvCheckout.js (~1221 líneas, checkout/pago)
│   ├── app-integration-sicfe.js (config SICFE)
│   └── app-ecommerce-order-details.js (detalle orden + modal nota)
├── views/
│   ├── content/e-commerce/backoffice/orders/
│   │   ├── show-order.blade.php (vista orden con historial facturación)
│   │   ├── bill-order.blade.php (modal emitir factura)
│   │   └── modal-emitir-nota.blade.php (modal nota crédito/débito)
│   ├── invoices/pdf/cfe_80mm.blade.php (ticket 80mm)
│   ├── stores/edit.blade.php (edición tienda con config SICFE y facturación)
│   └── pdv/ (front.blade.php, front2.blade.php, index.blade.php)
```

---

## Notas Técnicas

- SICFE usa SOAP, PyMo usa REST
- Credenciales SICFE en tabla `billing_credentials` (encriptadas)
- Credenciales PyMo directamente en `stores` (pymo_user, pymo_password, pymo_branch_office)
- `BillingServiceResolver` determina qué servicio usar según `store.billing_provider_id` (1=PyMo, 2=SICFE)
- Extensión PHP `soap` requerida para SICFE
- Los productos de las órdenes se almacenan como JSON en `orders.products` (no hay tabla pivot `order_products`)
- El campo `type` de CFE almacena códigos numéricos (101, 102, etc.), no strings
