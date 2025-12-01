<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\OlimpiadaController;
// use App\Http\Controllers\EvaluadorController;
use App\Http\Controllers\NivelController;
// use App\Http\Controllers\ProductController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaOlimpiadaController;
use App\Http\Controllers\EvaluadorController;
// use App\Http\Controllers\ResponsableController;
// use App\Http\Controllers\Responsable\CompetidorController as ResponsableCompetidorController;
use App\Http\Controllers\ImportarcsvController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\AreaNivelController;
use App\Http\Controllers\ListaResponsableAreaController;
use App\Http\Controllers\GradoEscolaridadController;
use App\Http\Controllers\FaseController;
use App\Http\Controllers\MedalleroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepartamentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    return response()->json([
        'message' => '¡OhSansi Backend API funcionando correctamente!',
        'status' => 'success',
        'timestamp' => now()
    ]);
});

Route::get('/', function () {
    return response()->json(['message' => 'API funcionando correctamente']);
});

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [UsuarioController::class, 'login']);
});

Route::get('/usuarios/ci/{ci}', [UsuarioController::class, 'showByCi']);

// Rutas para responsables de área
Route::prefix('responsables')->group(function () {
    Route::post('/', [ResponsableController::class, 'store']);
    Route::get('/', [ResponsableController::class, 'index']);
    Route::get('/{id}', [ResponsableController::class, 'show']);
    Route::get('/ci/{ci}/gestiones', [ResponsableController::class, 'getGestionesByCi']);
    Route::put('/ci/{ci}', [ResponsableController::class, 'updateByCi']);
    Route::post('/ci/{ci}/areas', [ResponsableController::class, 'addAreasByCi']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [ResponsableController::class, 'getAreasByCiAndGestion']);
    Route::get('/areas/ocupadas/gestion/actual', [ResponsableController::class, 'getOcupadasEnGestionActual']);
});

// Rutas para evaluadores
Route::prefix('evaluadores')->group(function () {
    Route::post('/', [EvaluadorController::class, 'store']);
    Route::get('/', [EvaluadorController::class, 'index']);
    Route::get('/{id}', [EvaluadorController::class, 'show']);
    Route::put('/ci/{ci}', [EvaluadorController::class, 'updateByCi']);
    Route::get('/{id}/areas-niveles', [EvaluadorController::class, 'getAreasNivelesById']);
    Route::post('/ci/{ci}/areas', [EvaluadorController::class, 'addAreasByCi']);
    Route::get('/ci/{ci}/gestiones', [EvaluadorController::class, 'getGestionesByCi']);
    Route::post('/ci/{ci}/asignaciones', [EvaluadorController::class, 'addAsignacionesByCi']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [EvaluadorController::class, 'getAreasByCiAndGestion']);
});

Route::get('olimpiadas/{identifier}/areas', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);

//Rutas Olimpiada
Route::get('/olimpiadas/anteriores', [OlimpiadaController::class, 'olimpiadasAnteriores']);
Route::get('/olimpiadas/actual', [OlimpiadaController::class, 'olimpiadaActual']);
Route::get('/gestiones', [OlimpiadaController::class, 'gestiones']);


//Rutas para la gestión de niveles
Route::apiResource('niveles', NivelController::class)->only(['index', 'store']);

//area mostrar y insertar
Route::get('/area', [AreaController::class, 'index']);
Route::post('/area', [AreaController::class, 'store']);
Route::get('/area/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/area/gestion/{gestion}', [AreaOlimpiadaController::class, 'getAreasByGestion']);

//Niveles
Route::get('/niveles', [NivelController::class, 'index']);
Route::get('/niveles/{id_nivel}', [NivelController::class, 'show']);

// Grados de escolaridad
Route::get('/grados-escolaridad', [GradoEscolaridadController::class, 'index']);
Route::get('/grados-escolaridad/{id_grado_escolaridad}', [GradoEscolaridadController::class, 'show']);

//Importar csv
Route::post('importar/{gestion}',[ImportarcsvController::class,'importar']);

// Rutas comentadas temporalmente hasta que se creen los controladores
/*
// Rutas para la gestión de productos
Route::apiResource('products', ProductController::class)->only(['index', 'store']);


// Rutas para la gestión de evaluadores
Route::prefix('v1')->group(function () {
    Route::apiResource('evaluadores', EvaluadorController::class)->only(['store']);
});

//area mostrar y insertar
Route::get('/areas/{gestion}', [AreaController::class, 'getAreasPorGestion']);

//responsable de area mostrar y insertar
Route::get('/responsableArea', [ResponsableController::class, 'index']);
Route::post('/responsableArea', [ResponsableController::class, 'store']);
Route::get('/usuarios/roles/{ci}', [ResponsableController::class, 'showRolesByCi']);

// Competidores por Responsable de Área
Route::get('/responsables/{id_persona}/competidores', [ResponsableCompetidorController::class, 'index']);


//Rutas asociacion area - nivel
/*Route::apiResource('nivel',NivelController::class)->only(['index']);*/
Route::post('area-niveles', [AreaNivelController::class, 'store']);
Route::post('/area-nivel/por-gestion', [AreaNivelController::class, 'getByGestionAndAreas']);
Route::post('/area-nivel/gestion/{gestion}/areas', [AreaNivelController::class, 'getNivelesGradosByAreasAndGestion']);
Route::get('/area-nivel/detalle', [AreaNivelController::class, 'getAllWithDetails']);
Route::get('/area-nivel/actuales', [AreaNivelController::class, 'getActuales']);
Route::get('area-niveles/{id_area}', [AreaNivelController::class, 'getByAreaAll']);
Route::get('/areas-con-niveles', [AreaNivelController::class, 'getAreasConNiveles']);
Route::get('/area-nivel', [AreaNivelController::class, 'getAreasConNivelesSimplificado']);
Route::get('/area-nivel/gestion/{gestion}/area/{id_area}', [AreaNivelController::class, 'getNivelesGradosByAreaAndGestion']);
Route::get('/area-nivel/{id_olimpiada}', [AreaNivelController::class, 'getAreasConNivelesPorOlimpiada']);
Route::get('/area-nivel/gestion/{gestion}', [AreaNivelController::class, 'getAreasConNivelesPorGestion']);


