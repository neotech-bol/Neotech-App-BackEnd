<?php
namespace App\Exports;

use App\Models\Pedido;
use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
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

class PedidosPorCatalogoExport implements WithMultipleSheets
{
    /**
     * ID del catálogo para filtrar los pedidos.
     *
     * @var int|null
     */
    protected $catalogoId;
    
    /**
     * Nombre del catálogo para el título del archivo.
     *
     * @var string
     */
    protected $catalogoNombre;

    /**
     * Constructor.
     *
     * @param int $catalogoId ID del catálogo para filtrar
     */
    public function __construct(int $catalogoId)
    {
        $this->catalogoId = $catalogoId;
        
        // Obtener el nombre del catálogo
        $catalogo = Catalogo::find($catalogoId);
        $this->catalogoNombre = $catalogo ? $catalogo->nombre : 'Desconocido';
    }

    /**
     * Implementa las hojas que contendrá el archivo Excel.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new PedidosResumenPorCatalogoSheet($this->catalogoId, $this->catalogoNombre),
            new PedidosDetallePorCatalogoSheet($this->catalogoId, $this->catalogoNombre),
        ];

        return $sheets;
    }
}

/**
 * Hoja de resumen de pedidos filtrados por catálogo
 */
class PedidosResumenPorCatalogoSheet implements FromCollection, WithMapping, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
     * ID del catálogo para filtrar los pedidos.
     *
     * @var int|null
     */
    protected $catalogoId;
    
    /**
     * Nombre del catálogo.
     *
     * @var string
     */
    protected $catalogoNombre;

    /**
     * Constructor.
     *
     * @param int $catalogoId ID del catálogo para filtrar
     * @param string $catalogoNombre Nombre del catálogo
     */
    public function __construct(int $catalogoId, string $catalogoNombre)
    {
        $this->catalogoId = $catalogoId;
        $this->catalogoNombre = $catalogoNombre;
    }

    /**
     * Obtiene la colección de pedidos para exportar, filtrados por catálogo.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener pedidos que contengan productos del catálogo especificado
        $pedidosIds = DB::table('pedido_producto')
            ->join('productos', 'pedido_producto.producto_id', '=', 'productos.id')
            ->where('productos.catalogo_id', $this->catalogoId)
            ->select('pedido_producto.pedido_id')
            ->distinct()
            ->pluck('pedido_id');
        
        // Cargar los pedidos con los datos del usuario y cupón
        return Pedido::with(['user', 'cupon', 'productos' => function($query) {
                $query->where('catalogo_id', $this->catalogoId);
            }])
            ->whereIn('id', $pedidosIds)
            ->get();
    }

    /**
     * Mapea cada pedido a un formato específico para la exportación.
     *
     * @param mixed $pedido
     * @return array
     */
    public function map($pedido): array
    {
        // Calcular el total de productos del catálogo específico
        $totalProductos = $pedido->productos->sum('pivot.cantidad');

        // Calcular el total de ventas de productos del catálogo específico
        $totalVentas = $pedido->productos->sum(function ($producto) {
            $pivotData = $producto->pivot;
            $precioUsado = $pivotData->es_preventa ? $pivotData->precio_preventa : $pivotData->precio;
            return $precioUsado * $pivotData->cantidad;
        });

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
            'total_catalogo' => $totalVentas, // Total de ventas de productos del catálogo
            'total_productos' => $totalProductos, // Total de productos del catálogo
            'estado' => $pedido->estado ? 'Completado' : 'Pendiente',
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
            'ID Pedido',
            'Nombre',
            'Apellido',
            'CI',
            'NIT',
            'Teléfono',
            'Género',
            'Total Ventas Catálogo',
            'Total Productos',
            'Estado',
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
        
        // Añadir título del catálogo
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->setCellValue('A1', 'Catálogo: ' . $this->catalogoNombre);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);
        
        // Estilo para el título del catálogo
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2'); // Azul claro
        
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
        
        // Aplicar estilo a encabezados (ahora en la fila 2)
        $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray($headerStyle);
        $sheet->getRowDimension(2)->setRowHeight(30);
        
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
        
        // Aplicar estilo a filas de datos (desde la fila 3)
        if ($highestRow > 2) {
            $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray($dataStyle);
            
            // Estilo para filas alternas (cebra)
            for ($row = 3; $row <= $highestRow; $row++) {
                if ($row % 2 == 1) { // Cambio para que alterne correctamente
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F2F2F2'); // Gris muy claro
                }
            }
            
            // Ajustar altura de filas de datos
            for ($row = 3; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(20);
            }
        }
        
        // Congelar la primera fila
        $sheet->freezePane('A3');
        
        // Aplicar formato condicional para resaltar pedidos pendientes
        $conditionalStyles = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];
        $conditionalStyles[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Pendiente')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFCC99');
        
        $sheet->getStyle('J3:J' . $highestRow)->setConditionalStyles($conditionalStyles);
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
 * Hoja de detalles de productos por pedido filtrados por catálogo
 */
