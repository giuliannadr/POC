<?php
require_once("core/Session.php");
use Dompdf\Dompdf;
require_once("vendor/autoload.php"); // Si usás Composer y Dompdf
class LobbyADMController
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function show()
    {


        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyAdminData();

        $this->view->render('headerAdminEditor', 'lobbyADM', $data);
    }

    public function exportarPDF()
    {

        $imgEdad   = $_POST['imgEdad'] ?? '';
        $imgGenero = $_POST['imgGenero'] ?? '';
        $imgPais   = $_POST['imgPais'] ?? '';

        $html = "
        <h1 style='text-align:center;'>Estadísticas del Sistema</h1>

        <h2>Distribución por Edad</h2>
        <img src='$imgEdad' width='400'>

        <h2>Distribución por Género</h2>
        <img src='$imgGenero' width='400'>

        <h2>Usuarios por País</h2>
        <img src='$imgPais' width='400'>
    ";

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        $pdf->stream('estadisticas.pdf', ['Attachment' => false]);
    }
}