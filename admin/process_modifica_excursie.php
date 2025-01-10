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

    $originalName = basename($file['name']);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedTypes)) {
        throw new Exception('Tip de fișier nepermis. Sunt permise doar: ' . implode(', ', $allowedTypes));
    }

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Nu s-a putut crea directorul pentru upload-uri.');
        }
        chmod($uploadDir, 0777);
    }

    $fileName = $originalName;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Eroare la încărcarea fișierului.');
    }

    chmod($targetPath, 0777);
    return $fileName;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        $excursie_id = (int)$_POST['id'];
        $nume = $_POST['nume'];
        $tip = $_POST['tip'];
        $tip_masa = $_POST['tip_masa'];
        $tip_cazare_id = (int)$_POST['tip_cazare_id'] ?? null;
        $locatie_id = (int)$_POST['locatie_id'] ?? null;
        $oferta_speciala = $_POST['oferta_speciala'] ?? null;
        $sezon_id = (int)$_POST['sezon_id'] ?? null;
        $descriere = $_POST['descriere'] ?? null;
        $data_inceput = $_POST['data_inceput'];
        $data_sfarsit = $_POST['data_sfarsit'];
        $pret_cazare_per_persoana = (float)$_POST['pret_cazare_per_persoana'];
        $numar_nopti = (int)$_POST['numar_nopti'] ?? null;

        $poza1 = isset($_FILES['poza1']) && $_FILES['poza1']['error'] == 0 ? processImageUpload($_FILES['poza1']) : $_POST['poza1_existenta'];
        $poza2 = isset($_FILES['poza2']) && $_FILES['poza2']['error'] == 0 ? processImageUpload($_FILES['poza2']) : $_POST['poza2_existenta'];

        $sql = "UPDATE excursii SET
             tip = ?, 
             oferta_speciala = ?, 
             tip_masa = ?, 
             sezon_id = ?, 
             nume = ?, 
             descriere = ?, 
             data_inceput = ?, 
             data_sfarsit = ?, 
             pret_cazare_per_persoana = ?, 
             poza1 = ?, 
             poza2 = ?, 
             tip_cazare_id = ?, 
             locatie_id = ?, 
             numar_nopti = ?
             WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssissssdssiiii",
    $tip,
    $oferta_speciala,
    $tip_masa,
    $sezon_id,
    $nume,
    $descriere,
    $data_inceput,
    $data_sfarsit,
    $pret_cazare_per_persoana,
    $poza1,
    $poza2,
    $tip_cazare_id,
    $locatie_id,
    $numar_nopti,
    $excursie_id
);


        $stmt->execute();

        if ($tip === 'Sejur') {
            $tip_camera = $_POST['tip_camera'];
            $rating_hotel = $_POST['rating_hotel'];
            $facilitati_hotel = $_POST['facilitati_hotel'];

            $sql_sejur = "UPDATE sejururi SET tip_camera = ?, rating_hotel = ?, facilitati_hotel = ? WHERE excursie_id = ?";
            $stmt_sejur = $conn->prepare($sql_sejur);
            $stmt_sejur->bind_param("sssi", $tip_camera, $rating_hotel, $facilitati_hotel, $excursie_id);
            $stmt_sejur->execute();

        } elseif ($tip === 'Circuit') {
            $descriere_traseu_circuit = $_POST['descriere_traseu_circuit'];
            $vizite_incluse_circuit = $_POST['vizite_incluse_circuit'];

            $sql_circuit = "UPDATE circuite SET descriere_traseu = ?, vizite_incluse = ? WHERE excursie_id = ?";
            $stmt_circuit = $conn->prepare($sql_circuit);
            $stmt_circuit->bind_param("ssi", $descriere_traseu_circuit, $vizite_incluse_circuit, $excursie_id);
            $stmt_circuit->execute();

        } elseif ($tip === 'Croaziera') {
            $categorie_nava = $_POST['categorie_nava'];
            $facilitati_vas = $_POST['facilitati_vas'];
            $porturi_oprire = $_POST['porturi_oprire'];
            $activitati_bord = $_POST['activitati_bord'];
            $descriere_traseu = $_POST['descriere_traseu'];
            $vizite_incluse = $_POST['vizite_incluse'];

            $sql_croaziera = "UPDATE croaziere SET categorie_nava = ?, facilitati_vas = ?, porturi_oprire = ?, activitati_bord = ?, descriere_traseu = ?, vizite_incluse = ? WHERE excursie_id = ?";
            $stmt_croaziera = $conn->prepare($sql_croaziera);
            $stmt_croaziera->bind_param("ssssssi", $categorie_nava, $facilitati_vas, $porturi_oprire, $activitati_bord, $descriere_traseu, $vizite_incluse, $excursie_id);
            $stmt_croaziera->execute();
        }

        $sql_delete_transport = "DELETE FROM optiuni_transport_excursii WHERE excursie_id = ?";
        $stmt_delete_transport = $conn->prepare($sql_delete_transport);
        $stmt_delete_transport->bind_param("i", $excursie_id);
        $stmt_delete_transport->execute();

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
        header("Location: excursii.php?success=1");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error: " . $e->getMessage());
        header("Location: modifica_excursie.php?id=$excursie_id&error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
