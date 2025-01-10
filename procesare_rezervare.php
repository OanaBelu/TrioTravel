<?php
session_start();
require_once 'conexiune.php';

// Activăm afișarea erorilor pentru debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperăm datele din sesiune
    $rezervare_data = $_SESSION['rezervare'];
    $excursie_id = $rezervare_data['excursie_id'];
    $transport_id = $rezervare_data['transport_id'];
    $adulti = $rezervare_data['adulti'];
    $copii = $rezervare_data['copii'];
    $tip_plata = $rezervare_data['tip_plata'];

    try {
        // Începe tranzacția
        $conn->begin_transaction();

        // Debug: Afișăm datele primite
        echo "<pre>Datele din POST:";
        print_r($_POST);
        echo "\nDatele din SESSION:";
        print_r($_SESSION);
        echo "</pre>";

        // 1. Inserăm toți adulții în tabelul clienti
        foreach ($_POST['adult'] as $adult) {
            echo "<br>Procesăm adultul: " . $adult['nume'] . " " . $adult['prenume'];

            $sql_client = "INSERT INTO clienti 
                          (nume, prenume, email, telefon, numar_identitate, este_client_top) 
                          VALUES (?, ?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE 
                          nume = VALUES(nume), 
                          prenume = VALUES(prenume), 
                          telefon = VALUES(telefon)";
            
            $stmt = $conn->prepare($sql_client);
            $nume = $adult['nume'];
            $prenume = $adult['prenume'];
            $email = $adult['email'];
            $telefon = $adult['telefon'];
            $numar_id = $adult['numar_identitate'];
            $este_client_top = 0;
            
            $stmt->bind_param("sssssi", 
                $nume, 
                $prenume, 
                $email, 
                $telefon, 
                $numar_id,
                $este_client_top
            );
            $stmt->execute();

            // Debug: Afișăm query-ul
            echo "<br>Query client: " . $sql_client;
        }

        // Inserăm și copiii în tabelul clienti
        if (isset($_POST['copil']) && !empty($_POST['copil'])) {
            foreach ($_POST['copil'] as $copil) {
                $sql_client = "INSERT INTO clienti 
                              (nume, prenume, email, telefon, numar_identitate, este_client_top) 
                              VALUES (?, ?, ?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              nume = VALUES(nume), 
                              prenume = VALUES(prenume)";
                
                $stmt = $conn->prepare($sql_client);
                $nume = $copil['nume'];
                $prenume = $copil['prenume'];
                $email = $copil['email'];
                $telefon = $copil['telefon'];
                $numar_id = $copil['numar_identitate'];
                $este_client_top = 0;
                
                $stmt->bind_param("sssssi", 
                    $nume, 
                    $prenume, 
                    $email, 
                    $telefon, 
                    $numar_id,
                    $este_client_top
                );
                $stmt->execute();
            }
        }

        // Folosim primul adult ca client principal
        $primul_adult = reset($_POST['adult']);
        echo "<br>Client principal: " . $primul_adult['nume'] . " " . $primul_adult['prenume'];

        $client_id = $conn->query("SELECT id FROM clienti WHERE email = '{$primul_adult['email']}'")->fetch_object()->id;

        // 2. Calculăm prețurile
        $sql_pret = "SELECT e.pret_cazare_per_persoana, t.pret_per_persoana as pret_transport 
                     FROM excursii e 
                     LEFT JOIN optiuni_transport_excursii t ON t.id = ? 
                     WHERE e.id = ?";
        $stmt = $conn->prepare($sql_pret);
        $stmt->bind_param("ii", $transport_id, $excursie_id);
        $stmt->execute();
        $preturi = $stmt->get_result()->fetch_object();

        // 3. Calculăm prețul total
        $pret_cazare = ($preturi->pret_cazare_per_persoana * $adulti) + 
                      ($preturi->pret_cazare_per_persoana * 0.5 * $copii);
        $pret_transport = $preturi->pret_transport * ($adulti + $copii);
        $pret_total = $pret_cazare + $pret_transport;
        $pret_original = $pret_total;

        // Inițializăm pret_final cu prețul total
        $pret_final = $pret_total;

        // Verifică dacă clientul este client top
        $sql_client = "SELECT este_client_top FROM clienti WHERE id = ?";
        $stmt = $conn->prepare($sql_client);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $client_data = $stmt->get_result()->fetch_object();

        // Aplică reducerile în ordinea corectă
        if ($tip_plata == 'integral') {
            // Mai întâi aplicăm reducerea de 5% pentru plata integrală
            $pret_final = $pret_total * 0.95;
            $suma_plata = $pret_final;
            
            // Apoi aplicăm reducerea de 2% pentru client top dacă este cazul
            if ($client_data->este_client_top) {
                $pret_final = $pret_final * 0.98;
                $suma_plata = $pret_final;
            }
        } elseif ($tip_plata == 'avans') {
            // Pentru avans, calculăm 20% din prețul original
            $suma_plata = $pret_original * 0.2;
            
            // Apoi aplicăm reducerea de 2% pentru client top dacă este cazul
            if ($client_data->este_client_top) {
                $pret_final = $pret_final * 0.98;
            }
        }

        // 4. Creează rezervarea
        $sql_rezervare = "INSERT INTO rezervari 
                         (client_id, excursie_id, transport_id, numar_adulti, numar_copii, 
                          pret_cazare, pret_transport, pret_total, status_plata, suma_plata) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql_rezervare);
        $stmt->bind_param("iiiiddddss", 
            $client_id, 
            $excursie_id, 
            $transport_id, 
            $adulti, 
            $copii,
            $pret_cazare, 
            $pret_transport, 
            $pret_final,
            $tip_plata, 
            $suma_plata
        );
        $stmt->execute();
        
        $rezervare_id = $stmt->insert_id;

        // Adaugă participanții
        $sql_participant = "INSERT INTO participanti 
                           (rezervare_id, nume, prenume, email, telefon, numar_identitate, tip_participant) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Adaugă adulții
        foreach ($_POST['adult'] as $adult) {
            $stmt = $conn->prepare($sql_participant);
            // Primul adult este clientul principal, restul sunt însoțitori
            $tip = ($adult === reset($_POST['adult'])) ? 'client' : 'insotitor';
            $stmt->bind_param("issssss", 
                $rezervare_id,
                $adult['nume'],
                $adult['prenume'],
                $adult['email'],
                $adult['telefon'],
                $adult['numar_identitate'],
                $tip
            );
            $stmt->execute();
        }
        
        // Adaugă copiii
        if (isset($_POST['copil']) && !empty($_POST['copil'])) {
            foreach ($_POST['copil'] as $copil) {
                $stmt = $conn->prepare($sql_participant);
                $tip = 'copil';
                $stmt->bind_param("issssss", 
                    $rezervare_id,
                    $copil['nume'],
                    $copil['prenume'],
                    $copil['email'],
                    $copil['telefon'],
                    $copil['numar_identitate'],
                    $tip
                );
                $stmt->execute();
            }
        }

        // 5. Creează chitanța
        $sql_chitanta = "INSERT INTO chitante (rezervare_id, suma) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_chitanta);
        $stmt->bind_param("id", $rezervare_id, $suma_plata);
        $stmt->execute();

        // Finalizează tranzacția
        $conn->commit();

        // Curăță sesiunea
        unset($_SESSION['rezervare']);

        // Redirecționează către pagina de confirmare
        header("Location: confirmare_rezervare.php?id=" . $rezervare_id);
        exit;

        // Debug: Afișăm query-urile importante
        echo "<br>Query verificare client top: " . $sql_client;
        echo "<br>Query inserare rezervare: " . $sql_rezervare;

    } catch (Exception $e) {
        $conn->rollback();
        // Debug: Afișăm stack trace-ul complet
        echo "<pre>Eroare:\n";
        echo $e->getMessage() . "\n";
        echo "În fișierul: " . $e->getFile() . "\n";
        echo "La linia: " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "</pre>";
        die();
    }
}
?>
