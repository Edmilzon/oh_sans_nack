<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Services\CompetidorService;
use App\Model\ArchivoCsv;
use App\Model\Olimpiada;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

            $nombreArchivo = Str::upper(Str::ascii(trim($request->input('nombre_archivo'))));

            $archivoCsv = ArchivoCsv::create([
                'nombre' => $nombreArchivo,
                'fecha' => now()
            ]);

            $resultados = $this->competidorService->procesarImportacion(
                $request->input('competidores'),
                $olimpiada->id_olimpiada,
                $archivoCsv->id_archivo_csv
            );

            $registrados = $resultados['registrados'];
            $duplicados = $resultados['duplicados'];
            $errores = $resultados['errores'];

            $totalRegistrados = count($registrados);
            $totalDuplicados = count($duplicados);
            $totalErrores = count($errores);

            $reporteDuplicados = array_map(function ($item) {
                return [
                    'nombre_completo' => $item['persona']['nombre'] . ' ' . $item['persona']['apellido'],
                    'ci' => $item['persona']['ci'],
                    'motivo' => 'Ya inscrito en ' . ($item['origen_duplicado'] ?? 'otra lista')
                ];
            }, $duplicados);

            $response = [
                'success' => true,
                'message' => 'Proceso de importación finalizado',
                'data' => [
                    'resumen' => [
                        'total_procesados' => $totalRegistrados + $totalDuplicados + $totalErrores,
                        'total_registrados' => $totalRegistrados,
                        'total_duplicados' => $totalDuplicados,
                        'total_errores' => $totalErrores,
                    ],
                    'archivo' => [
                        'id' => $archivoCsv->id_archivo_csv,
                        'nombre' => $archivoCsv->nombre
                    ],
                    'competidores_creados' => array_map(fn($r) => [
                        'nombre_completo' => $r['persona']->nombre . ' ' . $r['persona']->apellido,
                        'ci' => $r['persona']->ci,
                        'estado' => $r['tipo'],
                        'area' => $r['area'],
                        'nivel' => $r['nivel'],
                        'institucion' => $r['institucion']
                    ], $registrados)
                ]
            ];

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
