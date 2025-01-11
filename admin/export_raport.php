<?php
require_once 'check_auth.php';
require_once '../conexiune.php';
require_once('../tcpdf/tcpdf.php');

// Verificăm dacă avem datele necesare în sesiune
if (!isset($_SESSION['raport_incasari']) || !isset($_SESSION['raport_reduceri'])) {
    die("Vă rugăm să generați mai întâi raportul din interfața web.");
}

// Asigurăm-ne că nu există output înainte de generarea PDF-ului
ob_clean();

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

// Folosim datele din sesiune pentru încasări
foreach ($_SESSION['raport_incasari'] as $row) {
    $pdf->Cell(60, 7, ucfirst($row['status_plata']), 1);
    $pdf->Cell(60, 7, $row['numar_rezervari'], 1);
    $pdf->Cell(60, 7, number_format($row['total_incasat'], 2) . ' EUR', 1);
    $pdf->Ln();
}

// Total general încasări
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(120, 7, 'Total General', 1);
$pdf->Cell(60, 7, number_format($_SESSION['total_general'], 2) . ' EUR', 1);
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

// Folosim datele din sesiune pentru reduceri
foreach ($_SESSION['raport_reduceri'] as $row) {
    $pdf->Cell(60, 7, $row['tip_reducere'], 1);
    $pdf->Cell(60, 7, $row['numar_aplicari'], 1);
    $pdf->Cell(60, 7, number_format($row['valoare_reduceri'], 2) . ' EUR', 1);
    $pdf->Ln();
}

// Total reduceri
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(120, 7, 'Total Reduceri', 1);
$pdf->Cell(60, 7, number_format($_SESSION['total_reduceri'], 2) . ' EUR', 1);

// La final, curățăm datele din sesiune
unset($_SESSION['raport_incasari']);
unset($_SESSION['raport_reduceri']);
unset($_SESSION['total_general']);
unset($_SESSION['total_reduceri']);

// Output PDF
$pdf->Output('Raport_Financiar_' . $an_selectat . ($luna_selectata ? '_' . $luni[$luna_selectata] : '') . '.pdf', 'D'); 