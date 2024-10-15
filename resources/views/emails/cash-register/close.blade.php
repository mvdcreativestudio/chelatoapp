<!DOCTYPE html>
<html>
<head>
    <title>Cierre de Caja Registradora</title>
</head>
<body>
    <p>La caja registradora ha sido cerrada.</p>
    <p>ID de la Caja Registradora: {{ $cash_register_id }}</p>
    <p>Empleado: {{ $employee_name }}</p>
    <p>Hora de Cierre: {{ $close_time }}</p>
    <p>Ventas en efectivo: ${{ $cash_sales }}</p>
    <p>Ventas POS: ${{ $pos_sales }}</p>
</body>
</html>