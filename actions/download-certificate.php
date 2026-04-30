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

    // Load installed FPDF library.
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
    if (!class_exists('FPDF')) {
        $fpdf_file = __DIR__ . '/../vendor/setasign/fpdf/fpdf.php';
        if (file_exists($fpdf_file)) {
            require_once $fpdf_file;
        }
    }

    if (!class_exists('FPDF')) {
        redirect_with_message('pages/student/certificates.php', 'FPDF non detecte.', WARNING);
    }

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetDrawColor(220, 20, 60);
    $pdf->SetLineWidth(1.2);
    $pdf->Rect(10, 10, 277, 190);
    $pdf->SetFont('Arial', 'B', 30);
    $pdf->SetTextColor(178, 34, 34);
    $pdf->Cell(0, 30, 'CERTIFICATE OF PARTICIPATION', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'This is awarded to', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 26);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(0, 16, iconv('UTF-8', 'windows-1252//TRANSLIT', $cert['student_name']), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell(0, 10, 'for successful participation in the event', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 12, iconv('UTF-8', 'windows-1252//TRANSLIT', $cert['event_title']), 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Date: ' . (new DateTime($cert['event_date']))->format('d/m/Y'), 0, 1, 'C');
    $pdf->Cell(0, 8, 'Certificate Code: ' . $cert['cert_code'], 0, 1, 'C');
    $pdf->Ln(18);
    $pdf->Cell(130, 8, '____________________________', 0, 0, 'C');
    $pdf->Cell(130, 8, '____________________________', 0, 1, 'C');
    $pdf->Cell(130, 8, 'Organizer Signature', 0, 0, 'C');
    $pdf->Cell(130, 8, 'UniEvents', 0, 1, 'C');
    $pdf->Output('D', 'certificate-' . $cert['cert_code'] . '.pdf');
    exit();
} catch (Throwable $e) {
    error_log('Download certificate error: ' . $e->getMessage());
    redirect_with_message('pages/student/certificates.php', 'Erreur de telechargement.', ERROR);
}
