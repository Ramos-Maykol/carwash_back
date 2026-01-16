<?php

namespace App\Exports;

use App\Models\Reserva;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Reserva::with([
                'cliente.usuario',
                'cupoHorario.sucursal',
                'precioServicio.servicio'
            ])
            ->get()
            ->map(function ($reserva) {
                $clienteNombre = trim((string) ($reserva->cliente?->nombre ?? '') . ' ' . (string) ($reserva->cliente?->apellido ?? ''));
                $clienteUsuario = (string) ($reserva->cliente?->usuario?->name ?? '');
                $cliente = $clienteNombre !== '' ? $clienteNombre : ($clienteUsuario !== '' ? $clienteUsuario : '');

                $sucursal = (string) ($reserva->cupoHorario?->sucursal?->nombre ?? '');

                $servicio = (string) ($reserva->precioServicio?->servicio?->nombre ?? '');

                return [
                    'ID' => $reserva->id,
                    'Cliente' => $cliente,
                    'Sucursal' => $sucursal,
                    'Servicio' => $servicio,
                    'Fecha' => optional($reserva->created_at)->toDateTimeString(),
                    'Estado' => $reserva->estado,
                    'Total' => $reserva->precio_final,
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Cliente', 'Sucursal', 'Servicio', 'Fecha', 'Estado', 'Monto ($)'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}