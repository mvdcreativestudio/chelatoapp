@extends('layouts.layoutMaster')

@section('title', 'Ventas de la Caja Registradora')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('content')
<div class="container-fluid p-0">
    @hasrole('Administrador')
    <!-- Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">Resumen de Ventas</h4>
                    <p class="text-muted mb-0">Detalle de operaciones realizadas</p>
                </div>
                <button id="export-pdf-btn" class="btn btn-primary" data-id="{{ $id }}">
                    <i class="bx bxs-file-pdf me-1"></i>Exportar PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Ventas -->
    <div class="row g-4 mb-4">
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Ventas Totales</p>
                            <h3 class="mb-0">{{ $totalSales }}</h3>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-shopping-bag fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Efectivo</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($cashSales, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-money fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">POS</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($posSales, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-credit-card fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Mercadopago</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($mercadopagoSales, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bxl-paypal fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Transferencias</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($bankTransferSales, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-transfer fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Cuenta Corriente</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($internalCreditSales, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-secondary rounded p-2">
                            <i class="bx bx-notepad fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
          <div class="card border-2 border-primary">
              <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between">
                      <div>
                          <p class="mb-2 text-muted fw-semibold">TOTAL VENDIDO</p>
                          <h3 class="mb-0 text-primary">{{ $settings->currency_symbol }}{{ number_format($totalSalesAmount, 0, ',', '.') }}</h3>
                      </div>
                      <div class="avatar bg-label-primary rounded p-2">
                          <i class="bx bx-line-chart fs-3"></i>
                      </div>
                  </div>
              </div>
          </div>
        </div>
    </div>

    <!-- Fila de Gastos y Balance -->
    <div class="row g-4 mb-4">
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Total Gastos</p>
                            <h3 class="mb-0">{{ $cashRegisterExpenses->count() }}</h3>
                        </div>
                        <div class="avatar bg-label-warning rounded p-2">
                            <i class="bx bx-receipt fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Monto Gastos</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($expenses, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-danger rounded p-2">
                            <i class="bx bx-wallet fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Fondo de Caja</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($cashFloat, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-secondary rounded p-2">
                            <i class="bx bx-calculator fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Efectivo Final</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($cashFloat + $cashSales - $expenses, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-success rounded p-2">
                            <i class="bx bx-money-withdraw fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Verificacion de efectivo (solo si la caja esta cerrada y hay datos) --}}
    @if(isset($cashRegisterLog) && $cashRegisterLog->close_time && $cashRegisterLog->actual_cash !== null)
    <div class="row g-4 mb-4">
        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Efectivo Esperado</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($cashFloat + $cashSales - $expenses, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-primary rounded p-2">
                            <i class="bx bx-calculator fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Efectivo Contado</p>
                            <h3 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($cashRegisterLog->actual_cash, 0, ',', '.') }}</h3>
                        </div>
                        <div class="avatar bg-label-info rounded p-2">
                            <i class="bx bx-money fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card {{ $cashRegisterLog->cash_difference == 0 ? '' : ($cashRegisterLog->cash_difference > 0 ? 'border-warning' : 'border-danger') }}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">
                                @if($cashRegisterLog->cash_difference == 0)
                                    Diferencia
                                @elseif($cashRegisterLog->cash_difference > 0)
                                    Sobrante
                                @else
                                    Faltante
                                @endif
                            </p>
                            <h3 class="mb-0 {{ $cashRegisterLog->cash_difference == 0 ? 'text-success' : ($cashRegisterLog->cash_difference > 0 ? 'text-warning' : 'text-danger') }}">
                                @if($cashRegisterLog->cash_difference == 0)
                                    Cuadra
                                @else
                                    {{ $settings->currency_symbol }}{{ number_format(abs($cashRegisterLog->cash_difference), 0, ',', '.') }}
                                @endif
                            </h3>
                        </div>
                        <div class="avatar {{ $cashRegisterLog->cash_difference == 0 ? 'bg-label-success' : ($cashRegisterLog->cash_difference > 0 ? 'bg-label-warning' : 'bg-label-danger') }} rounded p-2">
                            @if($cashRegisterLog->cash_difference == 0)
                                <i class="bx bx-check-circle fs-3"></i>
                            @elseif($cashRegisterLog->cash_difference > 0)
                                <i class="bx bx-up-arrow-alt fs-3"></i>
                            @else
                                <i class="bx bx-down-arrow-alt fs-3"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Table Card -->
    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-4">
                    <h5 class="card-title mb-0">Registro de Ventas y Egresos</h5>
                </div>
                <div class="col-md-8 d-flex gap-2 align-items-center justify-content-end">
                    <div class="input-group" style="max-width: 300px;">
                        <input type="time" id="start-time" class="form-control" placeholder="Hora inicio">
                        <input type="time" id="end-time" class="form-control" placeholder="Hora fin">
                        <button id="filter-button" class="btn btn-sm btn-primary">
                            <i class="bx bx-filter-alt me-1"></i>Filtrar
                        </button>
                    </div>
                    <div class="btn-group">
                      <button id="btnViewGrid" class="btn btn-sm btn-outline-primary active">
                          <i class="bx bx-grid-alt"></i> Tarjetas
                      </button>
                      <button id="btnViewList" class="btn btn-sm btn-outline-primary">
                          <i class="bx bx-list-ul"></i> Lista
                      </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="pt-4"></div>

    <!-- Vista en Lista -->
    <div id="viewList" class="d-none">
      <table class="table table-striped">
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Tipo</th>
                  <th>Fecha</th>
                  <th>Hora</th>
                  <th>Cliente/Concepto</th>
                  <th>Metodo</th>
                  <th>Total</th>
              </tr>
          </thead>
          <tbody>
              @forelse($sales as $sale)
              <tr>
                  <td>{{ $sale->id }}</td>
                  <td><span class="badge bg-label-success">Venta</span></td>
                  <td>{{ \Carbon\Carbon::parse($sale->date)->translatedFormat('d \d\e F Y') }}</td>
                  <td>{{ $sale->hour ? \Carbon\Carbon::parse($sale->hour)->format('h:i a') : '-' }}</td>
                  <td>{{ $sale->client ? ($sale->client->type == 'individual' ? $sale->client->name . ' ' . $sale->client->lastname : $sale->client->company_name) : 'N/A' }}</td>
                  <td>
                      @php
                          $methodLabels = [
                              'cash' => ['label' => 'Efectivo', 'color' => 'success', 'icon' => 'bx-money'],
                              'credit' => ['label' => 'POS (Credito)', 'color' => 'info', 'icon' => 'bx-credit-card'],
                              'debit' => ['label' => 'POS (Debito)', 'color' => 'info', 'icon' => 'bx-credit-card'],
                              'mercadopago' => ['label' => 'Mercadopago', 'color' => 'primary', 'icon' => 'bxl-paypal'],
                              'bankTransfer' => ['label' => 'Transferencia', 'color' => 'warning', 'icon' => 'bx-transfer'],
                              'internalCredit' => ['label' => 'Cuenta Cte.', 'color' => 'secondary', 'icon' => 'bx-notepad'],
                          ];
                          $method = $methodLabels[$sale->payment_method] ?? ['label' => 'Otro', 'color' => 'secondary', 'icon' => 'bx-question-mark'];
                      @endphp
                      <span class="badge bg-label-{{ $method['color'] }}">
                          <i class="bx {{ $method['icon'] }} me-1"></i>{{ $method['label'] }}
                      </span>
                  </td>
                  <td>{{ $settings->currency_symbol }}{{ number_format($sale->total, 0, ',', '.') }}</td>
              </tr>
              @empty
              @endforelse

              @forelse($cashRegisterExpenses as $expense)
              <tr>
                  <td>{{ $expense->id }}</td>
                  <td><span class="badge bg-label-danger">Egreso</span></td>
                  <td>{{ \Carbon\Carbon::parse($expense->created_at)->translatedFormat('d \d\e F Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($expense->created_at)->format('h:i a') }}</td>
                  <td>{{ $expense->concept ?? '-' }}</td>
                  <td><span class="badge bg-label-warning">{{ $expense->currency === 'Dolar' ? 'Dolar' : 'Efectivo' }}</span></td>
                  <td class="text-danger">
                      @if($expense->currency === 'Dolar')
                          -{{ $settings->currency_symbol }}{{ number_format($expense->amount * ($expense->currency_rate ?? 1), 0, ',', '.') }}
                          <br><small class="text-muted">(US${{ number_format($expense->amount, 2) }})</small>
                      @else
                          -{{ $settings->currency_symbol }}{{ number_format($expense->amount, 0, ',', '.') }}
                      @endif
                  </td>
              </tr>
              @empty
              @endforelse

              @if($sales->isEmpty() && $cashRegisterExpenses->isEmpty())
              <tr>
                  <td colspan="7" class="text-center">No hay registros</td>
              </tr>
              @endif
          </tbody>
      </table>
    </div>

    <!-- Vista en Cards -->
    <div id="viewGrid" class="card-body">
        <div class="row g-4">
            @forelse($sales as $sale)
            <div class="col-md-6 col-lg-4 col-xl-4 sale-card" data-time="{{ $sale->hour ? \Carbon\Carbon::parse($sale->hour)->format('H:i') : '' }}">
                <div class="clients-card-container">
                    <div class="clients-card position-relative">
                        <div class="card-header bottom p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        @php
                                            $methodIcons = [
                                                'cash' => ['bg' => 'bg-label-success', 'icon' => 'bx-money'],
                                                'credit' => ['bg' => 'bg-label-info', 'icon' => 'bx-credit-card'],
                                                'debit' => ['bg' => 'bg-label-info', 'icon' => 'bx-credit-card'],
                                                'card' => ['bg' => 'bg-label-info', 'icon' => 'bx-credit-card'],
                                                'mercadopago' => ['bg' => 'bg-label-primary', 'icon' => 'bxl-paypal'],
                                                'bankTransfer' => ['bg' => 'bg-label-warning', 'icon' => 'bx-transfer'],
                                                'internalCredit' => ['bg' => 'bg-label-secondary', 'icon' => 'bx-notepad'],
                                            ];
                                            $mi = $methodIcons[$sale->payment_method] ?? ['bg' => 'bg-label-secondary', 'icon' => 'bx-question-mark'];
                                        @endphp
                                        <span class="avatar-initial rounded-circle {{ $mi['bg'] }}">
                                            <i class="bx {{ $mi['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-0">Venta #{{ $sale->id }}</h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge {{ $mi['bg'] }} me-2">
                                        {{ $methodLabels[$sale->payment_method]['label'] ?? 'Otro' }}
                                    </span>
                                    <div class="clients-card-toggle" style="cursor:pointer;">
                                        <i class="bx bx-chevron-down fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="clients-card-body" style="display: none;">
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-calendar text-muted"></i>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($sale->date)->translatedFormat('d \d\e F Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-time text-muted"></i>
                                        <span class="text-muted">{{ $sale->hour ? \Carbon\Carbon::parse($sale->hour)->format('h:i a') : '-' }}</span>
                                    </div>
                                    @if($sale->client)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-user text-muted"></i>
                                        <span class="text-muted">Cliente:
                                            {{ $sale->client->type == 'individual' ? $sale->client->name . ' ' . $sale->client->lastname : $sale->client->company_name }}
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                @if(!empty($sale->products))
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Productos</h6>
                                    <div class="list-group list-group-flush">
                                        @foreach($sale->products as $product)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                            <div class="text-truncate" style="max-width: 150px;" title="{{ $product['name'] }}">
                                                {{ $product['name'] }}
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-label-primary">x{{ $product['quantity'] }}</span>
                                                <span class="text-end" style="min-width: 80px;">
                                                    {{ $settings->currency_symbol }}{{ number_format($product['price'] ?? $product['unit_price'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if($sale->notes)
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Notas</h6>
                                    <p class="text-muted small mb-0">{{ Str::limit($sale->notes, 100) }}</p>
                                </div>
                                @endif
                            </div>

                            <div class="card-footer bg-light border-top p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Total</span>
                                    <h5 class="mb-0 fw-semibold">
                                        {{ $settings->currency_symbol }}{{ number_format($sale->total, 0, ',', '.') }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            @endforelse

            {{-- Mostrar gastos como tarjetas --}}
            @forelse($cashRegisterExpenses as $expense)
            <div class="col-md-6 col-lg-4 col-xl-4 sale-card" data-time="{{ \Carbon\Carbon::parse($expense->created_at)->format('H:i') }}">
                <div class="clients-card-container">
                    <div class="clients-card position-relative">
                        <div class="card-header bottom p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-danger">
                                            <i class="bx bx-wallet"></i>
                                        </span>
                                    </div>
                                    <h6 class="mb-0">Egreso #{{ $expense->id }}</h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-danger me-2">Gasto</span>
                                    <div class="clients-card-toggle" style="cursor:pointer;">
                                        <i class="bx bx-chevron-down fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="clients-card-body" style="display: none;">
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-calendar text-muted"></i>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($expense->created_at)->translatedFormat('d \d\e F Y') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-time text-muted"></i>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($expense->created_at)->format('h:i a') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-receipt text-muted"></i>
                                        <span class="text-muted">Concepto: {{ $expense->concept ?? '-' }}</span>
                                    </div>
                                    @if($expense->supplier)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-store text-muted"></i>
                                        <span class="text-muted">Proveedor: {{ $expense->supplier->name }}</span>
                                    </div>
                                    @endif
                                    @if($expense->expenseCategory)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-category text-muted"></i>
                                        <span class="text-muted">Categoria: {{ $expense->expenseCategory->name }}</span>
                                    </div>
                                    @endif
                                    @if($expense->observations)
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-note text-muted"></i>
                                        <span class="text-muted">Notas: {{ Str::limit($expense->observations, 50) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card-footer bg-light border-top p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Monto</span>
                                    <h5 class="mb-0 fw-semibold text-danger">
                                        @if($expense->currency === 'Dolar')
                                            -{{ $settings->currency_symbol }}{{ number_format($expense->amount * ($expense->currency_rate ?? 1), 0, ',', '.') }}
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                (US${{ number_format($expense->amount, 2) }} x {{ number_format($expense->currency_rate ?? 1, 2) }})
                                            </small>
                                        @else
                                            -{{ $settings->currency_symbol }}{{ number_format($expense->amount, 0, ',', '.') }}
                                        @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            @endforelse

            @if($sales->isEmpty() && $cashRegisterExpenses->isEmpty())
            <div class="col-12">
                <div class="text-center py-5">
                    <h5 class="mb-2">No hay ventas ni egresos registrados</h5>
                    <p class="text-muted">No se encontraron registros para mostrar</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-lock-alt text-danger mb-3" style="font-size: 3rem;"></i>
            <h4>Acceso Restringido</h4>
            <p class="text-muted">No tienes permisos para acceder a esta seccion.</p>
        </div>
    </div>
    @endhasrole
</div>
@endsection

@section('page-script')
<script>
var baseUrl = "{{ url('/') }}/admin/";

$(document).ready(function() {
    // Toggle cards
    $(document).on('click', '.clients-card-toggle', function () {
        let card = $(this).closest('.clients-card');
        let body = card.find('.clients-card-body');
        let icon = $(this).find('i');

        $('.clients-card-body').not(body).slideUp(200);
        $('.clients-card-toggle i').not(icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');

        body.stop(true, true).slideToggle(200);
        icon.toggleClass('bx-chevron-down bx-chevron-up');
    });

    // Toggle views
    $('#btnViewGrid').click(function () {
        $('#viewGrid').removeClass('d-none');
        $('#viewList').addClass('d-none');
        $(this).addClass('active');
        $('#btnViewList').removeClass('active');
    });

    $('#btnViewList').click(function () {
        $('#viewList').removeClass('d-none');
        $('#viewGrid').addClass('d-none');
        $(this).addClass('active');
        $('#btnViewGrid').removeClass('active');
    });

    // Time filter
    $('#filter-button').click(function() {
        var startTime = $('#start-time').val();
        var endTime = $('#end-time').val();

        if (!startTime || !endTime) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Ambas horas deben ser ingresadas para filtrar.', confirmButtonClass: 'btn btn-danger' });
            return;
        }

        // Filter cards
        $('.sale-card').each(function() {
            var time = $(this).data('time');
            if (time >= startTime && time <= endTime) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Filter table rows
        $('#viewList tbody tr').each(function() {
            var hourCell = $(this).find('td:eq(3)').text().trim();
            // Simple comparison
            $(this).show();
        });
    });

    // Export PDF
    $('#export-pdf-btn').on('click', function () {
        var id = $(this).data('id');
        $.ajax({
            url: baseUrl + 'point-of-sale/details/sales/pdf/' + id,
            method: 'GET',
            xhrFields: { responseType: 'blob' },
            success: function (response) {
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(response);
                link.download = 'cash_register_sales_log_' + id + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function () {
                Swal.fire('Error!', 'No se pudo generar el PDF.', 'error');
            }
        });
    });
});
</script>
@endsection
