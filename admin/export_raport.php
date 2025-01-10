<?php
require_once 'check_auth.php';
require_once '../conexiune.php';
require_once('../tcpdf/tcpdf.php'); // Asigură-te că calea este corectă

// Preluăm parametrii
$an_selectat = isset($_GET['an']) ? $_GET['an'] : date('Y');
$luna_selectata = isset($_GET['luna']) ? $_GET['luna'] : '';

// Array cu numele lunilor
$luni = [
    1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie',
    4 => 'Aprilie', 5 => 'Mai', 6 => 'Iunie',
    7 => 'Iulie', 8 => 'August', 9 => 'Septembrie',
    10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
];

// Creăm PDF-ul
class MYPDF extends TCPDF {
    public function Header() {
        // Logo
        $logo_path = '../images/logo.png';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, 10, 20);
        }
        
        // Aliniem textul la dreapta logo-ului
        $this->SetXY(35, 10);
        
        // Numele agenției
        $this->SetFont('dejavusans', 'B', 16);
        $this->Cell(0, 6, 'TrioTravel', 0, 1, 'C');
        
        // Date contact
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 4, 'Bulevardul Vasile Pârvan nr. 2, Timișoara, România', 0, 1, 'C');
        $this->Cell(0, 4, 'Tel: 0256 403 000 | Email: contact@triotravel.ro', 0, 1, 'C');
        $this->Cell(0, 4, 'www.triotravel.ro', 0, 1, 'C');
        
        // Linie separatoare
        $this->SetLineWidth(0.2);
        $this->Line(10, 35, $this->getPageWidth() - 10, 35);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }
}

// Inițializare PDF
$pdf = new MYPDF('P', 'mm', 'A4');
$pdf->SetCreator('Agenția de Turism');
$pdf->SetAuthor('Administrator');
$pdf->SetTitle('Raport Financiar ' . ($luna_selectata ? $luni[$luna_selectata] . ' ' : '') . $an_selectat);

// Adăugăm aceste linii pentru suport diacritice
$pdf->SetFont('dejavusans', '', 10); // folosim un font cu suport pentru diacritice
$pdf->setFontSubsetting(true);

// Adăugăm prima pagină
$pdf->AddPage();

// Adăugăm titlul și perioada după header
$pdf->SetY(40);
$pdf->SetFont('dejavusans', 'B', 14);
$pdf->Cell(0, 10, 'Raport Financiar', 0, 1, 'C');

$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(0, 6, 'Perioada: ' . ($luna_selectata ? $luni[$luna_selectata] . ' ' : '') . $an_selectat, 0, 1, 'L');
$pdf->Ln(5);

// Raport Încasări
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Încasări', 0, 1, 'L');

$pdf->SetFont('dejavusans', '', 10);
// Header tabel încasări
$pdf->Cell(60, 7, 'Tip Plată', 1);
$pdf->Cell(60, 7, 'Număr Rezervări', 1);
$pdf->Cell(60, 7, 'Total Încasat', 1);
$pdf->Ln();

// Date încasări
$sql_incasari = "SELECT 
    status_plata,
    COUNT(*) as numar_rezervari,
    SUM(pret_total) as total_incasat
FROM rezervari
WHERE YEAR(data_creare) = ?
" . ($luna_selectata ? "AND MONTH(data_creare) = ?" : "") . "
GROUP BY status_plata";

$stmt = $conn->prepare($sql_incasari);
if ($luna_selectata) {
    $stmt->bind_param("ii", $an_selectat, $luna_selectata);
} else {
    $stmt->bind_param("i", $an_selectat);
}
$stmt->execute();
$result_incasari = $stmt->get_result();

$total_general = 0;
while ($row = $result_incasari->fetch_assoc()) {
    $total_general += $row['total_incasat'];
    $pdf->Cell(60, 7, ucfirst($row['status_plata']), 1);
    $pdf->Cell(60, 7, $row['numar_rezervari'], 1);
    $pdf->Cell(60, 7, number_format($row['total_incasat'], 2) . ' EUR', 1);
    $pdf->Ln();
}

// Total general încasări
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(120, 7, 'Total General', 1);
$pdf->Cell(60, 7, number_format($total_general, 2) . ' EUR', 1);
$pdf->Ln(15);

// Raport Reduceri
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Reduceri Acordate', 0, 1, 'L');

$pdf->SetFont('dejavusans', '', 10);
// Header tabel reduceri
$pdf->Cell(60, 7, 'Tip Reducere', 1);
$pdf->Cell(60, 7, 'Număr Aplicări', 1);
$pdf->Cell(60, 7, 'Valoare Reduceri', 1);
$pdf->Ln();

// Query pentru reduceri (același ca în rapoarte.php)
$sql_reduceri = "
    SELECT 
        'Reducere Client Top (2%)' as tip_reducere,
        COALESCE(COUNT(*), 0) as numar_aplicari,
        COALESCE(SUM(pret_total * 0.02), 0) as valoare_reduceri
    FROM rezervari r
    JOIN clienti c ON r.client_id = c.id
    WHERE YEAR(r.data_creare) = ?
    " . ($luna_selectata ? "AND MONTH(r.data_creare) = ?" : "") . "
    AND c.este_client_top = 1
    AND r.status_plata = 'integral'
    
    UNION ALL
    
    SELECT 
        'Reducere Plată Integrală (5%)' as tip_reducere,
        COALESCE(COUNT(*), 0) as numar_aplicari,
        COALESCE(SUM(pret_total * 0.05), 0) as valoare_reduceri
    FROM rezervari
    WHERE YEAR(data_creare) = ?
    " . ($luna_selectata ? "AND MONTH(data_creare) = ?" : "") . "
    AND status_plata = 'integral'
    
    UNION ALL
    
    SELECT 
        'Reducere Copii Cazare (50%)' as tip_reducere,
        COALESCE(SUM(numar_copii), 0) as numar_aplicari,
        COALESCE(SUM(pret_cazare * 0.5 * numar_copii), 0) as valoare_reduceri
    FROM rezervari
    WHERE YEAR(data_creare) = ?
    " . ($luna_selectata ? "AND MONTH(data_creare) = ?" : "") . "
    AND numar_copii > 0
    AND status_plata != 'anulata'";

$stmt = $conn->prepare($sql_reduceri);
if ($luna_selectata) {
    $stmt->bind_param("iiiiii", 
        $an_selectat, $luna_selectata,
        $an_selectat, $luna_selectata,
        $an_selectat, $luna_selectata
    );
} else {
    $stmt->bind_param("iii", 
        $an_selectat, 
        $an_selectat, 
        $an_selectat
    );
}
$stmt->execute();
$result_reduceri = $stmt->get_result();

$total_reduceri = 0;
while ($row = $result_reduceri->fetch_assoc()) {
    $total_reduceri += $row['valoare_reduceri'];
    $pdf->Cell(60, 7, $row['tip_reducere'], 1);
    $pdf->Cell(60, 7, $row['numar_aplicari'], 1);
    $pdf->Cell(60, 7, number_format($row['valoare_reduceri'], 2) . ' EUR', 1);
    $pdf->Ln();
}

// Total reduceri
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(120, 7, 'Total Reduceri', 1);
$pdf->Cell(60, 7, number_format($total_reduceri, 2) . ' EUR', 1);

// Output PDF
$pdf->Output('Raport_Financiar_' . $an_selectat . ($luna_selectata ? '_' . $luni[$luna_selectata] : '') . '.pdf', 'D'); 