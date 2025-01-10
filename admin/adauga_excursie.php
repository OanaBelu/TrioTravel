<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success" role="alert">
        Excursia a fost adăugată cu succes!
    </div>
    <?php endif;
    

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['tara_id'])) {
        require_once '../conexiune.php';
        $taraId = intval($data['tara_id']);
        $sqlLocatii = "SELECT id, nume FROM locatii WHERE tara_id = $taraId ORDER BY nume ASC";
        $resultLocatii = $conn->query($sqlLocatii);

        $locatii = [];
        while ($rowLocatie = $resultLocatii->fetch_assoc()) {
            $locatii[] = $rowLocatie;
        }

        header('Content-Type: application/json');
        echo json_encode($locatii);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin - Detalii Excursie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Adaugă excursie nouă</h1>
        
        <form action="process_adauga_excursie.php" method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="nume" class="form-label">Nume Excursie</label>
                <input type="text" class="form-control" id="nume" name="nume" required>
            </div>

            <div class="mb-3">
                <label for="descriere" class="form-label">Descriere</label>
                <textarea class="form-control" id="descriere" name="descriere" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="tip" class="form-label">Tip Excursie</label>
                <select class="form-select" id="tip" name="tip" required>
                    <option value="">Selectează tipul</option>
                    <option value="Sejur">Sejur</option>
                    <option value="Circuit">Circuit</option>
                    <option value="Croaziera">Croaziera</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tara_id" class="form-label">Țară</label>
                        <select class="form-select" id="tara_id" name="tara_id" required>
                            <option value="">Selectează țara</option>
                            <?php
                            $sqlTari = "SELECT id, nume FROM tari ORDER BY nume ASC";
                            $resultTari = $conn->query($sqlTari);
                            while ($rowTara = $resultTari->fetch_assoc()) {
                                echo "<option value='" . $rowTara['id'] . "'>" . $rowTara['nume'] . "</option>";
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
                        </select>
                    </div>
                </div>
            </div>

            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sezon_id" class="form-label">Sezon</label>
                        <select class="form-select" id="sezon_id" name="sezon_id">
                            <option value="">Selectează sezonul</option>
                            <option value="1">Primăvară</option>
                            <option value="2">Vară</option>
                            <option value="3">Toamnă</option>
                            <option value="4">Iarnă</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="oferta_speciala" class="form-label">Ofertă Specială</label>
                        <select class="form-select" id="oferta_speciala" name="oferta_speciala">
                            <option value="">Fără ofertă specială</option>
                            <option value="Revelion">Revelion</option>
                            <option value="Paste">Paște</option>
                            <option value="1 Mai">1 Mai</option>
                            <option value="Craciun">Craciun</option>
                            <option value="Last minute">Last minute</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tip_masa" class="form-label">Tip Masă</label>
                        <select class="form-select" id="tip_masa" name="tip_masa">
                            <option value="">Selectează tipul de masă</option>
                            <option value="All inclusive">All Inclusive</option>
                            <option value="Demipensiune">Demipensiune</option>
                            <option value="Mic dejun">Mic Dejun</option>
                        </select>
                    </div>
                </div>
            </div>

            
            <div class="row align-items-end">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="numar_nopti" class="form-label">Număr nopți</label>
                        <input type="number" class="form-control" id="numar_nopti" name="numar_nopti" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_inceput" class="form-label">Data început</label>
                        <input type="date" class="form-control" id="data_inceput" name="data_inceput" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="data_sfarsit" class="form-label">Data sfârșit</label>
                        <input type="date" class="form-control" id="data_sfarsit" name="data_sfarsit" readonly>
                    </div>
                </div>
            </div>


            <div class="row align-items-end">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tip_cazare_id" class="form-label">Tip cazare</label>
                        <select class="form-select" id="tip_cazare_id" name="tip_cazare_id" required>
                            <option value="">Selectează tipul de cazare</option>
                            <!-- Populat din baza de date -->
                            <?php
                            $sql = "SELECT id, nume FROM tipuri_cazare";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['nume'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="pret_cazare_per_persoana" class="form-label">Preț cazare per persoană (€)</label>
                        <input type="number" class="form-control" id="pret_cazare_per_persoana" name="pret_cazare_per_persoana" step="0.01" required>
                    </div>
                </div>
            </div>

            <div class="row align-items-end">
                <div id="transport-container" class="mb-3">
                    <label class="form-label">Opțiuni Transport</label>
                    <!-- Rând inițial pentru transport -->
                    <div class="transport-row d-flex align-items-center mb-2">
                        <select class="form-select me-2" name="tip_transport[]" required>
                            <option value="">Selectează tipul de transport</option>
                            <option value="Autocar">Autocar</option>
                            <option value="Avion">Avion</option>
                            <option value="Transport propriu">Transport propriu</option>
                        </select>
                        <input type="number" class="form-control me-2" name="pret_per_persoana[]" placeholder="Preț per persoană (€)" step="0.01" required>
                        <input type="text" class="form-control me-2" name="descriere_transport[]" placeholder="Descriere transport">
                        <button type="button" class="btn btn-danger remove-transport">Șterge</button>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" id="adauga-transport" class="btn btn-primary">Adaugă transport</button>
                </div>
            </div>

            <!-- Câmpuri specifice pentru Sejur -->
            <div id="campuri-sejur" style="display: none;">
                <div class="mb-3">
                    <label for="tip_camera" class="form-label">Tip cameră</label>
                    <select class="form-select" id="tip_camera" name="tip_camera">
                        <option value="Standard">Standard</option>
                        <option value="Deluxe">Deluxe</option>
                        <option value="Suite">Suite</option>
                        <option value="Apartament">Apartament</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="facilitati_hotel" class="form-label">Facilități hotel</label>
                    <textarea class="form-control" id="facilitati_hotel" name="facilitati_hotel" rows="3" placeholder="Ex: WiFi, Piscină, Spa, Restaurant gourmet."></textarea>
                </div>

                <div class="mb-3">
                    <label for="rating_hotel" class="form-label">Număr stele hotel</label>
                    <input type="number" class="form-control" id="rating_hotel" name="rating_hotel" min="1" max="5">
                </div>
            </div>

            <!-- Câmpuri specifice pentru Circuit -->
            <div id="campuri-circuit" style="display: none;">
                <div class="mb-3">
                    <label for="descriere_traseu_circuit" class="form-label">Descriere Traseu</label>
                    <textarea class="form-control" id="descriere_traseu_circuit" name="descriere_traseu_circuit" rows="3" placeholder="Descrierea traseului"></textarea>
                </div>
                <div class="mb-3">
                    <label for="vizite_incluse_circuit" class="form-label">Vizite Incluse</label>
                    <textarea class="form-control" id="vizite_incluse_circuit" name="vizite_incluse_circuit" rows="3" placeholder="Vizitele incluse în circuit"></textarea>
                </div>
            </div>

            <!-- Câmpuri pentru Croaziere -->
            <div id="campuri-croaziere" style="display: none;">
                <div class="mb-3">
                    <label for="categorie_nava" class="form-label">Categorie Navă</label>
                    <select class="form-select" id="categorie_nava" name="categorie_nava">
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                        <option value="Luxury">Luxury</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="facilitati_vas" class="form-label">Facilități Vas</label>
                    <textarea class="form-control" id="facilitati_vas" name="facilitati_vas" rows="3" placeholder="Ex: Piscină, Spa, Restaurante gourmet"></textarea>
                </div>
                <div class="mb-3">
                    <label for="porturi_oprire" class="form-label">Porturi Oprire</label>
                    <textarea class="form-control" id="porturi_oprire" name="porturi_oprire" rows="3" placeholder="Ex: Santorini, Mykonos, Barcelona"></textarea>
                </div>
                <div class="mb-3">
                    <label for="activitati_bord" class="form-label">Activități la Bord</label>
                    <textarea class="form-control" id="activitati_bord" name="activitati_bord" rows="3" placeholder="Ex: Cursuri de dans, Spectacole live"></textarea>
                </div>
                <div class="mb-3">
                    <label for="descriere_traseu" class="form-label">Descriere Traseu</label>
                    <textarea class="form-control" id="descriere_traseu" name="descriere_traseu" rows="3" placeholder="Descrierea traseului croazierei"></textarea>
                </div>
                <div class="mb-3">
                    <label for="vizite_incluse" class="form-label">Vizite Incluse</label>
                    <textarea class="form-control" id="vizite_incluse" name="vizite_incluse" rows="3" placeholder="Vizitele incluse în croazieră"></textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="poza1" class="form-label">poza1</label>
                        <?php if (!empty($excursie['poza1'])): ?>
                            <div class="mb-2">
                                <img src="../uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" 
                                     class="img-thumbnail" style="max-height: 100px">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="poza1" id="poza1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="poza2" class="form-label">poza2</label>
                        <?php if (!empty($excursie['poza2'])): ?>
                            <div class="mb-2">
                                <img src="../uploads/<?php echo htmlspecialchars($excursie['poza2']); ?>" 
                                     class="img-thumbnail" style="max-height: 100px">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="poza2" id="poza2">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Salvează excursia</button>
                <a href="excursii.php" class="btn btn-secondary">Anulează</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('numar_nopti').addEventListener('input', function() {
            const numarNopti = parseInt(this.value, 10);
            const dataInceput = document.getElementById('data_inceput').value;

            if (!isNaN(numarNopti) && dataInceput) {
                const dataInceputObj = new Date(dataInceput);
                dataInceputObj.setDate(dataInceputObj.getDate() + numarNopti);

                const dataSfarsit = dataInceput                .toISOString().split('T')[0];
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

        //-- Afișează câmpurile specifice pentru Sejur/Circuit/Croazieră --//   
        document.getElementById('tip').addEventListener('change', function() {
            const tipExcursie = this.value;

            // Ascunde toate secțiunile specifice
            document.getElementById('campuri-sejur').style.display = 'none';
            document.getElementById('campuri-circuit').style.display = 'none';
            document.getElementById('campuri-croaziere').style.display = 'none';

            // Afișează câmpurile pentru tipul selectat
            if (tipExcursie === 'Sejur') {
                document.getElementById('campuri-sejur').style.display = 'block';
            } else if (tipExcursie === 'Circuit') {
                document.getElementById('campuri-circuit').style.display = 'block';
            } else if (tipExcursie === 'Croaziera') {
                document.getElementById('campuri-croaziere').style.display = 'block';
            }
        });

        document.getElementById('tara_id').addEventListener('change', function () {
        const taraId = this.value;
        const locatieSelect = document.getElementById('locatie_id');
        locatieSelect.innerHTML = '<option value="">Selectează locația</option>'; // Resetare locații

        if (taraId) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ tara_id: taraId })
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

    document.addEventListener('DOMContentLoaded', function () {
        const transportContainer = document.getElementById('transport-container');
        const adaugaTransportBtn = document.getElementById('adauga-transport');

        // Funcție pentru a adăuga un rând nou
        adaugaTransportBtn.addEventListener('click', function () {

            const lastRow = document.querySelector('#transport-container .transport-row:last-child');
    
            // Găsește valorile din ultimul rând
            const tipTransport = lastRow.querySelector('select[name="tip_transport[]"]').value.trim();
            const pretPerPersoana = lastRow.querySelector('input[name="pret_per_persoana[]"]').value.trim();
            const descriereTransport = lastRow.querySelector('input[name="descriere_transport[]"]').value.trim();

            // Verifică dacă câmpurile sunt completate
            if (!tipTransport || !pretPerPersoana) {
                alert('Te rog completează toate câmpurile înainte de a adăuga o nouă opțiune de transport.');
                return; // Oprește execuția dacă există câmpuri necompletate
            }
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

            // Adaugă funcționalitatea de ștergere pentru butonul nou
            const stergeBtns = newRow.querySelectorAll('.remove-transport');
            stergeBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    newRow.remove();
                });
            });
        });

        // Adaugă funcționalitatea de ștergere pentru rândurile existente
        transportContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-transport')) {
                const allRows = document.querySelectorAll('.transport-row'); // Toate rândurile existente
                if (allRows.length > 1) { // Permite ștergerea doar dacă există mai multe rânduri
                    e.target.closest('.transport-row').remove();
                } else {
                    alert('Trebuie să existe cel puțin o opțiune de transport.');
                }
            }
        });
    });


    
    </script>

</body>
</html>


