<?php
class Router {
    public function route() {
        $page = $_GET['page'] ?? 'empleados';
        $controllerFile = "../app/controllers/{$page}Controller.php";
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $className = ucfirst($page) . 'Controller';
            $controller = new $className();
            $controller->index();
        } else {
            echo "Página no encontrada.";
        }
    }
}