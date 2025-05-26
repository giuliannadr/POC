<?php

class LobbyUsuarioController
{

    private $view;
    public function __construct($view){
        $this->view = $view;
    }

    public function show(){

        session_start();
        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'jugador') {

            $this->view->render('headerGrande','homeLogin');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $this->view->render('headerGrande','lobbyUsuario', ['usuario' => $usuario]);
    }

}