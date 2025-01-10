<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: excursii.php");
    exit;
}

$id = $_GET['id'];

// Verificăm dacă excursia are rezervări
$stmt = $conn->prepare("SELECT COUNT(*) as numar FROM rezervari WHERE excursie_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$rezervari = $result->fetch_object()->numar;

if ($rezervari > 0) {
    $_SESSION['error'] = "Nu se poate șterge excursia deoarece are rezervări asociate!";
    header("Location: excursii.php");
    exit;
}

// Preluăm pozele pentru a le șterge
$stmt = $conn->prepare("SELECT poza1, poza2 FROM excursii WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$excursie = $result->fetch_assoc();

// Ștergem pozele din folder
if ($excursie['poza1'] && file_exists('../uploads/' . $excursie['poza1'])) {
    unlink('../uploads/' . $excursie['poza1']);
}
if ($excursie['poza2'] && file_exists('../uploads/' . $excursie['poza2'])) {
    unlink('../uploads/' . $excursie['poza2']);
}

// Ștergem excursia din baza de date
$stmt = $conn->prepare("DELETE FROM excursii WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Resetăm auto_increment
    $conn->query("ALTER TABLE excursii AUTO_INCREMENT = 1");
    $_SESSION['success'] = "Excursia a fost ștearsă cu succes!";
} else {
    $_SESSION['error'] = "Eroare la ștergerea excursiei!";
}

header("Location: excursii.php");
exit;
?>
