<?php

class LobbyADMController
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

            $this->view->render('headerChico','homeLogin');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $this->view->render('headerChico','lobbyADMView', ['usuario' => $usuario]);

    }

}