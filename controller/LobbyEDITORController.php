<?php

class LobbyEDITORController
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
        $this->session->verificarSesion(); // Accede a sesion

        // Redirige si no hay usuario o si el tipo no es 'editor'
        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'editor') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $this->session->obtenerUsuario();

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
