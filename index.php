<?php

require_once("core/Session.php");
Session::init();  // Iniciar la sesión


require_once("Configuration.php");


$configuration = new Configuration();
$router = $configuration->getRouter();
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'home';
$method = isset($_GET['method']) ? $_GET['method'] : 'show';


$publicControllers = ['home', 'registro', 'login'];


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
        if (!($controller === 'home' && $method === 'show')) {
            header('Location: /POC/');
            exit; }
    }

    $tipoUsuario = Session::get('tipo');

    // Si el tipo del usuario no tiene permiso para acceder al controlador
    if (!isset($permisos[$controller]) || !in_array($tipoUsuario, $permisos[$controller])) {
        if (!($controller === 'home' && $method === 'show')) {
            header('Location: /POC/');
            exit; }
    }
}


$router->go($controller, $method);

