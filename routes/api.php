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
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\AreaNivelGradoController;
use App\Http\Controllers\ReporteController;

use App\Http\Controllers\RolAccionController;
use App\Http\Controllers\AccionDisponibilidadController;
use App\Http\Controllers\SistemaEstadoController;
use App\Http\Controllers\UsuarioAccionesController;
use App\Http\Controllers\CronogramaFaseController;
use App\Http\Controllers\FaseGlobalController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CompetidorController;
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
    Route::post('/ci/{ci}/areas', [ResponsableController::class, 'addAreas']);
    Route::get('/ci/{ci}/gestion/{gestion}/areas', [ResponsableController::class, 'getAreasByCiAndGestion']);
    Route::get('/areas/ocupadas/gestion/actual', [ResponsableController::class, 'getOcupadasEnGestionActual']);
    //para clau
    Route::get('/{id_usuario}/areas-con-niveles/olimpiada-actual', [ResponsableController::class, 'areasConNivelesPorOlimpiadaActual']);
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
    Route::post('/ci/{ci}/asignaciones', [EvaluadorController::class, 'addAsignaciones']);
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

//Rutas asociacion area - nivel
Route::get('/area-nivel/show/{id}', [AreaNivelController::class, 'show']);
Route::get('/area-nivel/actuales', [AreaNivelController::class, 'getActuales']);
Route::get('/area-nivel/detalle', [AreaNivelController::class, 'getAllWithDetails']);
Route::get('/area-nivel/por-area/{id_area}', [AreaNivelController::class, 'getByArea']);
Route::get('/area-nivel/{id_olimpiada}', [AreaNivelController::class, 'getAreasConNivelesPorOlimpiada']);
Route::get('/area-nivel/gestion/{gestion}', [AreaNivelController::class, 'getAreasConNivelesPorGestion']);
Route::put('/area-nivel/{id}', [AreaNivelController::class, 'update']);
Route::put('/area-nivel/por-area/{id_area}', [AreaNivelController::class, 'updateByArea']);
Route::get('/area-nivel/{id_area_nivel}/competencias', [CompetenciaController::class, 'getByAreaNivelId']);

// AreaNivelGrado
Route::get('/area-nivel', [AreaNivelGradoController::class, 'index']);
Route::post('/area-nivel', [AreaNivelGradoController::class, 'store']);
Route::get('/area-nivel/sim/simplificado', [AreaNivelGradoController::class, 'getAreasConNivelesSimplificado']);
Route::get('/area-nivel/gestion/{gestion}/area/{id_area}', [AreaNivelGradoController::class, 'getNivelesGradosByAreaAndGestion']);
Route::post('/area-nivel/gestion/{gestion}/areas', [AreaNivelGradoController::class, 'getNivelesGradosByAreasAndGestion']);
Route::post('/area-nivel/por-gestion', [AreaNivelGradoController::class, 'getByGestionAndAreas']);
Route::get('/area-niveles/{id_area}', [AreaNivelGradoController::class, 'getByAreaAll']);
Route::get('/areas-con-niveles', [AreaNivelGradoController::class, 'getAreasConNiveles']);


//Rutas Parametros de clasificación
Route::get('/areas-olimpiada/{id_olimpiada}', [AreaOlimpiadaController::class, 'getAreasByOlimpiada']);
Route::get('/areas-gestion', [AreaOlimpiadaController::class, 'getAreasGestionActual']);
Route::get('/areas-nombres', [AreaOlimpiadaController::class, 'getNombresAreasGestionActual']);
Route::get('/parametros/gestion-actual', [ParametroController::class, 'getParametrosGestionActual']);
Route::get('/parametros/gestiones', [ParametroController::class, 'getAllParametrosByGestiones']);
Route::get('/parametros/an/area-niveles', [ParametroController::class, 'getParametrosByAreaNiveles']);
Route::get('/parametros/{idOlimpiada}', [ParametroController::class, 'getByOlimpiada']);
Route::post('/parametros', [ParametroController::class, 'store']);

