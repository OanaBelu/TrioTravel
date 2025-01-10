<?php
require_once 'conexiune.php';

// Activăm afișarea erorilor pentru debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug log
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));

    $nume = $conn->real_escape_string($_POST['nume']);
    $email = $conn->real_escape_string($_POST['email']);
    $subiect = $conn->real_escape_string($_POST['subiect']);
    $mesaj = $conn->real_escape_string($_POST['mesaj']);

    $sql = "INSERT INTO mesaje (nume, email, subiect, mesaj) VALUES (?, ?, ?, ?)";

    // Debug log
    error_log("SQL Query: " . $sql);

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nume, $email, $subiect, $mesaj);
    
    if ($stmt->execute()) {
        error_log("Insert successful");
        echo json_encode(['success' => true, 'message' => 'Mesajul a fost trimis cu succes!']);
    } else {
        error_log("Insert failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Eroare la trimiterea mesajului: ' . $stmt->error]);
    }
    exit;
} else {
    error_log("Non-POST request received");
    echo json_encode(['success' => false, 'message' => 'Metoda de request invalidă']);
}
?> 