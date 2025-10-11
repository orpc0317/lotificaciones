<?php

use App\Controllers\EmpleadoController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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