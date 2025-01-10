<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

// Verifică dacă a fost transmis un ID valid
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: excursii.php');
    exit;
}

$excursie_id = intval($_GET['id']);

// Preluăm detaliile excursiei
$sql = "SELECT * FROM excursii WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $excursie_id);
$stmt->execute();
$result = $stmt->get_result();
$excursie = $result->fetch_assoc();

if (!$excursie) {
    header('Location: excursii.php');
    exit;
}
$transportOptions = [];
if (isset($excursie_id)) {
    $sqlTransport = "SELECT tip_transport, pret_per_persoana, descriere FROM optiuni_transport_excursii WHERE excursie_id = ?";
    $stmtTransport = $conn->prepare($sqlTransport);
    $stmtTransport->bind_param("i", $excursie_id);
    $stmtTransport->execute();
    $resultTransport = $stmtTransport->get_result();
    while ($row = $resultTransport->fetch_assoc()) {
        $transportOptions[] = $row;
    }
}


// Preluăm date specifice în funcție de tipul excursiei
$extraData = [];
if ($excursie['tip'] === 'Sejur') {
    $sqlSejur = "SELECT * FROM sejururi WHERE excursie_id = ?";
    $stmtSejur = $conn->prepare($sqlSejur);
    $stmtSejur->bind_param("i", $excursie_id);
    $stmtSejur->execute();
    $extraData = $stmtSejur->get_result()->fetch_assoc();
} elseif ($excursie['tip'] === 'Circuit') {
    $sqlCircuit = "SELECT * FROM circuite WHERE excursie_id = ?";
    $stmtCircuit = $conn->prepare($sqlCircuit);
    $stmtCircuit->bind_param("i", $excursie_id);
    $stmtCircuit->execute();
    $extraData = $stmtCircuit->get_result()->fetch_assoc();
} elseif ($excursie['tip'] === 'Croaziera') {
    $sqlCroaziera = "SELECT * FROM croaziere WHERE excursie_id = ?";
    $stmtCroaziera = $conn->prepare($sqlCroaziera);
    $stmtCroaziera->bind_param("i", $excursie_id);
    $stmtCroaziera->execute();
    $extraData = $stmtCroaziera->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tara_id'])) {
    header('Content-Type: application/json');

    $taraId = intval($_POST['tara_id']);
    $sqlLocatii = "SELECT id, nume FROM locatii WHERE tara_id = ? ORDER BY nume ASC";
    $stmt = $conn->prepare($sqlLocatii);
    $stmt->bind_param("i", $taraId);
    $stmt->execute();
    $result = $stmt->get_result();

    $locatii = [];
    while ($row = $result->fetch_assoc()) {
        $locatii[] = $row;
    }

    echo json_encode($locatii);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Modifică Excursie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
    <?php echo "<h1>Modifică " . htmlspecialchars($excursie['tip']) . "</h1>"; ?>
        <form action="process_modifica_excursie.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $excursie['id']; ?>">
            <input type="hidden" name="tip" value="<?php echo $excursie['tip']; ?>">

            <div class="mb-3">
                <label for="nume" class="form-label">Nume Excursie</label>
                <input type="text" class="form-control" id="nume" name="nume" value="<?php echo htmlspecialchars($excursie['nume']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="descriere" class="form-label">Descriere</label>
                <textarea class="form-control" id="descriere" name="descriere" rows="3"><?php echo htmlspecialchars($excursie['descriere']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="pret_cazare_per_persoana" class="form-label">Preț cazare per persoană (€)</label>
                <input type="number" class="form-control" id="pret_cazare_per_persoana" name="pret_cazare_per_persoana" min="0" value="<?php echo $excursie['pret_cazare_per_persoana']; ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tara_id" class="form-label">Țară</label>
                        <select class="form-select" id="tara_id" name="tara_id" required>
                            <option value="">Selectează țara</option>
                            <?php
                            // Obține ID-ul țării corespunzătoare locației curente
                            $sqlTaraSelectata = "SELECT t.id AS tara_id FROM tari t 
                                                JOIN locatii l ON t.id = l.tara_id 
                                                WHERE l.id = ?";
                            $stmtTaraSelectata = $conn->prepare($sqlTaraSelectata);
                            $stmtTaraSelectata->bind_param("i", $excursie['locatie_id']);
                            $stmtTaraSelectata->execute();
                            $resultTaraSelectata = $stmtTaraSelectata->get_result();
                            $taraSelectata = $resultTaraSelectata->fetch_assoc();

                            // Obține toate țările pentru lista dropdown
                            $sqlTari = "SELECT id, nume FROM tari ORDER BY nume ASC";
                            $resultTari = $conn->query($sqlTari);
                            while ($rowTara = $resultTari->fetch_assoc()) {
                                $selected = $taraSelectata && $taraSelectata['tara_id'] == $rowTara['id'] ? 'selected' : '';
                                echo "<option value='" . $rowTara['id'] . "' $selected>" . htmlspecialchars($rowTara['nume']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                    <label for="locatie_id" class="form-label">Locație</label>
                        <select class="form-select" id="locatie_id" name="locatie_id" required>
                            <option value="">Selectează locația</option>
                            <?php
                            // Obține ID-ul țării selectate (din excursie)
                            $sqlTaraSelectata = "SELECT t.id AS tara_id FROM tari t 
                                                JOIN locatii l ON t.id = l.tara_id 
                                                WHERE l.id = ?";
                            $stmtTaraSelectata = $conn->prepare($sqlTaraSelectata);
                            $stmtTaraSelectata->bind_param("i", $excursie['locatie_id']);
                            $stmtTaraSelectata->execute();
                            $resultTaraSelectata = $stmtTaraSelectata->get_result();
                            $taraSelectata = $resultTaraSelectata->fetch_assoc()['tara_id'];

                            // Obține locațiile doar pentru țara selectată
                            $sqlLocatii = "SELECT id, nume FROM locatii WHERE tara_id = ? ORDER BY nume ASC";
                            $stmtLocatii = $conn->prepare($sqlLocatii);
                            $stmtLocatii->bind_param("i", $taraSelectata);
                            $stmtLocatii->execute();
                            $resultLocatii = $stmtLocatii->get_result();

                            while ($rowLocatie = $resultLocatii->fetch_assoc()) {
                                // Marchează locația selectată
                                $selected = $excursie['locatie_id'] == $rowLocatie['id'] ? 'selected' : '';
                                echo "<option value='" . $rowLocatie['id'] . "' $selected>" . htmlspecialchars($rowLocatie['nume']) . "</option>";
                            }
                            ?>
                        </select>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sezon_id" class="form-label">Sezon</label>
                        <select class="form-select" id="sezon_id" name="sezon_id">
                            <option value="">Selectează sezonul</option>
                            <option value="1" <?php echo $excursie['sezon_id'] == 1 ? 'selected' : ''; ?>>Primăvară</option>
                            <option value="2" <?php echo $excursie['sezon_id'] == 2 ? 'selected' : ''; ?>>Vară</option>
                            <option value="3" <?php echo $excursie['sezon_id'] == 3 ? 'selected' : ''; ?>>Toamnă</option>
                            <option value="4" <?php echo $excursie['sezon_id'] == 4 ? 'selected' : ''; ?>>Iarnă</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="oferta_speciala" class="form-label">Ofertă Specială</label>
                        <select class="form-select" id="oferta_speciala" name="oferta_speciala">
                            <option value="">Fără ofertă specială</option>
                            <option value="Revelion" <?php echo $excursie['oferta_speciala'] == 'Revelion' ? 'selected' : ''; ?>>Revelion</option>
                            <option value="Paste" <?php echo $excursie['oferta_speciala'] == 'Paste' ? 'selected' : ''; ?>>Paște</option>
                            <option value="1 Mai" <?php echo $excursie['oferta_speciala'] == '1 Mai' ? 'selected' : ''; ?>>1 Mai</option>
                            <option value="Craciun" <?php echo $excursie['oferta_speciala'] == 'Craciun' ? 'selected' : ''; ?>>Crăciun</option>
                            <option value="Last minute" <?php echo $excursie['oferta_speciala'] == 'Last minute' ? 'selected' : ''; ?>>Last minute</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tip_masa" class="form-label">Tip Masă</label>
                        <select class="form-select" id="tip_masa" name="tip_masa">
                            <option value="">Selectează tipul de masă</option>
                            <option value="All inclusive" <?php echo $excursie['tip_masa'] == 'All inclusive' ? 'selected' : ''; ?>>All Inclusive</option>
                            <option value="Demipensiune" <?php echo $excursie['tip_masa'] == 'Demipensiune' ? 'selected' : ''; ?>>Demipensiune</option>
                            <option value="Mic dejun" <?php echo $excursie['tip_masa'] == 'Mic dejun' ? 'selected' : ''; ?>>Mic Dejun</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="numar_nopti" class="form-label">Număr Zile</label>
                        <input type="number" class="form-control" id="numar_nopti" name="numar_nopti" min="1" value="<?php echo $excursie['numar_nopti']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_inceput" class="form-label">Data Început</label>
                        <input type="date" class="form-control" id="data_inceput" name="data_inceput" value="<?php echo $excursie['data_inceput']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_sfarsit" class="form-label">Data Sfârșit</label>
                        <input type="date" class="form-control" id="data_sfarsit" name="data_sfarsit" value="<?php echo $excursie['data_sfarsit']; ?>" readonly>
                    </div>
                </div>
            </div>
            <!--TRANSPORT-->
            <div id="transport-container" class="mb-3">
    <label class="form-label">Opțiuni Transport</label>
    <!-- Exemple de transporturi existente -->
    <div class="transport-row d-flex align-items-center mb-2">
        <input type="hidden" name="transport_id[]" value="1"> <!-- ID-ul transportului existent -->
        <select class="form-select me-2" name="tip_transport[]" required>
            <option value="">Selectează tipul de transport</option>
            <option value="Autocar" selected>Autocar</option>
            <option value="Avion">Avion</option>
            <option value="Transport propriu">Transport propriu</option>
        </select>
        <input type="number" class="form-control me-2" name="pret_per_persoana[]" placeholder="Preț per persoană (€)" step="0.01" value="100" required>
        <input type="text" class="form-control me-2" name="descriere_transport[]" placeholder="Descriere transport" value="Transport inclus">
        <button type="button" class="btn btn-danger remove-transport">Șterge</button>
    </div>
</div>
<div class="mb-3">
    <button type="button" id="adauga-transport" class="btn btn-primary">Adaugă transport</button>
</div>
<div id="delete-transport-container"></div>



            <!--END TRANSPORT-->

            <div id="campuri-sejur" style="display: <?php echo $excursie['tip'] === 'Sejur' ? 'block' : 'none'; ?>;">
                <!-- Câmpuri specifice pentru Sejur -->
                <div class="mb-3">
                    <label for="tip_camera" class="form-label">Tip cameră</label>
                    <select class="form-select" id="tip_camera" name="tip_camera">
                        <option value="">Selectează tipul de cameră</option>
                        <option value="Standard" <?php echo $extraData['tip_camera'] == 'Standard' ? 'selected' : ''; ?>>Standard</option>
                        <option value="Deluxe" <?php echo $extraData['tip_camera'] == 'Deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                        <option value="Suite" <?php echo $extraData['tip_camera'] == 'Suite' ? 'selected' : ''; ?>>Suite</option>
                        <option value="Apartament" <?php echo $extraData['tip_camera'] == 'Apartament' ? 'selected' : ''; ?>>Family</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="facilitati_hotel" class="form-label">Facilități hotel</label>
                    <textarea class="form-control" id="facilitati_hotel" name="facilitati_hotel" rows="3"><?php echo htmlspecialchars($extraData['facilitati_hotel'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="rating_hotel" class="form-label">Număr stele hotel</label>
                    <input type="number" class="form-control" id="rating_hotel" name="rating_hotel" min="1" max="5" value="<?php echo htmlspecialchars($extraData['rating_hotel'] ?? ''); ?>">
                </div>
            </div>

            <div id="campuri-circuit" style="display: <?php echo $excursie['tip'] === 'Circuit' ? 'block' : 'none'; ?>;">
                <!-- Câmpuri specifice pentru Circuit -->
                <div class="mb-3">
                    <label for="descriere_traseu_circuit" class="form-label">Descriere Traseu</label>
                    <textarea class="form-control" id="descriere_traseu_circuit" name="descriere_traseu_circuit" rows="3"><?php echo htmlspecialchars($extraData['descriere_traseu'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="vizite_incluse_circuit" class="form-label">Vizite Incluse</label>
                    <textarea class="form-control" id="vizite_incluse_circuit" name="vizite_incluse_circuit" rows="3"><?php echo htmlspecialchars($extraData['vizite_incluse'] ?? ''); ?></textarea>
                </div>
            </div>

            <div id="campuri-croaziere" style="display: <?php echo $excursie['tip'] === 'Croaziera' ? 'block' : 'none'; ?>;">
                <!-- Câmpuri specifice pentru Croaziere -->
                <div class="mb-3">
                    <label for="categorie_nava" class="form-label">Categorie Navă</label>
                    <input type="text" class="form-control" id="categorie_nava" name="categorie_nava" value="<?php echo htmlspecialchars($extraData['categorie_nava'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="facilitati_vas" class="form-label">Facilități Vas</label>
                    <textarea class="form-control" id="facilitati_vas" name="facilitati_vas" rows="3"><?php echo htmlspecialchars($extraData['facilitati_vas'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="porturi_oprire" class="
                    form-label">Porturi Oprire</label>
                    <textarea class="form-control" id="porturi_oprire" name="porturi_oprire" rows="3"><?php echo htmlspecialchars($extraData['porturi_oprire'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="activitati_bord" class="form-label">Activități la Bord</label>
                    <textarea class="form-control" id="activitati_bord" name="activitati_bord" rows="3"><?php echo htmlspecialchars($extraData['activitati_bord'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="descriere_traseu" class="form-label">Descriere Traseu</label>
                    <textarea class="form-control" id="descriere_traseu" name="descriere_traseu" rows="3"><?php echo htmlspecialchars($extraData['descriere_traseu'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="vizite_incluse" class="form-label">Vizite Incluse</label>
                    <textarea class="form-control" id="vizite_incluse" name="vizite_incluse" rows="3"><?php echo htmlspecialchars($extraData['vizite_incluse'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="poza1" class="form-label">Poza 1</label>
                    <?php if (!empty($excursie['poza1'])): ?>
                        <div class="mb-2">
                            <img src="../uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="poza1" id="poza1">
                </div>
                <div class= "col-md-6">
                    <label for="poza2" class="form-label">Poza 2</label>
                    <?php if (!empty($excursie['poza2'])): ?>
                        <div class="mb-2">
                            <img src="../uploads/<?php echo htmlspecialchars($excursie['poza2']); ?>" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="poza2" id="poza2">
                    </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Salvează Modificările</button>
                <a href="excursii.php" class="btn btn-secondary">Anulează</a>
            </div>
            <input type="hidden" name="poza1_existenta" value="<?php echo htmlspecialchars($excursie['poza1']); ?>">
            <input type="hidden" name="poza2_existenta" value="<?php echo htmlspecialchars($excursie['poza2']); ?>">
            <input type="hidden" name="tip_cazare_id" value="<?php echo $excursie['tip_cazare_id']; ?>">
        </form>
    </div>

    <script>
        document.getElementById('tara_id').addEventListener('change', function() {
            const taraId = this.value;
            const locatieSelect = document.getElementById('locatie_id');

            locatieSelect.innerHTML = '<option value="">Selectează locația</option>'; // Resetare locații

            if (taraId) {
                fetch(window.location.href, { // Trimite cererea către același fișier
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ tara_id: taraId }),
                })
                .then(response => response.json())
                .then(data => {
                    data.forEach(locatie => {
                        const option = document.createElement('option');
                        option.value = locatie.id;
                        option.textContent = locatie.nume;
                        locatieSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Eroare la încărcarea locațiilor:', error));
            }
        });
        document.getElementById('numar_nopti').addEventListener('input', function() {
            const numarNopti = parseInt(this.value, 10);
            const dataInceput = document.getElementById('data_inceput').value;

            if (!isNaN(numarNopti) && dataInceput) {
                const dataInceputObj = new Date(dataInceput);
                dataInceputObj.setDate(dataInceputObj.getDate() + numarNopti);

                const dataSfarsit = dataInceput.toISOString().split('T')[0];
                document.getElementById('data_sfarsit').value = dataSfarsit;
            }
        });

        document.getElementById('data_inceput').addEventListener('change', function() {
            const dataInceput = this.value;
            const numarNopti = parseInt(document.getElementById('numar_nopti').value, 10);

            if (!isNaN(numarNopti) && dataInceput) {
                const dataInceputObj = new Date(dataInceput);
                dataInceputObj.setDate(dataInceputObj.getDate() + numarNopti);

                const dataSfarsit = dataInceputObj.toISOString().split('T')[0];
                document.getElementById('data_sfarsit').value = dataSfarsit;
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
    const transportContainer = document.getElementById('transport-container');
    const adaugaTransportBtn = document.getElementById('adauga-transport');

    // Funcție pentru a adăuga un rând nou
    adaugaTransportBtn.addEventListener('click', function () {
        const newRow = document.createElement('div');
        newRow.classList.add('transport-row', 'd-flex', 'align-items-center', 'mb-2');

        newRow.innerHTML = `
            <select class="form-select me-2" name="tip_transport[]" required>
                <option value="">Selectează tipul de transport</option>
                <option value="Autocar">Autocar</option>
                <option value="Avion">Avion</option>
                <option value="Transport propriu">Transport propriu</option>
            </select>
            <input type="number" class="form-control me-2" name="pret_per_persoana[]" placeholder="Preț per persoană (€)" step="0.01" required>
            <input type="text" class="form-control me-2" name="descriere_transport[]" placeholder="Descriere transport">
            <button type="button" class="btn btn-danger remove-transport">Șterge</button>
        `;
        transportContainer.appendChild(newRow);
    });

    // Funcție pentru a șterge un rând existent
    transportContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-transport')) {
            const row = e.target.closest('.transport-row');
            const hiddenInput = row.querySelector('input[name="transport_id[]"]');
            if (hiddenInput) {
                // Adaugă ID-ul transportului la un câmp ascuns pentru ștergere
                const deleteContainer = document.getElementById('delete-transport-container');
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_transport_ids[]';
                deleteInput.value = hiddenInput.value;
                deleteContainer.appendChild(deleteInput);
            }
            row.remove();
        }
    });
});




    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
