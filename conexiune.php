<?php
// Conectare la baza de date
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tema10";
$port = 3305;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificare conexiune
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}
?>
