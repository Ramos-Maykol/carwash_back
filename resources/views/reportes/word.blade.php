<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Gerencial</title>
</head>
<body>
    <h1 style="font-family: 'Segoe UI', Tahoma, sans-serif;">Reporte Gerencial de Ventas</h1>
    <p style="margin-bottom: 4px;">Fecha: {{ $fecha }}</p>
    <p style="font-size: 14px;">Generado por AppCarwash · Resultados clave del período actual.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 6px; font-weight: bold;">Indicador</th>
                <th style="border: 1px solid #000; padding: 6px; font-weight: bold;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #000; padding: 6px;">Total Ventas</td>
                <td style="border: 1px solid #000; padding: 6px;">${{ number_format($data['kpis']['total_ventas'], 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px;">Reservas Totales</td>
                <td style="border: 1px solid #000; padding: 6px;">{{ $data['kpis']['total_reservas'] }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px;">Reservas Pendientes</td>
                <td style="border: 1px solid #000; padding: 6px;">{{ $data['kpis']['reservas_pendientes'] }}</td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin-top: 20px;">Reservas por estado</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 6px;">Estado</th>
                <th style="border: 1px solid #000; padding: 6px;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['reservas_por_estado'] as $item)
                <tr>
                    <td style="border: 1px solid #000; padding: 6px;">{{ ucfirst($item->estado) }}</td>
                    <td style="border: 1px solid #000; padding: 6px;">{{ $item->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
