<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_role(ROLE_STUDENT);

$certificate_id = (int) ($_GET['id'] ?? 0);
$student_id = (int) get_current_user_id();

if ($certificate_id < 1) {
    redirect_with_message('pages/student/certificates.php', 'Certificat invalide.', ERROR);
}

try {
    $stmt = $pdo->prepare('
        SELECT c.*, u.name AS student_name, e.title AS event_title, e.event_date
        FROM certificates c
        INNER JOIN registrations r ON r.id = c.registration_id
        INNER JOIN users u ON u.id = r.student_id
        INNER JOIN events e ON e.id = r.event_id
        WHERE c.id = ? AND r.student_id = ?
    ');
    $stmt->execute([$certificate_id, $student_id]);
    $cert = $stmt->fetch();
    if (!$cert) {
        redirect_with_message('pages/student/certificates.php', 'Certificat introuvable.', ERROR);
    }

    // Try TCPDF or FPDF if installed in the project.
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
    if (class_exists('TCPDF')) {
        $pdf = new TCPDF();
        $pdf->SetCreator('UniEvents');
        $pdf->SetAuthor('UniEvents');
        $pdf->SetTitle('Certificate');
        $pdf->AddPage();
        $html = '<h1 style="text-align:center;">Certificate of Participation</h1>';
        $html .= '<p style="text-align:center;">This certifies that <strong>' . escape_output($cert['student_name']) . '</strong></p>';
        $html .= '<p style="text-align:center;">participated in <strong>' . escape_output($cert['event_title']) . '</strong></p>';
        $html .= '<p style="text-align:center;">Date: ' . escape_output((new DateTime($cert['event_date']))->format('d/m/Y')) . '</p>';
        $html .= '<p style="text-align:center;">Code: ' . escape_output($cert['cert_code']) . '</p>';
        $pdf->writeHTML($html);
        $pdf->Output('certificate-' . $cert['cert_code'] . '.pdf', 'D');
        exit();
    }

    if (class_exists('FPDF')) {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 15, 'Certificate of Participation', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Student: ' . $cert['student_name'], 0, 1, 'C');
        $pdf->Cell(0, 10, 'Event: ' . $cert['event_title'], 0, 1, 'C');
        $pdf->Cell(0, 10, 'Date: ' . (new DateTime($cert['event_date']))->format('d/m/Y'), 0, 1, 'C');
        $pdf->Cell(0, 10, 'Code: ' . $cert['cert_code'], 0, 1, 'C');
        $pdf->Output('D', 'certificate-' . $cert['cert_code'] . '.pdf');
        exit();
    }

    redirect_with_message('pages/student/certificates.php', 'Installez FPDF/TCPDF pour le telechargement PDF.', WARNING);
} catch (PDOException $e) {
    error_log('Download certificate error: ' . $e->getMessage());
    redirect_with_message('pages/student/certificates.php', 'Erreur de telechargement.', ERROR);
}
