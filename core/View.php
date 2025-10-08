<?php
class View {
    public static function render($viewName, $data = []) {
        extract($data);
        include "../app/views/{$viewName}.php";
    }
}