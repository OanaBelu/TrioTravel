<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID rezervare invalid!";
    header("Location: rezervari.php");
    exit;
}

$id = $_GET['id'];

// Preluăm detaliile rezervării
$stmt = $conn->prepare("SELECT * FROM rezervari WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$rezervare = $stmt->get_result()->fetch_assoc();

if (!$rezervare) {
    $_SESSION['error'] = "Rezervarea nu a fost găsită!";
    header("Location: rezervari.php");
    exit;
}

// Calculăm suma de returnat
$suma_returnata = 0;
if ($rezervare['suma_plata'] >= $rezervare['pret_total']) {
    // A plătit integral - returnăm 80% din suma totală
    $suma_returnata = $rezervare['pret_total'] * 0.8;
} else if ($rezervare['suma_plata'] > 0) {
    // A plătit doar avansul - nu returnăm nimic
    $suma_returnata = 0;
}

// Începem tranzacția
$conn->begin_transaction();

try {
    // Actualizăm statusul rezervării
    $stmt = $conn->prepare("UPDATE rezervari SET status_plata = 'anulata' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Dacă există sumă de returnat, înregistrăm în chitanțe
    if ($suma_returnata > 0) {
        $stmt = $conn->prepare("INSERT INTO chitante (rezervare_id, suma, tip_operatie) VALUES (?, ?, 'retur')");
        $stmt->bind_param("id", $id, $suma_returnata);
        $stmt->execute();
    }

    $conn->commit();
    $_SESSION['success'] = "Rezervarea a fost anulată cu succes!" . 
                          ($suma_returnata > 0 ? " Suma de returnat: " . number_format($suma_returnata, 2) . " €" : "");
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Eroare la anularea rezervării: " . $e->getMessage();
}

header("Location: rezervari.php");
exit;
?>
