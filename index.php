<?php

require_once("core/Session.php");
Session::init();  // Iniciar la sesión


require_once("Configuration.php");


$configuration = new Configuration();
$router = $configuration->getRouter();
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : null;
$method = isset($_GET['method']) ? $_GET['method'] : null;

$publicControllers = ['home', 'registro', 'login'];

if ($controller === 'registro' && $method === 'verificarEmail') {
    require_once("controller/RegistroController.php");
    require_once("model/JugadorModel.php");
    require_once("view/View.php");

    $db = $configuration->getDatabase();
    $emailHandler = $configuration->getEmail(); // O como se llame tu clase de envío
    $model = new JugadorModel($db, $emailHandler);
    $view = new View();
    $registroController = new RegistroController($model, $view);

    $registroController->verificarEmail();
    exit;
}

/*
// Verifica si el controlador actual requiere sesión
if (!in_array($controller, $publicControllers)) {
if (!Session::exists('usuario') || Session::get('tipo') !== 'jugador') {
    header('Location: index.php?controller=home&method=show');
    exit;
}}*/

$permisos = [
    'home' => ['publico'],
    'login' => ['publico'],
    'registro' => ['publico'],

//jugador
    'ranking' => ['jugador'],
    'historial' => ['jugador'],
    'perfil' => ['jugador'],
    'lobbyjug' => ['jugador'],
    'preguntas' => ['jugador'],
    'crearpregunta' => ['jugador', 'editor'],
//editor
    'gestionarpreguntas' => ['editor'],
    'editor' => ['editor'],
    'lobbyeditor' => ['editor'],
//admin
    'admin' => ['admin'],
    'lobbyadm' => ['admin']
];

if (!in_array($controller, $publicControllers)) {
    if (!Session::exists('usuario')) {
        header('Location: index.php?controller=home&method=show');
        exit;
    }

    $tipoUsuario = Session::get('tipo');

    // Si el tipo del usuario no tiene permiso para acceder al controlador
    if (!isset($permisos[$controller]) || !in_array($tipoUsuario, $permisos[$controller])) {
        header('Location: index.php?controller=home&method=show');
        exit;
    }
}


$router->go($controller, $method);