class PedidosDetallePorCatalogoSheet implements FromCollection, WithMapping, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    /**
     * ID del catálogo para filtrar los pedidos.
     *
     * @var int|null
     */
    protected $catalogoId;
    
    /**
     * Nombre del catálogo.
     *
     * @var string
     */
    protected $catalogoNombre;

    /**
     * Constructor.
     *
     * @param int $catalogoId ID del catálogo para filtrar
     * @param string $catalogoNombre Nombre del catálogo
     */
    public function __construct(int $catalogoId, string $catalogoNombre)
    {
        $this->catalogoId = $catalogoId;
        $this->catalogoNombre = $catalogoNombre;
    }

    /**
     * Obtiene la colección de productos de pedidos para exportar, filtrados por catálogo.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener pedidos que contengan productos del catálogo especificado
        $pedidosIds = DB::table('pedido_producto')
            ->join('productos', 'pedido_producto.producto_id', '=', 'productos.id')
            ->where('productos.catalogo_id', $this->catalogoId)
            ->select('pedido_producto.pedido_id')
            ->distinct()
            ->pluck('pedido_id');
        
        // Cargar todos los pedidos con sus productos del catálogo específico
        $pedidos = Pedido::with([
            'productos' => function($query) {
                $query->where('catalogo_id', $this->catalogoId)
                      ->with('modelos', 'categorias');
            }, 
            'user'
        ])->whereIn('id', $pedidosIds)->get();
        
        // Crear una colección plana de todos los productos en todos los pedidos
        $productosEnPedidos = collect();
        
        foreach ($pedidos as $pedido) {
            foreach ($pedido->productos as $producto) {
                $pivotData = $producto->pivot;
                
                // Obtener las categorías del producto
                $categorias = $producto->categorias;
                $categoriasNombres = $categorias->pluck('nombre')->implode(', ');
                
                $productosEnPedidos->push([
                    'pedido_id' => $pedido->id,
                    'cliente' => $pedido->user->nombre . ' ' . $pedido->user->apellido,
                    'fecha_pedido' => $pedido->created_at,
                    'producto' => $producto,
                    'pivot' => $pivotData,
                    'modelo' => $pivotData->modelo_id ? optional($producto->modelos->where('id', $pivotData->modelo_id)->first()) : null,
                    'categorias' => $categoriasNombres,
                    'estado_pedido' => $pedido->estado,
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
            'categorias' => $item['categorias'] ?: 'Sin categoría',
            'producto_nombre' => $producto->nombre,
            'cantidad' => $pivotData->cantidad,
            'modelo' => $modelo ? $modelo->nombre : 'N/A',
            'color' => $pivotData->color ?? 'N/A',
            'tipo_precio' => $tipoPrecio,
            'precio_unitario' => $precioUsado,
            'subtotal' => $precioUsado * $pivotData->cantidad,
            'estado_pedido' => $item['estado_pedido'] ? 'Completado' : 'Pendiente',
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
            'Categorías',
            'Producto',
            'Cantidad',
            'Modelo',
            'Color',
            'Tipo de Precio',
            'Precio Unitario',
            'Subtotal',
            'Estado del Pedido',
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
            'J' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
            'K' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE,
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
        
        // Añadir título del catálogo
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->setCellValue('A1', 'Catálogo: ' . $this->catalogoNombre . ' - Detalle de Productos');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);
        
        // Estilo para el título del catálogo
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2EFDA'); // Verde claro
        
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
        
        // Aplicar estilo a encabezados (ahora en la fila 2)
        $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray($headerStyle);
        $sheet->getRowDimension(2)->setRowHeight(30);
        
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
        
        // Aplicar estilo a filas de datos (desde la fila 3)
        if ($highestRow > 2) {
            $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray($dataStyle);
            
            // Estilo para filas alternas (cebra)
            for ($row = 3; $row <= $highestRow; $row++) {
                if ($row % 2 == 1) { // Cambio para que alterne correctamente
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E2EFDA'); // Verde muy claro
                }
            }
            
            // Ajustar altura de filas de datos
            for ($row = 3; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(20);
            }
        }
        
        // Congelar la primera fila
        $sheet->freezePane('A3');
        
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
        
        $sheet->getStyle('I3:I' . $highestRow)->setConditionalStyles($conditionalStyles1);
        $sheet->getStyle('I3:I' . $highestRow)->setConditionalStyles($conditionalStyles2);
        
        // Aplicar formato condicional para resaltar pedidos pendientes
        $conditionalStyles3 = [
            new \PhpOffice\PhpSpreadsheet\Style\Conditional(),
        ];
        $conditionalStyles3[0]->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
            ->setText('Pendiente')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFCC99');
        
        $sheet->getStyle('L3:L' . $highestRow)->setConditionalStyles($conditionalStyles3);
        
        // Centrar algunas columnas
        $sheet->getStyle('A3:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I3:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L3:L' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    /**
     * Define el título de la hoja de cálculo.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Detalle de Productos';
    }
}