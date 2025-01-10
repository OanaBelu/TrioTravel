<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

// Obține anii disponibili pentru rapoarte
$result_ani = $conn->query("SELECT DISTINCT YEAR(data_creare) as an FROM rezervari ORDER BY an DESC");
$ani = [];
while ($row = $result_ani->fetch_assoc()) {
    $ani[] = $row['an'];
}

// Obținem lunile pentru filtrare
$luni = [
    1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie',
    4 => 'Aprilie', 5 => 'Mai', 6 => 'Iunie',
    7 => 'Iulie', 8 => 'August', 9 => 'Septembrie',
    10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
];

// Filtrare
$an_selectat = isset($_GET['an']) ? $_GET['an'] : date('Y');
$luna_selectata = isset($_GET['luna']) ? $_GET['luna'] : '';

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapoarte Financiare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .card-header h3 {
            margin-bottom: 0;
            color: #2c3e50;
        }
        .table-info {
            background-color: #e3f2fd;
        }
        .btn-success {
            margin-bottom: 1rem;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-4">
        <h1>Rapoarte Financiare</h1>

        <!-- Filtre -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">An</label>
                        <select name="an" class="form-select">
                            <?php foreach ($ani as $an): ?>
                                <option value="<?= $an ?>" <?= $an == $an_selectat ? 'selected' : '' ?>>
                                    <?= $an ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Luna (opțional)</label>
                        <select name="luna" class="form-select">
                            <option value="">Toate lunile</option>
                            <?php foreach ($luni as $nr => $nume): ?>
                                <option value="<?= $nr ?>" <?= $nr == $luna_selectata ? 'selected' : '' ?>>
                                    <?= $nume ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filtrează</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- După formularul de filtrare -->
        <div class="mb-3">
            <a href="export_raport.php?an=<?= $an_selectat ?><?= $luna_selectata ? '&luna='.$luna_selectata : '' ?>" 
               class="btn btn-success">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>

        <!-- Raport Încasări -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Încasări <?= $luna_selectata ? $luni[$luna_selectata] . ' ' : '' ?><?= $an_selectat ?></h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tip Plată</th>
                            <th>Număr Rezervări</th>
                            <th>Total Încasat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_incasari = "
                            SELECT 
                                r.status_plata,
                                COUNT(DISTINCT r.id) as numar_rezervari,
                                COALESCE(SUM(ch.suma), 0) as total_incasat
                            FROM rezervari r
                            LEFT JOIN chitante ch ON r.id = ch.rezervare_id AND ch.tip_operatie = 'plata'
                            WHERE YEAR(r.data_creare) = ?
                            " . ($luna_selectata ? "AND MONTH(r.data_creare) = ?" : "") . "
                            AND r.status_plata != 'anulata'
                            GROUP BY r.status_plata";

                        $stmt = $conn->prepare($sql_incasari);
                        if ($luna_selectata) {
                            $stmt->bind_param("ii", $an_selectat, $luna_selectata);
                        } else {
                            $stmt->bind_param("i", $an_selectat);
                        }
                        $stmt->execute();
                        $result_incasari = $stmt->get_result();

                        $total_general = 0;
                        while ($row = $result_incasari->fetch_assoc()):
                            $total_general += $row['total_incasat'];
                        ?>
                            <tr>
                                <td><?= ucfirst($row['status_plata']) ?></td>
                                <td><?= $row['numar_rezervari'] ?></td>
                                <td><?= number_format($row['total_incasat'], 2) ?> EUR</td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="table-info">
                            <td><strong>Total General</strong></td>
                            <td></td>
                            <td><strong><?= number_format($total_general, 2) ?> EUR</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Raport Reduceri -->
        <div class="card">
            <div class="card-header">
                <h3>Reduceri Acordate <?= $luna_selectata ? $luni[$luna_selectata] . ' ' : '' ?><?= $an_selectat ?></h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tip Reducere</th>
                            <th>Număr Aplicări</th>
                            <th>Valoare Totală Reduceri</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_reduceri = "
                            WITH rezervari_detaliate AS (
                                SELECT DISTINCT
                                    r.id as rezervare_id,
                                    r.client_id,
                                    r.data_creare,
                                    r.pret_cazare,
                                    r.transport_id,
                                    r.numar_adulti,
                                    r.numar_copii,
                                    c.este_client_top,
                                    r.status_plata,
                                    ch.suma as suma_chitanta,
                                    ch.id as chitanta_id,
                                    (r.pret_cazare * r.numar_adulti + 
                                     r.pret_cazare * r.numar_copii * 0.5 + 
                                     COALESCE(r.pret_transport, 0)) as pret_excursie,
                                    -- Verificăm dacă era client top la momentul rezervării
                                    (SELECT COUNT(*) >= 2
                                     FROM rezervari r_anterior
                                     WHERE r_anterior.client_id = r.client_id 
                                     AND r_anterior.data_creare < r.data_creare
                                     AND r_anterior.status_plata != 'anulata'
                                    ) as era_client_top
                                FROM chitante ch
                                JOIN rezervari r ON ch.rezervare_id = r.id
                                JOIN clienti c ON r.client_id = c.id
                                WHERE YEAR(ch.data_plata) = ?
                                " . ($luna_selectata ? "AND MONTH(ch.data_plata) = ?" : "") . "
                                AND ch.tip_operatie = 'plata'
                                AND r.status_plata != 'anulata'
                            )
                            SELECT tip_reducere, numar_aplicari, valoare_reduceri
                            FROM (
                                -- Client Top (2% din suma rămasă după reducerea de 5%)
                                SELECT 
                                    'Reducere Client Top (2%)' as tip_reducere,
                                    COUNT(DISTINCT CASE WHEN era_client_top = 1 AND status_plata = 'integral' THEN rezervare_id END) as numar_aplicari,
                                    COALESCE(SUM(
                                        CASE 
                                            WHEN era_client_top = 1 AND status_plata = 'integral'
                                            THEN pret_excursie * 0.95 * 0.02  -- 2% din suma după reducerea de 5%
                                            ELSE 0 
                                        END
                                    ), 0) as valoare_reduceri
                                FROM rezervari_detaliate
                                
                                UNION ALL
                                
                                -- Plată Integrală (5% din subtotal)
                                SELECT 
                                    'Reducere Plată Integrală (5%)' as tip_reducere,
                                    COUNT(DISTINCT CASE WHEN status_plata = 'integral' THEN rezervare_id END) as numar_aplicari,
                                    COALESCE(SUM(
                                        CASE 
                                            WHEN status_plata = 'integral' 
                                            THEN pret_excursie * 0.05  -- 5% din subtotal inițial
                                            ELSE 0 
                                        END
                                    ), 0) as valoare_reduceri
                                FROM rezervari_detaliate
                                
                                UNION ALL
                                
                                -- Copii (50% doar la cazare)
                                SELECT 
                                    'Reducere Copii Cazare (50%)' as tip_reducere,
                                    COUNT(DISTINCT CASE WHEN numar_copii > 0 THEN rezervare_id END) as numar_aplicari,
                                    COALESCE(SUM(
                                        CASE 
                                            WHEN numar_copii > 0
                                            THEN pret_cazare * numar_copii * 0.5
                                            ELSE 0 
                                        END
                                    ), 0) as valoare_reduceri
                                FROM rezervari_detaliate
                            ) reduceri";

                        $stmt = $conn->prepare($sql_reduceri);
                        if ($luna_selectata) {
                            $stmt->bind_param("ii", $an_selectat, $luna_selectata);
                        } else {
                            $stmt->bind_param("i", $an_selectat);
                        }
                        $stmt->execute();
                        $result_reduceri = $stmt->get_result();

                        $total_reduceri = 0;
                        if ($result_reduceri->num_rows > 0) {
                            while ($row = $result_reduceri->fetch_assoc()):
                                $total_reduceri += $row['valoare_reduceri'];
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['tip_reducere']) ?></td>
                                    <td><?= (int)$row['numar_aplicari'] ?></td>
                                    <td><?= sprintf('%.2f', floatval($row['valoare_reduceri'])) ?> EUR</td>
                                </tr>
                        <?php endwhile; 
                        } else { ?>
                            <tr>
                                <td colspan="3" class="text-center">Nu există reduceri în perioada selectată</td>
                            </tr>
                        <?php } ?>

                        <tr class="table-info">
                            <td><strong>Total Reduceri</strong></td>
                            <td></td>
                            <td><strong><?= number_format($total_reduceri, 2) ?> EUR</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Raport Chitanțe -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Chitanțe Emise <?= $luna_selectata ? $luni[$luna_selectata] . ' ' : '' ?><?= $an_selectat ?></h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nr. Chitanță</th>
                            <th>Data Emiterii</th>
                            <th>Client</th>
                            <th>Excursie</th>
                            <th>Sumă</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT ch.id as chitanta_id, 
                                DATE(ch.data_plata) as data_emiterii,
                                CONCAT(p.prenume, ' ', p.nume) as nume_client,
                                e.nume as nume_excursie,
                                ch.suma,
                                r.id as rezervare_id
                                FROM chitante ch
                                JOIN rezervari r ON ch.rezervare_id = r.id
                                JOIN excursii e ON r.excursie_id = e.id
                                JOIN participanti p ON r.id = p.rezervare_id
                                WHERE ch.tip_operatie = 'plata'
                                AND p.id = (
                                    SELECT MIN(id) 
                                    FROM participanti 
                                    WHERE rezervare_id = r.id
                                )
                                AND YEAR(ch.data_plata) = ?
                                ORDER BY ch.data_plata DESC";

                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $an_selectat);
                        $stmt->execute();
                        $result_chitante = $stmt->get_result();

                        $total_chitante = 0;
                        while ($row = $result_chitante->fetch_assoc()):
                            $total_chitante += $row['suma'];
                        ?>
                            <tr>
                                <td>#<?= $row['chitanta_id'] ?></td>
                                <td><?= date('d.m.Y', strtotime($row['data_emiterii'])) ?></td>
                                <td><?= htmlspecialchars($row['nume_client']) ?></td>
                                <td><?= htmlspecialchars($row['nume_excursie']) ?></td>
                                <td><?= number_format($row['suma'], 2) ?> EUR</td>
                                <td>
                                    <a href="print_chitanta.php?chitanta_id=<?= $row['chitanta_id'] ?>" 
                                       class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-printer"></i> Tipărește
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="table-info">
                            <td colspan="4"><strong>Total Chitanțe</strong></td>
                            <td colspan="2"><strong><?= number_format($total_chitante, 2) ?> EUR</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 