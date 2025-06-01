<?php

class LobbyJugController
{
    private $view;
    private $session;

    public function __construct($view,$session)
    {
        $this->view = $view;
        $this->session = $session;
    }

    public function show()
    {
        $this->session->verificarSesion(); // Necesario para acceder a $_SESSION

        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'jugador') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $this->session->obtenerUsuario();

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