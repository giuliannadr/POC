<?php

class LobbyEDITORController
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function show()
    {
        session_start(); // Accede a sesion

        // SESION HARCODEADA ELIMINAR DESPUES
        $_SESSION['usuario'] = 'usuarioPrueba';
        $_SESSION['tipo'] = 'editor';

        // Redirige si no hay usuario o si el tipo no es 'editor'
        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'editor') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $_SESSION['usuario'];

        // Botones en el nav segun el rol
        $botones = [
            ['texto' => 'Gestionar Preguntas', 'link' => '/editor/gestionarPreguntas'],
            ['texto' => 'Preguntas Reportadas', 'link' => '/editor/preguntasReportadas'],
            ['texto' => 'Preguntas Sugeridas', 'link' => '/editor/preguntasSugeridas'],
        ];

        // Se pasa a mustachol los botones
        $this->view->render('headerAdminEditor', 'lobbyEDITOR', [
            'usuario' => $usuario,
            'botones' => $botones
        ]);
    }
}
