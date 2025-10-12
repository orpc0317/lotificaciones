<?php

use App\Controllers\EmpleadoController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Detectar la base (subdirectorio) dinámicamente a partir de SCRIPT_NAME para soportar
// que la aplicación se sirva desde /lotificaciones/public u otro subdirectorio.
$scriptNameDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
if ($scriptNameDir !== '' && $scriptNameDir !== '/' && strpos($uri, $scriptNameDir) === 0) {
    $uri = substr($uri, strlen($scriptNameDir));
    if ($uri === '') {
        $uri = '/';
    }
}

switch ($uri) {
    case '/':
    case '/public':
    case '/lotificaciones/public':
    case '/lotificaciones/public/':
    case '/empleados':
        (new EmpleadoController())->index();
        break;

    case '/empleados/ajax':
        (new EmpleadoController())->ajaxList();
        break;

    case '/empleados/get':
        (new EmpleadoController())->get();
        break;

    case '/empleados/debug':
        (new EmpleadoController())->debug();
        break;

    case '/empleados/create':
        (new EmpleadoController())->create();
        break;

    case '/empleados/update':
        (new EmpleadoController())->update();
        break;

    case '/empleados/delete':
        (new EmpleadoController())->delete();
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}