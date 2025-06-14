<?php

require_once("core/Session.php");
Session::init();  // Iniciar la sesión


require_once("Configuration.php");


$configuration = new Configuration();
$router = $configuration->getRouter();
$controller = isset($_GET['controller']) ? $_GET['controller'] : null;
$method = isset($_GET['method']) ? $_GET['method'] : null;

$publicControllers = ['home', 'registro', 'login'];

// Verifica si el controlador actual requiere sesión
if (!in_array($controller, $publicControllers)) {
if (!Session::exists('usuario') || Session::get('tipo') !== 'jugador') {
    header('Location: index.php?controller=home&method=show');
    exit;
}}

$router->go($controller, $method);

