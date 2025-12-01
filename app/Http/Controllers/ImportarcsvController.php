<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Services\CompetidorService;
use App\Model\ArchivoCsv;
use App\Model\Olimpiada;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportarcsvController extends Controller
{
    protected $competidorService;

    public function __construct(CompetidorService $competidorService)
    {
        $this->competidorService = $competidorService;
    }

    public function importar(ImportarRequest $request, string $gestion): JsonResponse
    {
        try {
            $olimpiada = Olimpiada::where('gestion', $gestion)->first();
            if (!$olimpiada) {
                return response()->json([
                    'success' => false,
                    'message' => "La olimpiada de gestión $gestion no existe."
                ], 404);
            }

            $nombreArchivo = $request->input('nombre_archivo');

            // Creamos registro de archivo
            $archivoCsv = ArchivoCsv::create([
                'nombre' => $nombreArchivo,
                'fecha' => now()
            ]);

            // Llamada al servicio optimizado
            $resultados = $this->competidorService->procesarImportacion(
                $request->input('competidores'),
                $olimpiada->id_olimpiada,
                $archivoCsv->id_archivo_csv
            );

            $registrados = $resultados['registrados'];
            $duplicados = $resultados['duplicados'];
            $errores = $resultados['errores'];

            // --- CONSTRUCCIÓN DE REPORTE (Compatibilidad Frontend) ---

            // Agrupar duplicados por el archivo donde se encontraron
            $reporteDuplicados = collect($duplicados)->groupBy('origen_duplicado')->map(function ($items, $archivoOrigen) {
                return [
                    'archivo_origen' => $archivoOrigen,
                    'cantidad' => count($items),
                    // Mostramos una muestra de CIs para feedback
                    'ejemplos' => $items->take(5)->map(fn($i) => $i['persona']['ci'])->values()
                ];
            })->values();

            $totalRegistrados = count($registrados);
            $totalDuplicados = count($duplicados);
            $totalErrores = count($errores);

            $mensaje = "Importación finalizada. $totalRegistrados procesados exitosamente.";
            if ($totalDuplicados > 0) $mensaje .= " $totalDuplicados duplicados ignorados.";
            if ($totalErrores > 0) $mensaje .= " $totalErrores errores de validación.";

            $response = [
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'resumen' => [
                        'total_procesados' => $totalRegistrados,
                        'total_duplicados' => $totalDuplicados,
                        'total_errores' => $totalErrores,
                    ],
                    'archivo' => [
                        'id' => $archivoCsv->id_archivo_csv,
                        'nombre' => $archivoCsv->nombre
                    ],
                    // Mapeo para tabla visual en frontend
                    'competidores_creados' => array_map(fn($r) => [
                        'nombre_completo' => $r['persona']->nombre . ' ' . $r['persona']->apellido,
                        'ci' => $r['persona']->ci,
                        'estado' => $r['tipo'], // REGISTRO o ASIGNACION
                        'area' => $r['area'],
                        'nivel' => $r['nivel'],
                        'institucion' => $r['institucion']
                    ], $registrados)
                ]
            ];

            // Adjuntar detalles si existen problemas
            if ($totalDuplicados > 0) {
                $response['detalles_duplicados'] = $reporteDuplicados;
            }
            if ($totalErrores > 0) {
                $response['detalles_errores'] = $errores;
            }

            return response()->json($response, 201);

        } catch (Throwable $e) {
            Log::error("Error Importación CSV: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error crítico al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
