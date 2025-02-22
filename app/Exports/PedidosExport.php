<?php
namespace App\Exports;

use App\Models\Pedido;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PedidosExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        // Cargar los pedidos con los datos del usuario y productos
        return Pedido::with('user', 'productos', 'cupon')->get()->map(function ($pedido) {
            // Calcular el total de productos
            $totalProductos = $pedido->productos->sum('pivot.cantidad');

            // Obtener datos del cupón
            $cuponNombre = $pedido->cupon ? $pedido->cupon->codigo : 'Sin cupón';
            $cuponDescripcion = $pedido->cupon ? $pedido->cupon->descuento : 'Sin descuento';

            return [
                'id' => $pedido->id,
                'nombre' => $pedido->user->nombre,
                'apellido' => $pedido->user->apellido,
                'ci' => $pedido->user->ci,
                'nit' => $pedido->user->nit,
                'telefono' => $pedido->user->telefono,
                'genero' => $pedido->user->genero,
                'total_amount' => $pedido->total_amount,
                'total_to_pay' => $pedido->total_to_pay,
                'pending' => $pedido->pending,
                'cupon_codigo' => $cuponNombre, // Nombre del cupón
                'cupon_descuento' => $cuponDescripcion, // Descripción del cupón
                'estado' => $pedido->estado,
                'total_productos' => $totalProductos, // Agregar total de productos
                'created_at' => $pedido->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Apellido',
            'CI',
            'NIT',
            'Teléfono',
            'Género',
            'Total Amount',
            'Total a Pagar',
            'Pendiente',
            'Nombre del Cupón', // Encabezado para el nombre del cupón
            'Descripción del Cupón', // Encabezado para la descripción del cupón
            'Estado',
            'Total de Productos', // Encabezado para total de productos
            'Fecha de Creación',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos al encabezado
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFont()->setSize(12);
        $sheet->getStyle('A1:O1')->getAlignment()->setHorizontal('center');

        // Aplicar un color de fondo al encabezado
        $sheet->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:O1')->getFill()->getStartColor()->setARGB('FFCCCCCC'); // Color gris claro
    }

    public function title(): string
    {
        return 'Pedidos'; // Título de la hoja
    }
}