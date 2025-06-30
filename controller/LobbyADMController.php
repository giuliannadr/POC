<?php
require_once("core/Session.php");

use Dompdf\Dompdf;

require_once("vendor/autoload.php"); // Si usás Composer y Dompdf
class LobbyADMController
{
    private $view;
    private $db;

    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function show()
    {
        $usuario = Session::get('usuario');
        $periodo = $_GET['periodo'] ?? 'mes';

        $botones = [
            ['texto' => 'Dia', 'link' => '/POC/admin/dashboard?periodo=dia'],
            ['texto' => 'Semana', 'link' => '/POC/admin/dashboard?periodo=semana'],
            ['texto' => 'Mes', 'link' => '/POC/admin/dashboard?periodo=mes']
        ];

        $adminModel = new AdminModel($this->db);

        $datos = $adminModel->obtenerEstadisticas($periodo);
        $estadisticasPorEdad = $adminModel->obtenerEstadisticasPorEdad();
        $usuariosPorPais = $adminModel->obtenerEstadisticasPorPais();

        $totalEdad = $estadisticasPorEdad['menores'] + $estadisticasPorEdad['adultos'] + $estadisticasPorEdad['jubilados'];
        $porcentajesEdad = [
            'porcentajeMenores' => $totalEdad > 0 ? round(($estadisticasPorEdad['menores'] / $totalEdad) * 100, 1) : 0,
            'porcentajeAdultos' => $totalEdad > 0 ? round(($estadisticasPorEdad['adultos'] / $totalEdad) * 100, 1) : 0,
            'porcentajeJubilados' => $totalEdad > 0 ? round(($estadisticasPorEdad['jubilados'] / $totalEdad) * 100, 1) : 0
        ];

        $datosGenero = $adminModel->obtenerEstadisticasPorGenero();
        $totalGenero = $datosGenero['hombres'] + $datosGenero['mujeres'] + $datosGenero['otros'];
        $porcentajeHombres = $totalGenero > 0 ? round(($datosGenero['hombres'] / $totalGenero) * 100, 1) : 0;
        $porcentajeMujeres = $totalGenero > 0 ? round(($datosGenero['mujeres'] / $totalGenero) * 100, 1) : 0;
        $porcentajeOtros = $totalGenero > 0 ? round(($datosGenero['otros'] / $totalGenero) * 100, 1) : 0;

        $data = [
            'usuario' => $usuario,
            'botones' => $botones,
            'totalJugadores' => $datos['totalJugadores'],
            'totalPartidas' => $datos['totalPartidas'],
            'menores' => $estadisticasPorEdad['menores'],
            'adultos' => $estadisticasPorEdad['adultos'],
            'jubilados' => $estadisticasPorEdad['jubilados'],
            'usuariosPorPais' => $usuariosPorPais,
            'porcentajeMenores' => $porcentajesEdad['porcentajeMenores'],
            'porcentajeAdultos' => $porcentajesEdad['porcentajeAdultos'],
            'porcentajeJubilados' => $porcentajesEdad['porcentajeJubilados'],
            'hombres' => $datosGenero['hombres'],
            'mujeres' => $datosGenero['mujeres'],
            'otros' => $datosGenero['otros'],
            'porcentajeHombres' => $porcentajeHombres,
            'porcentajeMujeres' => $porcentajeMujeres,
            'porcentajeOtros' => $porcentajeOtros
        ];

        $this->view->render('headerAdminEditor', 'lobbyADM', $data);
    }


    public function exportarPDF()
    {

        $imgEdad = $_POST['imgEdad'] ?? '';
        $imgGenero = $_POST['imgGenero'] ?? '';
        $imgPais = $_POST['imgPais'] ?? '';

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