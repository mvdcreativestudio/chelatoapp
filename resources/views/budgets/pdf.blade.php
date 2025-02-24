<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Presupuesto #{{ $budget->id }}</title>
    <style>
        body {
            font-family: 'Lato', sans-serif;
            background-color: #F5F5F5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 650px;
            margin: 0 auto;
            background-color: #FFFFFF;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        p {
            font-size: 12px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .title {
            text-align: center;
            color: #333;
        }
        h2 {
            color: #333;
        }
        h2 .status {
            font-size: 16px;
            color: #666;
            font-weight: normal;
            display: block;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #D6E7F0;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #D6E7F0;
            color: #333;
        }
        .client-info {
            background-color: #F9F9F9;
            padding: 10px;
            border: 1px solid #EEE;
            margin-bottom: 20px;
        }
        .client-info p {
            margin: 5px 0;
        }
        .totals {
            text-align: right;
            padding-top: 20px;
        }
        .totals p {
            margin: 5px 0;
        }
        .totals p span {
            font-weight: bold;
        }
        .notes {
            background-color: #F9F9F9;
            padding: 10px;
            border: 1px solid #EEE;
            margin-top: 20px;
        }
        .notes h4 {
            color: #333;
            margin-top: 0;
        }
        .notes p {
            margin: 5px 0;
        }
        .company-info {
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.5;
            font-size: 12px;
        }
        
        /* Status styles */
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .status.draft {
            background-color: #fff7e6;
            color: #996b00;
        }
        
        .status.pending_approval {
            background-color: #e6f3ff;
            color: #004d99;
        }
        
        .status.sent {
            background-color: #e6f7ff;
            color: #0050B3;
        }
        
        .status.negotiation {
            background-color: #e6f3ff;
            color: #004d99;
        }
        
        .status.approved {
            background-color: #e6ffe6;
            color: #006600;
        }
        
        .status.rejected {
            background-color: #ffe6e6;
            color: #990000;
        }
        
        .status.expired {
            background-color: #f2f2f2;
            color: #666666;
        }
        
        .status.cancelled {
            background-color: #ffe6e6;
            color: #990000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($companySettings && $companySettings->logo_black)
                <img src="{{ $companySettings->logo_black }}" alt="{{ $companySettings->name }}">
            @endif
            <h2>Presupuesto #{{ $budget->id }} 
                <span class="status {{ $budget->status()->latest()->first()->status }}">
                    @php
                        $statusTranslations = [
                            'draft' => 'Borrador',
                            'pending_approval' => 'Pendiente de Aprobación',
                            'sent' => 'Enviado',
                            'negotiation' => 'En Negociación',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazado',
                            'expired' => 'Vencido',
                            'cancelled' => 'Cancelado'
                        ];
                    @endphp
                    {{ $statusTranslations[$budget->status()->latest()->first()->status] ?? $budget->status()->latest()->first()->status }}
                </span>
            </h2>
            <div class="company-info">
                <strong>{{ $budget->store->name }}</strong><br>
                {{ $budget->store->address }}<br>
                {{ $budget->store->phone }}<br>
                {{ $budget->store->email }}
            </div>
        </div>

        <div class="client-info">
            <h3>Información del Cliente</h3>
            @if($budget->client)
                <p>
                    <strong>Cliente:</strong> {{ $budget->client->type === 'company' ? $budget->client->company_name : $budget->client->name . ' ' . $budget->client->lastname }}<br>
                    <strong>{{ $budget->client->type === 'company' ? 'RUT' : 'CI' }}:</strong> {{ $budget->client->type === 'company' ? $budget->client->rut : $budget->client->ci }}<br>
                    <strong>Email:</strong> {{ $budget->client->email }}<br>
                    <strong>Teléfono:</strong> {{ $budget->client->phone }}<br>
                    <strong>Dirección:</strong> {{ $budget->client->address }}
                </p>
            @elseif($budget->lead)
                <p>
                    <strong>Lead:</strong> {{ $budget->lead->name }}<br>
                    <strong>Email:</strong> {{ $budget->lead->email }}<br>
                    <strong>Teléfono:</strong> {{ $budget->lead->phone }}
                </p>
            @endif
            <p>
                <strong>Fecha de vencimiento:</strong> {{ date('d/m/Y', strtotime($budget->due_date)) }}
            </p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Descuento</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budget->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>
                            @if($item->discount_type === 'Percentage')
                                {{ $item->discount_price }}%
                            @elseif($item->discount_type === 'Fixed')
                                ${{ number_format($item->discount_price, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <p><strong>Subtotal:</strong> ${{ number_format($budget->subtotal, 2) }}</p>
            @if($budget->discount)
                <p>
                    <strong>Descuento:</strong>
                    @if($budget->discount_type === 'Percentage')
                        {{ $budget->discount }}%
                    @else
                        ${{ number_format($budget->discount, 2) }}
                    @endif
                </p>
            @endif
            <h3>Total: ${{ number_format($budget->total, 2) }}</h3>
        </div>

        @if($budget->notes)
            <div class="notes">
                <h4>Notas:</h4>
                <p>{{ $budget->notes }}</p>
            </div>
        @endif
    </div>
</body>
</html>