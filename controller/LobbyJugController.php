<?php

class LobbyJugController
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function show()
    {
        session_start(); // Necesario para acceder a $_SESSION


        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'jugador') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $_SESSION['usuario'];

        $usuario['tiene_foto'] = !empty($usuario['foto_perfil']);

        $puntaje = isset($usuario['puntaje']) && $usuario['puntaje'] !== null ? $usuario['puntaje'] : 0;
        $usuario['puntaje_mostrar'] = $puntaje . "pts";





        $botones = [
            ['texto' => 'Ver ranking', 'link' => '#'],
            ['texto' => 'Historial de partidas', 'link' => '#'],
            ['texto' => 'Crear preguntas', 'link' => '#']
        ];



        // Se pasa a mustachol los botones
        $this->view->render('headerGrande', 'lobbyJug', [
            'usuario' => $usuario,
            'botones' => $botones
        ]);
    }

}