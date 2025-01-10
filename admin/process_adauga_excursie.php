<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'check_auth.php';
require_once '../conexiune.php';

function processImageUpload($file, $uploadDir = '../uploads/') {
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }

    // Get file extension and original filename
    $originalName = basename($file['name']);
    
    // List of allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('Tip de fișier nepermis. Sunt permise doar: ' . implode(', ', $allowedTypes));
    }

    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Nu s-a putut crea directorul pentru upload-uri.');
        }
        chmod($uploadDir, 0777);
    }

    // Use original filename
    $fileName = $originalName;
    $targetPath = $uploadDir . $fileName;

    // Move the uploaded file directly, without trying to delete first
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Eroare la încărcarea fișierului.');
    }

    // Ensure correct permissions on the uploaded file
    chmod($targetPath, 0777);

    return $fileName;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received");
    error_log(print_r($_POST, true));
    error_log(print_r($_FILES, true));

    $conn->begin_transaction();
    try {
        // Procesare poze
        $poza1 = null;
        $poza2 = null;
        
        if(isset($_FILES['poza1']) && $_FILES['poza1']['error'] == 0) {
            $poza1 = processImageUpload($_FILES['poza1']);
            error_log("Poza1 processed: " . $poza1);
        }
        
        if(isset($_FILES['poza2']) && $_FILES['poza2']['error'] == 0) {
            $poza2 = processImageUpload($_FILES['poza2']);
            error_log("Poza2 processed: " . $poza2);
        }

        // Verificare dacă pozele au fost procesate corect
        if (isset($_FILES['poza1']) && $_FILES['poza1']['error'] == 0 && empty($poza1)) {
            throw new Exception('Eroare: Poza 1 nu a fost procesată corect');
        }
        if (isset($_FILES['poza2']) && $_FILES['poza2']['error'] == 0 && empty($poza2)) {
            throw new Exception('Eroare: Poza 2 nu a fost procesată corect');
        }

        // Declarăm variabilele generale
        $nume = $_POST['nume'];
        $tip = $_POST['tip'];
        $tip_cazare_id = $_POST['tip_cazare_id'] ?? null;
        $locatie_id = $_POST['locatie_id'] ?? null;
        $oferta_speciala = $_POST['oferta_speciala'] ?? null;
        $sezon_id = $_POST['sezon_id'] ?? null;
        $descriere = $_POST['descriere'] ?? null;
        $data_inceput = $_POST['data_inceput'];
        $data_sfarsit = $_POST['data_sfarsit'];
        $pret_cazare_per_persoana = $_POST['pret_cazare_per_persoana'];
        $numar_nopti = $_POST['numar_nopti'] ?? null;
        $status = 'activ';


        error_log("Attempting to insert excursie with data:");
        error_log("Nume: " . $nume);
        error_log("Tip: " . $tip);
        error_log("Pret: " . $pret_cazare_per_persoana);

        // Inserare în tabela excursii
        // Inserare în tabela excursii
        $sql = "INSERT INTO excursii (
            tip, 
            oferta_speciala, 
            tip_masa, 
            sezon_id, 
            nume, 
            descriere, 
            data_inceput, 
            data_sfarsit, 
            pret_cazare_per_persoana, 
            poza1, 
            poza2, 
            status, 
            tip_cazare_id, 
            locatie_id, 
            numar_nopti
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssii", 
            $tip, 
            $oferta_speciala, 
            $_POST['tip_masa'], 
            $sezon_id, 
            $nume, 
            $descriere, 
            $data_inceput, 
            $data_sfarsit, 
            $pret_cazare_per_persoana, 
            $poza1, 
            $poza2, 
            $status, 
            $tip_cazare_id, 
            $locatie_id, 
            $numar_nopti
        );

        $stmt->execute();

        $excursie_id = $conn->insert_id;

        // Procesare tip specific excursie
        if ($tip === 'Sejur') {
            $tip_camera = $_POST['tip_camera'];
            $rating_hotel = $_POST['rating_hotel'];
            $facilitati_hotel = $_POST['facilitati_hotel'];

            $sql_sejur = "INSERT INTO sejururi (excursie_id, tip_camera, rating_hotel, facilitati_hotel) 
                          VALUES (?, ?, ?, ?)";
            $stmt_sejur = $conn->prepare($sql_sejur);
            $stmt_sejur->bind_param("isss", 
                $excursie_id,
                $tip_camera,
                $rating_hotel,
                $facilitati_hotel
            );
            $stmt_sejur->execute();

        } elseif ($tip === 'Circuit') {
            $descriere_traseu_circuit = $_POST['descriere_traseu_circuit'];
            $vizite_incluse_circuit = $_POST['vizite_incluse_circuit'];

            $sql_circuit = "INSERT INTO circuite (excursie_id, descriere_traseu, vizite_incluse) 
                            VALUES (?, ?, ?)";
            $stmt_circuit = $conn->prepare($sql_circuit);
            $stmt_circuit->bind_param("iss", 
                $excursie_id,
                $descriere_traseu_circuit,
                $vizite_incluse_circuit
            );

            $stmt_circuit->execute();

        } elseif ($tip === 'Croaziera') {
            $categorie_nava = $_POST['categorie_nava'];
            $facilitati_vas = $_POST['facilitati_vas'];
            $porturi_oprire = $_POST['porturi_oprire'];
            $activitati_bord = $_POST['activitati_bord'];
            $descriere_traseu = $_POST['descriere_traseu'];
            $vizite_incluse = $_POST['vizite_incluse'];

            $sql_croaziera = "INSERT INTO croaziere (excursie_id, categorie_nava, facilitati_vas, porturi_oprire, activitati_bord, descriere_traseu, vizite_incluse) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_croaziera = $conn->prepare($sql_croaziera);
            $stmt_croaziera->bind_param("issssss", 
                $excursie_id,
                $categorie_nava,
                $facilitati_vas,
                $porturi_oprire,
                $activitati_bord,
                $descriere_traseu,
                $vizite_incluse
            );
            $stmt_croaziera->execute();
        }


        // Procesare opțiuni cazare
        $tip_transport = $_POST['tip_transport'];
        $pret_per_persoana = $_POST['pret_per_persoana'];
        $descriere_transport = $_POST['descriere_transport'];

        foreach ($tip_transport as $index => $transport) {
            if (!empty($transport) && isset($pret_per_persoana[$index])) {
                $pret = $pret_per_persoana[$index];
                $descriere = $descriere_transport[$index] ?? null;

                $sql_transport = "INSERT INTO optiuni_transport_excursii (excursie_id, tip_transport, pret_per_persoana, descriere) 
                                  VALUES (?, ?, ?, ?)";
                $stmt_transport = $conn->prepare($sql_transport);
                $stmt_transport->bind_param("isds", $excursie_id, $transport, $pret, $descriere);
                $stmt_transport->execute();
            }
        }


        $conn->commit();
        $_SESSION['success'] = "Excursia a fost adăugată cu succes!";

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        $_SESSION['error'] = "Eroare la adăugarea excursiei: " . $e->getMessage();
    
}
}
header("Location: excursii.php");
exit;
?>
        