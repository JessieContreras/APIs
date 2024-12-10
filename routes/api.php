<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\AdministradorController;
use App\Http\Controllers\Api\AsistenteController;
use App\Http\Controllers\Api\DuenoLocalController;
use App\Http\Controllers\Api\EstadoViaController; 
use App\Http\Controllers\Api\EtiquetaTuristicaController; 
use App\Http\Controllers\Api\HorarioAtencionController;
use App\Http\Controllers\Api\InformacionGeneralController;
use App\Http\Controllers\Api\LocalEtiquetaController;
use App\Http\Controllers\Api\LocalTuristicoController;
use App\Http\Controllers\Api\ParroquiaController;
use App\Http\Controllers\Api\ServicioLocalController;
use App\Http\Controllers\Api\ActividadPuntoTuristicoController;
use App\Http\Controllers\Api\PuntoTuristicoController;
use App\Http\Controllers\Api\PuntoTuristicoEtiquetaController;
use App\Http\Controllers\Api\AnuncioController;
use App\Http\Controllers\Api\ConsultaController;
use App\Http\Controllers\Api\ImagenController;

Route::post('login', [AuthController::class,'login']);
Route::post('registrarAdministrador', [AuthController::class,'registrarAdministrador']);

//para la movil
Route::get('localTuristicoMovil', [LocalTuristicoController::class, 'indexActivos']); 
Route::get('categorias', [EtiquetaTuristicaController::class, 'indexActivos']);
Route::get('/parroquiasMovil', [ParroquiaController::class, 'indexActivos']); 
Route::get('imagenesMovil', [ImagenController::class, 'index']); 