//Rutas Parametros de clasificación
Route::get('/areas-olimpiada/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/areas-gestion', [AreaOlimpiadaController::class, 'getAreasGestionActual']);
Route::get('/areas-nombres', [AreaOlimpiadaController::class, 'getNombresAreasGestionActual']);
Route::get('/parametros/gestion-actual', [ParametroController::class, 'getParametrosGestionActual']);
Route::get('/parametros/gestiones', [ParametroController::class, 'getAllParametrosByGestiones']);
Route::get('/parametros/area-niveles', [ParametroController::class, 'getParametrosByAreaNiveles']);
Route::get('/parametros/{idOlimpiada}', [ParametroController::class, 'getByOlimpiada']);
Route::post('/parametros', [ParametroController::class, 'store']);

//lista de competidores
Route::get('/responsable/{idResponsable}', [ListaResponsableAreaController::class, 'getAreaPorResponsable']);
Route::get('/niveles/{idArea}/area', [ListaResponsableAreaController::class, 'getNivelesPorArea']);
//Route::get('/grado', [ListaResponsableAreaController::class, 'getGrado']);
Route::get('/grados/nivel/{idNivel}', [App\Http\Controllers\ListaResponsableAreaController::class, 'getListaGrados']);
Route::get('/departamento', [ListaResponsableAreaController::class, 'getDepartamento']);
Route::get('/generos', [ListaResponsableAreaController::class, 'getGenero']);
Route::get('/listaCompleta/{idResponsable}/{idArea}/{idNivel}/{idGrado}/{genero?}/{departamento?}', [ListaResponsableAreaController::class, 'listarPorAreaYNivel']);
Route::get('/competidores/area/{idArea}/nivel/{idNivel}', [ListaResponsableAreaController::class, 'getCompetidoresPorAreaYNivel']);

//Rutas para la calificación
Route::get('/fases-globales', [FaseController::class, 'indexGlobales']);
Route::get('/acciones-sistema', [FaseController::class, 'listarAccionesSistema']);
Route::get('/gestiones/{idGestion}/configuracion-acciones', [FaseController::class, 'getConfiguracionAccionesPorGestion']);
Route::put('/gestiones/{idGestion}/configuracion-acciones', [FaseController::class, 'guardarConfiguracionAccionesPorGestion']);
Route::patch('/gestiones/{idGestion}/fases/{idFase}/acciones/{idAccion}', [FaseController::class, 'actualizarAccionEnFase']);
Route::get('/gestiones/{idGestion}/fases/{idFase}/acciones-habilitadas', [FaseController::class, 'getAccionesHabilitadas']);
Route::get('/fases/{id}/details', [FaseController::class, 'getFaseDetails']);
Route::get('/sub-fases/area/{id_area}/nivel/{id_nivel}/olimpiada/{id_olimpiada}', [FaseController::class, 'getSubFases']);
Route::apiResource('area-niveles.fases', FaseController::class)->shallow();
Route::post('/competencias/{id_competencia}/evaluacion', [EvaluacionController::class, 'store']);
Route::get('/competencias/{id_competencia}/calificados', [EvaluacionController::class, 'getCalificados']);
Route::get('/competidores/{id_competidor}/evaluacion', [EvaluacionController::class, 'getUltimaPorCompetidor']);
Route::put('/evaluaciones/{id_evaluacion}', [EvaluacionController::class, 'update']);
Route::post('/evaluaciones/{id_evaluacion}/finalizar', [EvaluacionController::class, 'finalizarCalificacion']);

//Rutas para parametrizacion
Route::get('/responsableGestion/{idResponsable}', [MedalleroController::class, 'getAreaPorResponsable']);
Route::get('/medallero/area/{idArea}/niveles', [MedalleroController::class, 'getNivelesPorArea']);
Route::post('/medallero/configuracion', [MedalleroController::class, 'guardarMedallero']);

// nuevos
Route::apiResource('departamentos', DepartamentoController::class);
Route::apiResource('grados-escolaridad', GradoEscolaridadController::class);
