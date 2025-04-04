<?php
namespace App\Exports;

use App\Models\Pedido;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PedidosExport implements WithMultipleSheets
{
    /**
     * Implementa las hojas que contendrá el archivo Excel.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new PedidosResumenSheet(),
            new PedidosDetalleSheet(),
        ];

        return $sheets;
    }
}

/**
 * Hoja de resumen de pedidos
 */
class PedidosResumenSheet implements FromCollection, WithMapping, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
     * Obtiene la colección de pedidos para exportar.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Cargar los pedidos con los datos del usuario y cupón
        return Pedido::with(['user', 'cupon', 'productos'])->get();
    }

    /**
     * Mapea cada pedido a un formato específico para la exportación.
     *
     * @param mixed $pedido
     * @return array
     */
    public function map($pedido): array
    {
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
            'cupon_codigo' => $cuponNombre,
            'cupon_descuento' => $cuponDescripcion,
            'estado' => $pedido->estado ? 'Completado' : 'Pendiente',
            'total_productos' => $totalProductos,
            'metodo_pago' => $pedido->payment_method,
            'created_at' => $pedido->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Define los encabezados de las columnas.
     *
     * @return array
     */
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
            'Monto Total',
            'Total a Pagar',
            'Pendiente',
            'Código de Cupón',
            'Descuento del Cupón',
            'Estado',
            'Total de Productos',
            'Método de Pago',
            'Fecha de Creación',
        ];
    }

    /**
     * Define los formatos de columna para valores numéricos.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'J' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }

    /**
     * Aplica estilos a la hoja de cálculo.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Obtener el número total de filas
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Estilo para encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // Azul corporativo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Aplicar estilo a encabezados
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30); // Altura para la fila de encabezados
        
        // Estilo para filas de datos
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Aplicar estilo a filas de datos
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray($dataStyle);
        
        // Estilo para filas alternas (cebra)
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2'); // Gris muy claro
            }
        }
        
        // Ajustar altura de filas de datos
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
        
        // Congelar la primera fila
        $sheet->freezePane('A2');
        
        // Aplicar formato condicional para resaltar pedidos pendientes
        $conditionalStyles = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];
        $conditionalStyles[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Pendiente')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFCC99');
        
        $sheet->getStyle('M2:M' . $highestRow)->setConditionalStyles($conditionalStyles);
    }

    /**
     * Define el título de la hoja de cálculo.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Resumen de Pedidos';
    }
}

/**
 * Hoja de detalles de productos por pedido
 */
class PedidosDetalleSheet implements FromCollection, WithMapping, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
     * Obtiene la colección de productos de pedidos para exportar.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Cargar todos los pedidos con sus productos
        $pedidos = Pedido::with(['productos.modelos', 'user'])->get();
        
        // Crear una colección plana de todos los productos en todos los pedidos
        $productosEnPedidos = collect();
        
        foreach ($pedidos as $pedido) {
            foreach ($pedido->productos as $producto) {
                $pivotData = $producto->pivot;
                
                $productosEnPedidos->push([
                    'pedido_id' => $pedido->id,
                    'cliente' => $pedido->user->nombre . ' ' . $pedido->user->apellido,
                    'fecha_pedido' => $pedido->created_at,
                    'producto' => $producto,
                    'pivot' => $pivotData,
                    'modelo' => $pivotData->modelo_id ? optional($producto->modelos->where('id', $pivotData->modelo_id)->first()) : null,
                ]);
            }
        }
        
        return $productosEnPedidos;
    }

    /**
     * Mapea cada producto de pedido a un formato específico para la exportación.
     *
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        $producto = $item['producto'];
        $pivotData = $item['pivot'];
        $modelo = $item['modelo'];
        
        // Determinar qué precio se usó
        $esPreventa = $pivotData->es_preventa;
        $precioUsado = $esPreventa ? $pivotData->precio_preventa : $pivotData->precio;
        $tipoPrecio = $esPreventa ? 'Preventa Estándar' : 'Preventa Especial';
        
        return [
            'pedido_id' => $item['pedido_id'],
            'cliente' => $item['cliente'],
            'fecha_pedido' => $item['fecha_pedido']->format('d/m/Y H:i:s'),
            'producto_nombre' => $producto->nombre,
            'cantidad' => $pivotData->cantidad,
            'modelo' => $modelo ? $modelo->nombre : 'N/A',
            'color' => $pivotData->color ?? 'N/A',
            'tipo_precio' => $tipoPrecio,
            'precio_unitario' => $precioUsado,
            'subtotal' => $precioUsado * $pivotData->cantidad,
        ];
    }

    /**
     * Define los encabezados de las columnas.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nº de Pedido',
            'Cliente',
            'Fecha del Pedido',
            'Producto',
            'Cantidad',
            'Modelo',
            'Color',
            'Tipo de Precio',
            'Precio Unitario',
            'Subtotal',
        ];
    }

    /**
     * Define los formatos de columna para valores numéricos.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'J' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
        ];
    }

    /**
     * Aplica estilos a la hoja de cálculo.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet)
    {
        // Obtener el número total de filas
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Estilo para encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '00B050'], // Verde para la hoja de productos
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Aplicar estilo a encabezados
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30); // Altura para la fila de encabezados
        
        // Estilo para filas de datos
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        // Aplicar estilo a filas de datos
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray($dataStyle);
        
        // Estilo para filas alternas (cebra)
        for ($row = 2; $row <= $highestRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E2EFDA'); // Verde muy claro
            }
        }
        
        // Ajustar altura de filas de datos
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
        
        // Congelar la primera fila
        $sheet->freezePane('A2');
        
        // Aplicar formato condicional para resaltar diferentes tipos de precio
        $conditionalStyles1 = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];
        $conditionalStyles1[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Preventa Estándar')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD8E4BC');
        
        $conditionalStyles2 = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];
        $conditionalStyles2[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Preventa Especial')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFBDD7EE');
        
        $sheet->getStyle('H2:H' . $highestRow)->setConditionalStyles($conditionalStyles1);
        $sheet->getStyle('H2:H' . $highestRow)->setConditionalStyles($conditionalStyles2);
        
        // Centrar algunas columnas
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H2:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Define el título de la hoja de cálculo.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Productos por Pedido';
    }
}