Route::middleware([JWTMiddleware::class])->group(function () {

    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/decode-token', [AuthController::class, 'descomprimirToken']);

    
    Route::get('contarTodo', [ConsultaController::class,'ContarTodo']);
    Route::get('MapaDashboard', [ConsultaController::class,'MapaDashboard']);     
    Route::get('MapaDashboardTODO', [ConsultaController::class,'MapaDashboardTODO']);   

    Route::prefix('administradores')->group(function () {
        Route::get('/', [AdministradorController::class, 'index']); 
        Route::get('/activos', [AdministradorController::class, 'indexActivos']); 
        Route::get('/{id}', [AdministradorController::class, 'show']); 
        Route::get('/activos/{id}', [AdministradorController::class, 'showActivo']); 
        Route::put('/{id}', [AdministradorController::class, 'update']); 
        Route::delete('/{id}', [AdministradorController::class, 'destroy']); 
        Route::post('/activar/{id}', [AdministradorController::class, 'activar']);  
    });

    Route::prefix('asistente')->group(function () {
        Route::get('/', [AsistenteController::class, 'index']); 
        Route::get('/activos', [AsistenteController::class, 'indexActivos']); 
        Route::get('/{id}', [AsistenteController::class, 'show']); 
        Route::get('/activos/{id}', [AsistenteController::class, 'showActivo']); 
        Route::post('/', [AsistenteController::class,'registrarAsistente']);
        Route::put('/{id}', [AsistenteController::class, 'update']); 
        Route::delete('/{id}', [AsistenteController::class, 'destroy']);
        Route::post('/activar/{id}', [AsistenteController::class, 'activar']); 
    });

    Route::prefix('duenoLocal')->group(function () {
        Route::get('/', [DuenoLocalController::class, 'index']); 
        Route::get('/activos', [DuenoLocalController::class, 'indexActivos']); 
        Route::get('/{id}', [DuenoLocalController::class, 'show']); 
        Route::get('/activos/{id}', [DuenoLocalController::class, 'showActivo']); 
        Route::post('/', [DuenoLocalController::class,'store']);
        Route::put('/{id}', [DuenoLocalController::class, 'update']); 
        Route::delete('/{id}', [DuenoLocalController::class, 'destroy']);
        Route::post('/activar/{id}', [DuenoLocalController::class, 'activar']); 
    });

    /*
    Route::prefix('estadoVias')->group(function () {
        Route::get('/', [EstadoViaController::class, 'index']); 
        Route::get('/activos', [EstadoViaController::class, 'indexActivos']); 
        Route::get('/{id}', [EstadoViaController::class, 'show']); 
        Route::get('/activos/{id}', [EstadoViaController::class, 'showActivo']);
        Route::post('/', [EstadoViaController::class, 'store']); 
        Route::put('/{id}', [EstadoViaController::class, 'update']);
        Route::delete('/{id}', [EstadoViaController::class, 'destroy']); 
        Route::post('/activar/{id}', [EstadoViaController::class, 'activar']); 
    });
    */

    Route::prefix('etiquetasTuristicas')->group(function () {
        Route::get('/', [EtiquetaTuristicaController::class, 'index']); 
        Route::get('/{id}', [EtiquetaTuristicaController::class, 'show']);
        Route::get('/activos/{id}', [EtiquetaTuristicaController::class, 'showActivo']); 
        Route::post('/', [EtiquetaTuristicaController::class, 'store']);
        Route::put('/{id}', [EtiquetaTuristicaController::class, 'update']);
        Route::delete('/{id}', [EtiquetaTuristicaController::class, 'destroy']); 
        Route::post('/activar/{id}', [EtiquetaTuristicaController::class, 'activar']); 
    });

    Route::prefix('horariosAtencion')->group(function () {
        Route::get('/', [HorarioAtencionController::class, 'index']); 
        Route::get('/activos', [HorarioAtencionController::class, 'indexActivos']);
        Route::get('/{id}', [HorarioAtencionController::class, 'show']); 
        Route::get('/activos/{id}', [HorarioAtencionController::class, 'showActivo']); 
        Route::post('/', [HorarioAtencionController::class, 'store']); 
        Route::put('/{id}', [HorarioAtencionController::class, 'update']); 
        Route::delete('/{id}', [HorarioAtencionController::class, 'destroy']); 
        Route::post('/activar/{id}', [HorarioAtencionController::class, 'activar']); 
        Route::get('/HorariosPorIDLocal/{id}', [HorarioAtencionController::class, 'HorariosPorIDLocal']); 
        
    });

    /*
    Route::prefix('informacionGeneral')->group(function () {
        Route::get('/', [InformacionGeneralController::class, 'index']);
        Route::get('/activos', [InformacionGeneralController::class, 'indexActivos']); 
        Route::get('/{id}', [InformacionGeneralController::class, 'show']); 
        Route::get('/activos/{id}', [InformacionGeneralController::class, 'showActivo']); 
        Route::post('/', [InformacionGeneralController::class, 'store']); 
        Route::put('/{id}', [InformacionGeneralController::class, 'update']); 
        Route::delete('/{id}', [InformacionGeneralController::class, 'destroy']);
        Route::post('/activar/{id}', [InformacionGeneralController::class, 'activar']); 
    });
    */

    Route::prefix('localEtiqueta')->group(function () {
        Route::get('/', [LocalEtiquetaController::class, 'index']);
        Route::get('/activos', [LocalEtiquetaController::class, 'indexActivos']); 
        Route::get('/{id}', [LocalEtiquetaController::class, 'show']); 
        Route::get('/activos/{id}', [LocalEtiquetaController::class, 'showActivo']); 
        Route::post('/', [LocalEtiquetaController::class, 'store']); 
        Route::put('/{id_local}/{id_etiqueta}', [LocalEtiquetaController::class, 'update']);
        Route::delete('/{id_local}/{id_etiqueta}', [LocalEtiquetaController::class, 'destroy']);
    });


    Route::prefix('localTuristico')->group(function () {
        Route::get('/', [LocalTuristicoController::class, 'index']);
        Route::get('/{id}', [LocalTuristicoController::class, 'show']); 
        Route::get('/activos/{id}', [LocalTuristicoController::class, 'showActivo']); 
        Route::post('/', [LocalTuristicoController::class, 'store']); 
        Route::put('/{id}', [LocalTuristicoController::class, 'update']); 
        Route::delete('/{id}', [LocalTuristicoController::class, 'destroy']); 
        Route::post('/activar/{id}', [LocalTuristicoController::class, 'activar']); 
        Route::get('/mostrarDataLocal/{id}', [LocalTuristicoController::class, 'mostrarDataLocal']);
        Route::get('/buscarPorEtiqueta/{id_etiqueta}', [LocalTuristicoController::class, 'buscarPorEtiqueta']);
    });

    Route::prefix('parroquia')->group(function () {
        Route::get('/', [ParroquiaController::class, 'index']);
        Route::get('/{id}', [ParroquiaController::class, 'show']); 
        Route::get('/activos/{id}', [ParroquiaController::class, 'showActivo']); 
        Route::post('/', [ParroquiaController::class, 'store']); 
        Route::put('/{id}', [ParroquiaController::class, 'update']); 
        Route::delete('/{id}', [ParroquiaController::class, 'destroy']); 
        Route::post('/activar/{id}', [ParroquiaController::class, 'activar']); 
        Route::get('/puntoTuristicoParroquia/{id}', [ParroquiaController::class, 'puntoTuristicoParroquia']); 
    });

    Route::prefix('servicioLocal')->group(function () {
        Route::get('/', [ServicioLocalController::class, 'index']);
        Route::get('/activos', [ServicioLocalController::class, 'indexActivos']); 
        Route::get('/{id}', [ServicioLocalController::class, 'show']); 
        Route::get('/activos/{id}', [ServicioLocalController::class, 'showActivo']); 
        Route::post('/', [ServicioLocalController::class, 'store']); 
        Route::put('/{id}', [ServicioLocalController::class, 'update']); 
        Route::delete('/{id}', [ServicioLocalController::class, 'destroy']); 
        Route::post('/activar/{id}', [ServicioLocalController::class, 'activar']); 
        Route::get('/ServiciosPorIDLocal/{id}', [ServicioLocalController::class, 'ServiciosPorIDLocal']); 
    });

    Route::prefix('actividadPuntoTuristico')->group(function () {
        Route::get('/', [ActividadPuntoTuristicoController::class, 'index']);
        Route::get('/activos', [ActividadPuntoTuristicoController::class, 'indexActivos']); 
        Route::get('/{id}', [ActividadPuntoTuristicoController::class, 'show']); 
        Route::get('/activos/{id}', [ActividadPuntoTuristicoController::class, 'showActivo']); 
        Route::post('/', [ActividadPuntoTuristicoController::class, 'store']); 
        Route::put('/{id}', [ActividadPuntoTuristicoController::class, 'update']); 
        Route::delete('/{id}', [ActividadPuntoTuristicoController::class, 'destroy']); 
        Route::post('/activar/{id}', [ActividadPuntoTuristicoController::class, 'activar']); 
        Route::get('/ActividadPorIDPunto/{id}', [ActividadPuntoTuristicoController::class, 'ActividadPorIDPunto']); 
    });

    Route::prefix('puntoTuristico')->group(function () {
        Route::get('/', [PuntoTuristicoController::class, 'index']);
        Route::get('/activos', [PuntoTuristicoController::class, 'indexActivos']); 
        Route::get('/{id}', [PuntoTuristicoController::class, 'show']); 
        Route::get('/activos/{id}', [PuntoTuristicoController::class, 'showActivo']); 
        Route::post('/', [PuntoTuristicoController::class, 'store']); 
        Route::put('/{id}', [PuntoTuristicoController::class, 'update']); 
        Route::delete('/{id}', [PuntoTuristicoController::class, 'destroy']);
        Route::post('/activar/{id}', [PuntoTuristicoController::class, 'activar']);
        Route::get('/mostrarDataPuntoTuristico/{id}', [PuntoTuristicoController::class, 'mostrarDataPuntoTuristico']); 
        Route::get('/buscarPorEtiqueta/{id_etiqueta}', [PuntoTuristicoController::class, 'buscarPorEtiqueta']);
    });

    Route::prefix('puntoTuristicoEtiqueta')->group(function () {
        Route::get('/', [PuntoTuristicoEtiquetaController::class, 'index']); 
        Route::get('/activos', [PuntoTuristicoEtiquetaController::class, 'indexActivos']); 
        Route::get('/{id_punto_turistico}/{id_etiqueta}', [PuntoTuristicoEtiquetaController::class, 'show']); 
        Route::post('/', [PuntoTuristicoEtiquetaController::class, 'store']); 
        Route::put('/{id_punto_turistico}/{id_etiqueta}', [PuntoTuristicoEtiquetaController::class, 'update']); 
        Route::delete('/{id_punto_turistico}/{id_etiqueta}', [PuntoTuristicoEtiquetaController::class, 'destroy']);     
    });

    Route::prefix('anuncios')->group(function () {
        Route::get('/', [AnuncioController::class, 'index']); 
        Route::get('/{id}', [AnuncioController::class, 'show']); 
        Route::post('/', [AnuncioController::class, 'store']); 
        Route::put('/{id}', [AnuncioController::class, 'update']); 
        Route::delete('/{id}', [AnuncioController::class, 'destroy']); 
        Route::post('/activar/{id}', [AnuncioController::class, 'activar']);  
    });

    Route::prefix('imagenes')->group(function () {
        Route::get('/', [ImagenController::class, 'index']); 
        Route::get('/{id}', [ImagenController::class, 'show']); 
        Route::post('/', [ImagenController::class, 'store']); 
        Route::put('/{id}', [ImagenController::class, 'update']); 
        Route::delete('/{id}', [ImagenController::class, 'destroy']); 
        Route::post('/activar/{id}', [ImagenController::class, 'activar']);  
    });
});
