<!DOCTYPE html>
<html>
<head><title>Reporte</title></head>
<body>
    <h1>Reporte AppCarwash</h1>
    <p>Total Ventas: ${{ number_format($data['kpis']['total_ventas'], 2) }}</p>
    <p>Total Reservas: {{ $data['kpis']['total_reservas'] }}</p>
    <h3>Desglose por Estado</h3>
    <ul>
        @foreach($data['reservas_por_estado'] as $item)
            <li>{{ ucfirst($item->estado) }}: {{ $item->total }}</li>
        @endforeach
    </ul>
</body>
</html>