//lista de competidores
Route::get('/responsable/{idResponsable}', [ListaResponsableAreaController::class, 'getAreaPorResponsable']);
Route::get('/niveles/{idArea}/area', [ListaResponsableAreaController::class, 'getNivelesPorArea']);
//Route::get('/grado', [ListaResponsableAreaController::class, 'getGrado']);
Route::get('/grados/{idArea}/nivel/{idNivel}', [ListaResponsableAreaController::class, 'getListaGrados']);
Route::get('/departamento', [ListaResponsableAreaController::class, 'getDepartamento']);
Route::get('/generos', [ListaResponsableAreaController::class, 'getGenero']);
Route::get('/listaCompleta/{idResponsable}/{idArea}/{idNivel}/{idGrado}/{genero?}/{departamento?}', [ListaResponsableAreaController::class, 'listarPorAreaYNivel']);
Route::get('/competencias/{id_competencia}/area/{idArea}/nivel/{idNivel}/competidores', [ListaResponsableAreaController::class, 'getCompetidoresPorAreaYNivel']);

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

Route::get('/competencias/por-area-nivel', [CompetenciaController::class, 'getByAreaAndNivel']);
Route::apiResource('competencias', CompetenciaController::class)->only(['index', 'show', 'store']);
Route::apiResource('competencias.examenes', ExamenController::class)->shallow()->only(['index', 'store']);
Route::get('/examenes/por-area-nivel', [ExamenController::class, 'getByAreaAndNivel']);
Route::apiResource('examenes', ExamenController::class)->only(['show']);

Route::post('/examenes/{id_examen_conf}/evaluaciones', [EvaluacionController::class, 'store']);
Route::get('/competencias/{id_competencia}/calificados', [EvaluacionController::class, 'getCalificados']);
Route::get('/competidores/{id_competidor}/evaluacion', [EvaluacionController::class, 'getUltimaPorCompetidor']);
Route::put('/evaluaciones/{id_evaluacion}', [EvaluacionController::class, 'update']);
Route::post('/evaluaciones/{id_evaluacion}/finalizar', [EvaluacionController::class, 'finalizarCalificacion']);
Route::post('/competidores/{id_competidor}/descalificar', [CompetidorController::class, 'descalificar']);
Route::post('/evaluaciones/{id_evaluacion}/descalificar', [EvaluacionController::class, 'descalificar']);

//Rutas para parametrizacion
Route::get('/responsableGestion/{idResponsable}', [MedalleroController::class, 'getAreaPorResponsable']);
Route::get('/medallero/area/{idArea}/niveles', [MedalleroController::class, 'getNivelesPorArea']);
Route::post('/medallero/configuracion', [MedalleroController::class, 'guardarMedallero']);

// nuevos
Route::apiResource('departamentos', DepartamentoController::class);
Route::apiResource('grados-escolaridad', GradoEscolaridadController::class);
Route::apiResource('instituciones', InstitucionController::class);
Route::patch('/sub-fases/{id}/estado', [FaseController::class, 'updateEstado']);

//Sub-fases
Route::get('/sub-fases/area/{id_area}/nivel/{id_nivel}/olimpiada/{id_olimpiada}', [FaseController::class, 'getSubFases']);
Route::get('/areas/actuales', [AreaController::class, 'getActualesPlanas']);
Route::get('/area-nivel/olimpiada/{id_olimpiada}/area/{id_area}', [AreaNivelController::class, 'getNivelesPorAreaOlimpiada']);
Route::patch('/sub-fases/{id_subfase}/estado', [FaseController::class, 'updateEstado']);

// REPORTES Y TRAZABILIDAD
Route::prefix('reportes')->group(function () {
    Route::get('/historial-calificaciones', [ReporteController::class, 'historialCalificaciones']);
    Route::get('/areas', [ReporteController::class, 'getAreas']);
    Route::get('/areas/{idArea}/niveles', [ReporteController::class, 'getNivelesPorArea']);
});

//wilian
Route::prefix('roles/{idRol}/acciones')->group(function () {
    Route::get('/', [RolAccionController::class, 'index']);
    Route::post('/', [RolAccionController::class, 'store']);
    Route::delete('/{idAccion}', [RolAccionController::class, 'destroy']);
});

Route::get(
    'rol/{id_rol}/fase-global/{id_fase_global}/gestion/{id_gestion}',
    [AccionDisponibilidadController::class, 'index']
);

Route::get('/sistema/estado', [SistemaEstadoController::class, 'index']);

Route::get(
    'usuario/{id_usuario}/fase-global/{id_fase_global}/gestion/{id_gestion}/acciones',
    [UsuarioAccionesController::class, 'index']
);

Route::get('cronograma-fases/actuales', [CronogramaFaseController::class, 'listarActuales']);
Route::get('fases-globales/actuales', [FaseGlobalController::class, 'listarActuales']);
Route::apiResource('cronograma-fases', CronogramaFaseController::class);
