<?php 
include 'navbar.php';
require_once 'conexiune.php';

$tip_curent = isset($_GET['tip']) ? $_GET['tip'] : 'croaziera';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caută Excursie - TrioTravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .tip-excursie {
            text-align: center;
            padding: 2rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            color: #333;
        }

        .tip-excursie:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .tip-excursie i {
            font-size: 2.5rem;
            color: #1565c0;
            margin-bottom: 1rem;
        }

        .tip-excursie h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .tip-excursie p {
            color: #666;
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .tip-excursie.active {
            background: #1565c0;
            color: white;
        }

        .tip-excursie.active i,
        .tip-excursie.active h4,
        .tip-excursie.active p {
            color: white;
        }

        .btn-detalii {
            background-color: #1565c0;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-detalii:hover {
            background-color: #0d47a1;
            color: white;
            transform: translateY(-2px);
        }

        .print-header {
            display: none;
        }

        @media print {
            .navbar,
            .formular-cautare,
            .tip-excursie,
            .btn-detalii,
            .d-print-none,
            .text-center.mb-4,
            .d-flex.justify-content-between.align-items-center.mt-4.mb-4 {
                display: none !important;
            }

            .print-header {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 20px;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #333;
            }

            .print-header img {
                height: 60px;
                width: auto;
            }

            .print-header h2 {
                margin: 0;
                color: #333;
                font-size: 24px;
            }

            .card {
                break-inside: avoid;
                border: 1px solid #ddd;
                margin-bottom: 20px;
            }

            .col-md-4 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            .card-img-top {
                height: 150px !important;
            }

            @page {
                margin: 2cm;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <img src="images/logo.png" alt="TrioTravel Logo">
        <h2>TrioTravel - Agenție de Turism</h2>
    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Ce tip de vacanță căutați?</h2>

        <div class="row justify-content-center mb-5">
            <div class="col-md-4">
                <a href="?tip=croaziera" class="text-decoration-none">
                    <div class="tip-excursie <?php echo $tip_curent == 'croaziera' ? 'active' : ''; ?>">
                        <i class="bi bi-water"></i>
                        <h4>Croazieră</h4>
                        <p>Explorează mările și oceanele într-o vacanță de lux pe apă</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?tip=sejur" class="text-decoration-none">
                    <div class="tip-excursie <?php echo $tip_curent == 'sejur' ? 'active' : ''; ?>">
                        <i class="bi bi-sun"></i>
                        <h4>Sejur</h4>
                        <p>Relaxare și confort într-o locație de vis</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?tip=circuit" class="text-decoration-none">
                    <div class="tip-excursie <?php echo $tip_curent == 'circuit' ? 'active' : ''; ?>">
                        <i class="bi bi-compass"></i>
                        <h4>Circuit</h4>
                        <p>Descoperă multiple destinații într-o singură călătorie</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Formular căutare -->

        <?php if ($tip_curent == 'croaziera'): ?>
            <div class="formular-cautare">
                <form method="GET" action="">
                    <input type="hidden" name="tip" value="croaziera">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="numar_nopti" class="form-label">Număr nopți</label>
                            <select name="numar_nopti" id="numar_nopti" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                    $sql_nopti = "SELECT DISTINCT numar_nopti FROM excursii WHERE tip = 'Croaziera' ORDER BY numar_nopti";
                                    $result_nopti = $conn->query($sql_nopti);
                                    if ($result_nopti && $result_nopti->num_rows > 0) {
                                        while ($row = $result_nopti->fetch_assoc()) {
                                            $selected = (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] == $row['numar_nopti']) ? 'selected' : '';
                                            echo "<option value=\"{$row['numar_nopti']}\" $selected>{$row['numar_nopti']} nopți</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="categorie_nava" class="form-label">Categorie navă</label>
                            <select name="categorie_nava" id="categorie_nava" class="form-select">
                                <option value="">Toate</option>
                                <option value="Standard" <?php echo (isset($_GET['categorie_nava']) && $_GET['categorie_nava'] == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                                <option value="Premium" <?php echo (isset($_GET['categorie_nava']) && $_GET['categorie_nava'] == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                                <option value="Luxury" <?php echo (isset($_GET['categorie_nava']) && $_GET['categorie_nava'] == 'Luxury') ? 'selected' : ''; ?>>Luxury</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="porturi_oprire" class="form-label">Porturi de oprire</label>
                            <select name="porturi_oprire" id="porturi_oprire" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                // Conectare la baza de date
                                require_once 'conexiune.php';

                                // Obține toate porturile din coloana `porturi_oprire`
                                $sql = "SELECT DISTINCT porturi_oprire FROM croaziere";
                                $result = $conn->query($sql);

                                // Listă pentru a stoca porturile unice
                                $porturi = [];

                                if ($result) {
                                    while ($row = $result->fetch_assoc()) {
                                        $porturiArray = array_map('trim', explode(',', $row['porturi_oprire']));
                                        $porturi = array_merge($porturi, $porturiArray);
                                    }
                                }

                                // Eliminăm duplicatele și sortăm lista
                                $porturi = array_unique($porturi);
                                sort($porturi);

                                // Generăm opțiunile din porturile unice
                                foreach ($porturi as $port): ?>
                                    <option value="<?php echo htmlspecialchars($port); ?>" 
                                            <?php echo (isset($_GET['porturi_oprire']) && $_GET['porturi_oprire'] == $port) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($port); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="pret_maxim" class="form-label">Preț maxim</label>
                            <select name="pret_maxim" id="pret_maxim" class="form-select">
                                <option value="">Toate</option>
                                <option value="1000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1000') ? 'selected' : ''; ?>>Sub 1000 EUR</option>
                                <option value="1500" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1500') ? 'selected' : ''; ?>>Sub 1500 EUR</option>
                                <option value="2000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '2000') ? 'selected' : ''; ?>>Sub 2000 EUR</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_plecare" class="form-label">Data plecare</label>
                            <input type="date" name="data_plecare" id="data_plecare" class="form-control" 
                                   value="<?php echo isset($_GET['data_plecare']) ? $_GET['data_plecare'] : ''; ?>">
                         </div>
                         <div class="col-md-3 mb-3">
                            <label for="data_intoarcere" class="form-label">Data întoarcere</label>
                            <input type="date" name="data_intoarcere" id="data_intoarcere" class="form-control" 
                                   value="<?php echo isset($_GET['data_intoarcere']) ? $_GET['data_intoarcere'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="oferta_speciala" class="form-label">Ofertă specială</label>
                            <select name="oferta_speciala" id="oferta_speciala" class="form-select">
                                <option value="">Toate</option>
                                <option value="Revelion" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Revelion') ? 'selected' : ''; ?>>Revelion</option>
                                <option value="Paste" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Paste') ? 'selected' : ''; ?>>Paste</option>
                                <option value="1 Mai" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == '1 Mai') ? 'selected' : ''; ?>>1 Mai</option>
                                <option value="Craciun" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Craciun') ? 'selected' : ''; ?>>Craciun</option>
                                <option value="Last minute" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Last minute') ? 'selected' : ''; ?>>Last minute</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for"sezon_id" class="form-label">Sezon</label>
                            <select name="sezon_id" id="sezon_id" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                $sql_sezoane = "SELECT * FROM sezoane ORDER BY id";
                                $result_sezoane = $conn->query($sql_sezoane);
                                while ($sezon = $result_sezoane->fetch_assoc()) {
                                    $selected = (isset($_GET['sezon_id']) && $_GET['sezon_id'] == $sezon['id']) ? 'selected' : '';
                                    echo "<option value='" . $sezon['id'] . "' " . $selected . ">" . htmlspecialchars($sezon['nume']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" name="cauta" class="btn btn-primary">
                            <i class="bi bi-search"></i> Caută
                        </button>
                    </div>
                    </div>
                </form>
            </div>

            <?php
            $sql = "SELECT * , e.id as id_ex,e.nume as nume_ex, s.nume as sezon FROM excursii e 
                    JOIN croaziere c on e.id = c.excursie_id
                    LEFT JOIN sezoane s ON e.sezon_id = s.id
                    WHERE LOWER(e.tip) LIKE '%croaziera%'";

            if (isset($_GET['cauta'])) {
                if (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] !== '') {
                    $sql .= " AND numar_nopti = " . (int)$_GET['numar_nopti'];
                }
                if (isset($_GET['categorie_nava']) && $_GET['categorie_nava'] !== '') {
                    $sql .= " AND categorie_nava = '" . $conn->real_escape_string($_GET['categorie_nava']) . "'";
                }
                if (isset($_GET['porturi_oprire']) && $_GET['porturi_oprire'] !== '') {
                    $sql .= " AND porturi_oprire LIKE '%" . $conn->real_escape_string($_GET['porturi_oprire']) . "%'";
                }
                if (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] !== '') {
                    $sql .= " AND pret_cazare_per_persoana <= " . (int)$_GET['pret_maxim'];
                }
                if (isset($_GET['data_plecare']) && $_GET['data_plecare'] !== '') {
                    $sql .= " AND data_inceput >= '" . $conn->real_escape_string($_GET['data_plecare']) . "'";
                }
                if (isset($_GET['data_intoarcere']) && $_GET['data_intoarcere'] !== '') {
                    $sql .= " AND data_sfarsit <= '" . $conn->real_escape_string($_GET['data_intoarcere']) . "'";
                }
                if (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] !== '') {
                    $sql .= " AND oferta_speciala = '" . $conn->real_escape_string($_GET['oferta_speciala']) . "'";
                }
                if (isset($_GET['sezon_id']) && $_GET['sezon_id'] !== '') {
                    $sql .= " AND sezon_id = " . (int)$_GET['sezon_id'];
                }
            }

            $result = $conn->query($sql);
            
            // Afisare croaziere
            if ($result && $result->num_rows > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                    <h4><?php echo $result->num_rows; ?> croaziere găsite</h4>
                    <button onclick="window.print()" class="btn btn-outline-primary d-print-none">
                        <i class="bi bi-printer"></i> Tipărește lista
                    </button>
                </div>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($row['poza1']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($row['poza1']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($row['nume']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($row['nume_ex']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge-custom small">
                                            <i class="bi bi-moon"></i> <?php echo htmlspecialchars($row['numar_nopti']); ?> nopți
                                        </span>
                                        <span class="badge-custom small">
                                            <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($row['categorie_nava']); ?>
                                        </span>
                                    </div>
                                    <p class="card-text mb-2">
                                        <strong>Sezon:</strong> <?php echo htmlspecialchars($row['sezon']); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Perioadă:</strong> 
                                        <?php echo date('d.m.Y', strtotime($row['data_inceput'])) . ' - ' . date('d.m.Y', strtotime($row['data_sfarsit'])); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Preț:</strong> 
                                        <?php echo number_format($row['pret_cazare_per_persoana'], 2); ?> EUR
                                    </p>
                                    <div class="text-end">
                                        <a href="rezervare.php?id=<?php echo $row['id_ex']; ?>" class="btn btn-sm btn-detalii">
                                            Vezi detalii <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Nu au fost găsite croaziere care să corespundă criteriilor.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Formular căutare sejururi-->

        <?php if ($tip_curent == 'sejur'): ?>
            <div class="formular-cautare">
                <form method="GET" action="">
                    <input type="hidden" name="tip" value="sejur">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="numar_nopti" class="form-label">Număr nopți</label>
                            <select name="numar_nopti" id="numar_nopti" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                    $sql_nopti = "SELECT DISTINCT numar_nopti FROM excursii WHERE tip = 'Sejur' ORDER BY numar_nopti";
                                    $result_nopti = $conn->query($sql_nopti);
                                    if ($result_nopti && $result_nopti->num_rows > 0) {
                                        while ($row = $result_nopti->fetch_assoc()) {
                                            $selected = (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] == $row['numar_nopti']) ? 'selected' : '';
                                            echo "<option value=\"{$row['numar_nopti']}\" $selected>{$row['numar_nopti']} nopți</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tip_masa" class="form-label">Tip masă</label>
                            <select name="tip_masa" id="tip_masa" class="form-select">
                                <option value="" >Toate</option>
                                <option value="Mic dejun" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'Mic dejun') ? 'selected' : ''; ?>>Mic dejun</option>
                                <option value="Demipensiune" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'Demipensiune') ? 'selected' : ''; ?>>Demipensiune</option>
                                <option value="All inclusive" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'All inclusive') ? 'selected' : ''; ?>>All inclusive</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="rating_hotel" class="form-label">Stele hotel</label>
                            <select name="rating_hotel" id="rating_hotel" class="form-select">
                                <option value="">Toate</option>
                                <option value="1" <?php echo (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] == '1') ? 'selected' : ''; ?>>1 stea</option>
                                <option value="2" <?php echo (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] == '2') ? 'selected' : ''; ?>>2 stele</option>
                                <option value="3" <?php echo (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] == '3') ? 'selected' : ''; ?>>3 stele</option>
                                <option value="4" <?php echo (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] == '4') ? 'selected' : ''; ?>>4 stele</option>
                                <option value="5" <?php echo (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] == '5') ? 'selected' : ''; ?>>5 stele</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="pret_maxim" class="form-label">Preț maxim</label>
                            <select name="pret_maxim" id="pret_maxim" class="form-select">
                                <option value="">Toate</option>
                                <option value="1000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1000') ? 'selected' : ''; ?>>Sub 1000 EUR</option>
                                <option value="1500" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1500') ? 'selected' : ''; ?>>Sub 1500 EUR</option>
                                <option value="2000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '2000') ? 'selected' : ''; ?>>Sub 2000 EUR</option>
                            </select>
                        </div>
                            <div class="col-md-3 mb-3">
                            <label for="data_plecare" class="form-label">Data plecare</label>
                            <input type="date" name="data_plecare" id="data_plecare" class="form-control" 
                                   value="<?php echo isset($_GET['data_plecare']) ? $_GET['data_plecare'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_intoarcere" class="form-label ">Data întoarcere</label>
                            <input type="date" name="data_intoarcere" id="data_intoarcere" class="form-control" 
                                   value="<?php echo isset($_GET['data_intoarcere']) ? $_GET['data_intoarcere'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="oferta_speciala" class="form-label">Ofertă specială</label>
                            <select name="oferta_speciala" id="oferta_speciala" class="form-select">
                                <option value="">Toate</option>
                                <option value="Revelion" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Revelion') ? 'selected' : ''; ?>>Revelion</option>
                                <option value="Paste" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Paste') ? 'selected' : ''; ?>>Paste</option>
                                <option value="1 Mai" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == '1 Mai') ? 'selected' : ''; ?>>1 Mai</option>
                                <option value="Craciun" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Craciun') ? 'selected' : ''; ?>>Craciun</option>
                                <option value="Last minute" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Last minute') ? 'selected' : ''; ?>>Last minute</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for"sezon_id" class="form-label">Sezon</label>
                            <select name="sezon_id" id="sezon_id" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                $sql_sezoane = "SELECT * FROM sezoane ORDER BY id";
                                $result_sezoane = $conn->query($sql_sezoane);
                                while ($sezon = $result_sezoane->fetch_assoc()) {
                                    $selected = (isset($_GET['sezon_id']) && $_GET['sezon_id'] == $sezon['id']) ? 'selected' : '';
                                    echo "<option value='" . $sezon['id'] . "' " . $selected . ">" . htmlspecialchars($sezon['nume']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" name="cauta" class="btn btn-primary">
                            <i class="bi bi-search"></i> Caută
                        </button>
                    </div>
                    </div>
                </form>
            </div>

            <?php
            $sql = "SELECT *, e.id as id_ex, e.nume as nume_ex, s.nume as sezon FROM excursii e 
                    JOIN sejururi sj on e.id = sj.excursie_id
                    LEFT JOIN sezoane s ON e.sezon_id = s.id
                    WHERE LOWER(e.tip) LIKE '%sejur%'";
            
            if (isset($_GET['cauta'])) {
                if (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] !== '') {
                    $sql .= " AND numar_nopti = " . (int)$_GET['numar_nopti'];
                }
                if (isset($_GET['tip_masa']) && $_GET['tip_masa'] !== '') {
                    $sql .= " AND tip_masa = '" . $conn->real_escape_string($_GET['tip_masa']) . "'";
                }
                if (isset($_GET['rating_hotel']) && $_GET['rating_hotel'] !== '') {
                    $sql .= " AND rating_hotel = " . (int)$_GET['rating_hotel'];
                }
                if (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] !== '') {
                    $sql .= " AND pret_cazare_per_persoana <= " . (int)$_GET['pret_maxim'];
                }
                if (isset($_GET['data_plecare']) && $_GET['data_plecare'] !== '') {
                    $sql .= " AND data_inceput >= '" . $conn->real_escape_string($_GET['data_plecare']) . "'";
                }
                if (isset($_GET['data_intoarcere']) && $_GET['data_intoarcere'] !== '') {
                    $sql .= " AND data_sfarsit <= '" . $conn->real_escape_string($_GET['data_intoarcere']) . "'";
                }
                if (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] !== '') {
                    $sql .= " AND oferta_speciala = '" . $conn->real_escape_string($_GET['oferta_speciala']) . "'";
                }
                if (isset($_GET['sezon_id']) && $_GET['sezon_id'] !== '') {
                    $sql .= " AND sezon_id = " . (int)$_GET['sezon_id'];
                }
            }

            $result = $conn->query($sql);


            
            if ($result && $result->num_rows > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                    <h4><?php echo $result->num_rows; ?> sejururi găsite</h4>
                    <button onclick="window.print()" class="btn btn-outline-primary d-print-none">
                        <i class="bi bi-printer"></i> Tipărește lista
                    </button>
                </div>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($row['poza1']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($row['poza1']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($row['nume_ex']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($row['nume_ex']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge-custom small">
                                            <i class="bi bi-moon"></i> <?php echo htmlspecialchars($row['numar_nopti']); ?> nopți
                                        </span>
                                        <span class="badge-custom small">
                                            <i class="bi bi-star-fill"></i> <?php echo htmlspecialchars($row['rating_hotel']); ?> stele
                                        </span>
                                        <span class="badge-custom small">
                                            <i class="bi bi-cup-hot"></i> <?php echo htmlspecialchars($row['tip_masa']); ?>
                                        </span>
                                    </div>
                                    <p class="card-text mb-2">
                                        <strong>Sezon:</strong> <?php echo htmlspecialchars($row['sezon']); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Perioadă:</strong> 
                                        <?php echo date('d.m.Y', strtotime($row['data_inceput'])) . ' - ' . date('d.m.Y', strtotime($row['data_sfarsit'])); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Preț:</strong> 
                                        <?php echo number_format($row['pret_cazare_per_persoana'], 2); ?> EUR
                                    </p>
                                    <div class="text-end">
                                        <a href="rezervare.php?id=<?php echo $row['id_ex']; ?>" class="btn btn-sm btn-detalii">
                                            Vezi detalii <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Nu au fost găsite sejururi care să corespundă criteriilor.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Formular căutare circuite -->

        <?php if ($tip_curent == 'circuit'): ?>
            <div class="formular-cautare">
                <form method="GET" action="">
                    <input type="hidden" name="tip" value="circuit">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="numar_nopti" class="form-label">Număr nopți</label>
                            <select name="numar_nopti" id="numar_nopti" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                    $sql_nopti = "SELECT DISTINCT numar_nopti FROM excursii WHERE tip = 'Circuit' ORDER BY numar_nopti";
                                    $result_nopti = $conn->query($sql_nopti);
                                    if ($result_nopti && $result_nopti->num_rows > 0) {
                                        while ($row = $result_nopti->fetch_assoc()) {
                                            $selected = (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] == $row['numar_nopti']) ? 'selected' : '';
                                            echo "<option value=\"{$row['numar_nopti']}\" $selected>{$row['numar_nopti']} nopți</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="tip_masa" class="form-label">Tip masă</label>
                            <select name="tip_masa" id="tip_masa" class="form-select">
                                <option value="" >Toate</option>
                                <option value="Mic dejun" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'Mic dejun') ? 'selected' : ''; ?>>Mic dejun</option>
                                <option value="Demipensiune" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'Demipensiune') ? 'selected' : ''; ?>>Demipensiune</option>
                                <option value="All inclusive" <?php echo (isset($_GET['tip_masa']) && $_GET['tip_masa'] == 'All inclusive') ? 'selected' : ''; ?>>All inclusive</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="vizite_incluse" class="form-label">Vizite incluse</label>
                            <select name="vizite_incluse" id="vizite_incluse" class="form-select">
                            <option value="">Toate</option>
                            <?php
                            $sql_vizite = "SELECT DISTINCT vizite_incluse FROM circuite";
                            $result_vizite = $conn->query($sql_vizite);
                            while ($vizita = $result_vizite->fetch_assoc()) {
                                $viziteArray = array_map('trim', explode(',', $vizita['vizite_incluse']));
                                foreach ($viziteArray as $vizita) {
                                    $selected = (isset($_GET['vizite_incluse']) && $_GET['vizite_incluse'] == $vizita) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($vizita) . "' " . $selected . ">" . htmlspecialchars($vizita) . "</option>";
                                }
                            }
                            ?>
                            </select>

                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="pret_maxim" class="form-label">Preț maxim</label>
                            <select name="pret_maxim" id="pret_maxim" class="form-select">
                                <option value="">Toate</option>
                                <option value="1000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1000') ? 'selected' : ''; ?>>Sub 1000 EUR</option>
                                <option value="1500" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '1500') ? 'selected' : ''; ?>>Sub 1500 EUR</option>
                                <option value="2000" <?php echo (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] == '2000') ? 'selected' : ''; ?>>Sub 2000 EUR</option>
                            </select>
                        </div> 
                        <div class="col-md-3 mb-3">
                            <label for="data_plecare" class="form-label ">Data plecare</label>
                            <input type="date" name="data_plecare" id="data_plecare" class="form-control" 
                                   value="<?php echo isset($_GET['data_plecare']) ? $_GET['data_plecare'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="data_intoarcere" class="form-label">Data întoarcere</label>
                            <input type="date" name="data_intoarcere" id="data_intoarcere" class="form-control" 
                                   value="<?php echo isset($_GET['data_intoarcere']) ? $_GET['data_intoarcere'] : ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="oferta_speciala" class="form-label">Ofertă specială</label>
                            <select name="oferta_speciala" id="oferta_speciala" class="form-select">
                                <option value="">Toate</option>
                                <option value="Revelion" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Revelion') ? 'selected' : ''; ?>>Revelion</option>
                                <option value="Paste" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Paste') ? 'selected' : ''; ?>>Paste</option>
                                <option value="1 Mai" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == '1 Mai') ? 'selected' : ''; ?>>1 Mai</option>
                                <option value="Craciun" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Craciun') ? 'selected' : ''; ?>>Craciun</option>
                                <option value="Last minute" <?php echo (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] == 'Last minute') ? 'selected' : ''; ?>>Last minute</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for"sezon_id" class="form-label">Sezon</label>
                            <select name="sezon_id" id="sezon_id" class="form-select">
                                <option value="">Toate</option>
                                <?php
                                $sql_sezoane = "SELECT * FROM sezoane ORDER BY id";
                                $result_sezoane = $conn->query($sql_sezoane);
                                while ($sezon = $result_sezoane->fetch_assoc()) {
                                    $selected = (isset($_GET['sezon_id']) && $_GET['sezon_id'] == $sezon['id']) ? 'selected' : '';
                                    echo "<option value='" . $sezon['id'] . "' " . $selected . ">" . htmlspecialchars($sezon['nume']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" name="cauta" class="btn btn-primary">
                                <i class="bi bi-search"></i> Caută
                            </button>
                        </div>
                    </div>

                    
                </form>
            </div>

            <?php
            $sql = "SELECT *, e.id as id_ex, e.nume as nume_ex, s.nume as nume_sezon FROM excursii e 
                    JOIN circuite c on e.id = c.excursie_id
                    LEFT JOIN sezoane s ON e.sezon_id = s.id
                    WHERE LOWER(e.tip) LIKE '%circuit%'";
            
            if (isset($_GET['cauta'])) {
                if (isset($_GET['numar_nopti']) && $_GET['numar_nopti'] !== '') {
                    $sql .= " AND numar_nopti = " . (int)$_GET['numar_nopti'];
                }
                if (isset($_GET['sezon_id']) && $_GET['sezon_id'] !== '') {
                    $sql .= " AND sezon_id = " . (int)$_GET['sezon_id'];
                }
                if (isset($_GET['vizite_incluse']) && $_GET['vizite_incluse'] !== '') {
                    $sql .= " AND vizite_incluse LIKE '%" . $conn->real_escape_string($_GET['vizite_incluse']) . "%'";
                }
                if (isset($_GET['pret_maxim']) && $_GET['pret_maxim'] !== '') {
                    $sql .= " AND pret_cazare_per_persoana <= " . (int)$_GET['pret_maxim'];
                }
                if (isset($_GET['data_plecare']) && $_GET['data_plecare'] !== '') {
                    $sql .= " AND data_inceput >= '" . $conn->real_escape_string($_GET['data_plecare']) . "'";
                }
                if (isset($_GET['data_intoarcere']) && $_GET['data_intoarcere'] !== '') {
                    $sql .= " AND data_sfarsit <= '" . $conn->real_escape_string($_GET['data_intoarcere']) . "'";
                }
                if (isset($_GET['oferta_speciala']) && $_GET['oferta_speciala'] !== '') {
                    $sql .= " AND oferta_speciala = '" . $conn->real_escape_string($_GET['oferta_speciala']) . "'";
                }
                if (isset($_GET['tip_masa']) && $_GET['tip_masa'] !== '') {
                    $sql .= " AND tip_masa = '" . $conn->real_escape_string($_GET['tip_masa']) . "'";
                }
            }

            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                    <h4><?php echo $result->num_rows; ?> circuite găsite</h4>
                    <button onclick="window.print()" class="btn btn-outline-primary d-print-none">
                        <i class="bi bi-printer"></i> Tipărește lista
                    </button>
                </div>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if ($row['poza1']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($row['poza1']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($row['nume_ex']); ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($row['nume_ex']); ?></h6>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge-custom small">
                                            <i class="bi bi-moon"></i> <?php echo htmlspecialchars($row['numar_nopti']); ?> nopți
                                        </span>
                                    </div>
                                    <p class="card-text mb-2">
                                        <strong>Sezon:</strong> <?php echo htmlspecialchars($row['nume_sezon']); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Perioadă:</strong> 
                                        <?php echo date('d.m.Y', strtotime($row['data_inceput'])) . ' - ' . date('d.m.Y', strtotime($row['data_sfarsit'])); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <strong>Preț:</strong> 
                                        <?php echo number_format($row['pret_cazare_per_persoana'], 2); ?> EUR
                                    </p>
                                    <div class="text-end">
                                        <a href="rezervare.php?id=<?php echo $row['id_ex']; ?>" class="btn btn-sm btn-detalii">
                                            Vezi detalii <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    Nu au fost găsite circuite care să corespundă criteriilor.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
