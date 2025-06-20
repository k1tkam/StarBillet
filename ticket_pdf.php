<?php
session_start();
require_once 'database.php';
require_once 'auth_functions.php';
require_once 'lib/fpdf.php'; // Ajusta la ruta si es necesario

if (!isset($_SESSION['user_id'])) {
    die('Acceso denegado');
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$ticket_code = isset($_GET['ticket_code']) ? $_GET['ticket_code'] : '';

if ($event_id <= 0 || empty($ticket_code)) {
    die('Datos inválidos');
}

// Busca el ticket del usuario para ese evento y código
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT t.*, e.name AS event_name, e.date AS event_date, e.time AS event_time, e.venue, e.city, u.name AS user_name
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ? AND t.event_id = ? AND t.ticket_code = ?");
$stmt->execute([$user_id, $event_id, $ticket_code]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die('Ticket no encontrado');
}

// Generar PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo centrado
$pdf->Image('img/logo.png', ($pdf->GetPageWidth()-30)/2, 10, 30);
$pdf->Ln(35);

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'StarBillet - Ticket de Evento',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);

// Datos del ticket con mejor espaciado
$pdf->Cell(50,10,'Nombre:',0,0);
$pdf->Cell(0,10,utf8_decode($ticket['user_name']),0,1);

$pdf->Cell(50,10,'Evento:',0,0);
$pdf->Cell(0,10,utf8_decode($ticket['event_name']),0,1);

$pdf->Cell(50,10,'Fecha del evento:',0,0);
$pdf->Cell(0,10,date('d/m/Y', strtotime($ticket['event_date'])).' '.$ticket['event_time'],0,1);

$pdf->Cell(50,10,'Lugar:',0,0);
$pdf->Cell(0,10,utf8_decode($ticket['venue'].' - '.$ticket['city']),0,1);

$pdf->Cell(50,10,'Cantidad de boletos:',0,0);
$pdf->Cell(0,10,$ticket['quantity'],0,1);

$pdf->Cell(50,10,'Código de Ticket:',0,0);
$pdf->Cell(0,10,$ticket['ticket_code'],0,1);

$pdf->Cell(50,10,'Estado de ticket:',0,0);
$pdf->Cell(0,10,ucfirst($ticket['status']),0,1);

$pdf->Ln(15);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0,10,'Guarda este ticket. Será requerido para el ingreso al evento.',0,1,'C');


$pdf->Output('I', 'ticket_'.$ticket['ticket_code'].'.pdf');
exit;
?>