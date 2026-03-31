<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja - {{ $log->name }}</title>
    <style>
        html, body {
            width: 204pt;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: white;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            page-break-inside: avoid !important;
        }

        .container {
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header h4 {
            margin: 0;
            font-size: 16px;
        }

        .header p {
            margin: 2px 0;
            font-size: 12px;
        }

        .separator {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin: 6px 0 4px;
            text-transform: uppercase;
        }

        .row {
            display: table;
            width: 100%;
            font-size: 13px;
            margin: 2px 0;
        }

        .row .label {
            display: table-cell;
            text-align: left;
            width: 60%;
        }

        .row .value {
            display: table-cell;
            text-align: right;
            width: 40%;
            font-weight: bold;
        }

        .total-row {
            display: table;
            width: 100%;
            font-size: 15px;
            font-weight: bold;
            margin: 4px 0;
        }

        .total-row .label {
            display: table-cell;
            text-align: left;
            width: 60%;
        }

        .total-row .value {
            display: table-cell;
            text-align: right;
            width: 40%;
        }

        .difference-box {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            margin: 8px 0;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            margin-top: 10px;
        }

        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }
    </style>
</head>

@php
    $cs = $store->currency_symbol ?? '$';
    $fmt = function($v) {
        return number_format((float)($v ?? 0), 0, ',', '.');
    };

    $cashFloat = $log->cash_float ?? 0;
    $cashSales = $log->cash_sales ?? 0;
    $posSales = $log->pos_sales ?? 0;
    $mercadopagoSales = $log->mercadopago_sales ?? 0;
    $bankTransferSales = $log->bank_transfer_sales ?? 0;
    $internalCreditSales = $log->internal_credit_sales ?? 0;
    $totalExpenses = $log->cash_expenses ?? 0;
    $actualCash = $log->actual_cash;
    $cashDifference = $log->cash_difference ?? 0;

    $totalSales = $cashSales + $posSales + $mercadopagoSales + $bankTransferSales + $internalCreditSales;
    $expectedCash = $cashFloat + $cashSales - $totalExpenses;
@endphp

<body>
<div class="container">
    {{-- HEADER --}}
    <div class="header">
        @if(isset($logo))
            <img src="{{ $logo }}" alt="Logo" style="max-height: 70px; max-width: 80%; margin-bottom: 8px;">
        @endif
        <h4>{{ $store->business_name ?? $store->name ?? '' }}</h4>
        <p>{{ $store->address ?? '' }}</p>
        @if($store->phone)
            <p>Tel: {{ $store->phone }}</p>
        @endif
    </div>

    <hr class="separator">

    <div class="section-title">Cierre de Caja</div>
    <div style="text-align: center; font-size: 13px; margin-bottom: 4px;">
        <strong>{{ $log->name }}</strong>
    </div>

    <hr class="separator">

    {{-- HORARIOS --}}
    <div class="row">
        <span class="label">Apertura:</span>
        <span class="value">{{ $log->open_time ? $log->open_time->format('d/m/Y H:i') : '-' }}</span>
    </div>
    <div class="row">
        <span class="label">Cierre:</span>
        <span class="value">{{ $log->close_time ? $log->close_time->format('d/m/Y H:i') : '-' }}</span>
    </div>

    <hr class="separator">

    {{-- DETALLE DE VENTAS --}}
    <div class="section-title">Detalle de Ventas</div>

    <div class="row">
        <span class="label">Fondo de Caja:</span>
        <span class="value">{{ $cs }}{{ $fmt($cashFloat) }}</span>
    </div>
    <div class="row">
        <span class="label">Efectivo:</span>
        <span class="value">{{ $cs }}{{ $fmt($cashSales) }}</span>
    </div>
    <div class="row">
        <span class="label">POS (Cred/Deb):</span>
        <span class="value">{{ $cs }}{{ $fmt($posSales) }}</span>
    </div>
    <div class="row">
        <span class="label">Mercadopago:</span>
        <span class="value">{{ $cs }}{{ $fmt($mercadopagoSales) }}</span>
    </div>
    <div class="row">
        <span class="label">Transferencias:</span>
        <span class="value">{{ $cs }}{{ $fmt($bankTransferSales) }}</span>
    </div>
    <div class="row">
        <span class="label">Cuenta Corriente:</span>
        <span class="value">{{ $cs }}{{ $fmt($internalCreditSales) }}</span>
    </div>

    <hr class="separator">

    <div class="row">
        <span class="label">Gastos:</span>
        <span class="value">-{{ $cs }}{{ $fmt($totalExpenses) }}</span>
    </div>

    <hr class="separator">

    <div class="total-row">
        <span class="label">TOTAL VENTAS:</span>
        <span class="value">{{ $cs }}{{ $fmt($totalSales) }}</span>
    </div>

    <hr class="separator">

    {{-- ARQUEO DE EFECTIVO --}}
    <div class="section-title">Arqueo de Efectivo</div>

    <div class="row">
        <span class="label">Efectivo Esperado:</span>
        <span class="value">{{ $cs }}{{ $fmt($expectedCash) }}</span>
    </div>

    @if($actualCash !== null)
    <div class="row">
        <span class="label">Efectivo Contado:</span>
        <span class="value">{{ $cs }}{{ $fmt($actualCash) }}</span>
    </div>

    @if($cashDifference != 0)
    <div class="difference-box">
        <strong>{{ $cashDifference > 0 ? 'SOBRANTE' : 'FALTANTE' }}:</strong>
        {{ $cs }}{{ $fmt(abs($cashDifference)) }}
    </div>
    @else
    <div class="difference-box">
        <strong>Sin diferencia</strong>
    </div>
    @endif
    @endif

    <hr class="separator">

    {{-- FOOTER --}}
    <div class="footer">
        @if($closedBy)
            <p>Cerrado por: {{ $closedBy }}</p>
        @endif
        <p>Generado por <strong>chelato.com.uy</strong></p>
    </div>
</div>
<div style="height: 1px; width: 100%;"></div>
</body>
</html>
