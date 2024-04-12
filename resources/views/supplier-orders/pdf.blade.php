<!DOCTYPE html>
<html>
<head>
    <title>Orden de Compra</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .information, .materials {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orden de Compra #{{ $order->id }}</h1>
    </div>
    <div class="information">
        <table>
            <thead>
                <tr>
                    <th colspan="2">Información de la Orden</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Proveedor:</strong></td>
                    <td>{{ $order->supplier->name }}</td>
                </tr>
                <tr>
                    <td><strong>Fecha de Orden:</strong></td>
                    <td>{{ $order->order_date }}</td>
                </tr>
                <tr>
                    <td><strong>Estado de Envío:</strong></td>
                    <td>{{ ucfirst($order->shipping_status) }}</td>
                </tr>
                <tr>
                    <td><strong>Tienda:</strong></td>
                    <td>{{ $order->store->name }}</td>
                </tr>
                <tr>
                    <td><strong>Notas:</strong></td>
                    <td>{{ $order->notes ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="materials">
        <h2>Materias Primas Solicitadas</h2>
        <table>
            <thead>
                <tr>
                    <th>Materia Prima</th>
                    <th>Cantidad</th>
                    <th>Unidad de Medida</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->rawMaterials as $material)
                    <tr>
                        <td>{{ $material->name }}</td>
                        <td>{{ $material->pivot->quantity }}</td>
                        <td>{{ $material->unit_of_measure }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
