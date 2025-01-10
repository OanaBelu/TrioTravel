<?php
require_once '../conexiune.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Statistici pentru dashboard
$sql_excursii = "SELECT COUNT(*) as total FROM excursii";
$sql_rezervari = "SELECT COUNT(*) as total FROM rezervari";
$sql_clienti = "SELECT COUNT(*) as total FROM clienti";
$sql_incasari = "SELECT SUM(pret_total) as total FROM rezervari"; // Am scos WHERE status = 'confirmata'

$result_excursii = $conn->query($sql_excursii);
$result_rezervari = $conn->query($sql_rezervari);
$result_clienti = $conn->query($sql_clienti);
$result_incasari = $conn->query($sql_incasari);

$excursii = $result_excursii->fetch_assoc()['total'];
$rezervari = $result_rezervari->fetch_assoc()['total'];
$clienti = $result_clienti->fetch_assoc()['total'];
$incasari = $result_incasari->fetch_assoc()['total'] ?? 0; // Am adăugat ?? 0 pentru cazul în care nu există rezervări

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1></h1>

        <div class="row mt-4">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Excursii</h5>
                        <h2><?php echo $excursii; ?></h2>
                        <a href="excursii.php" class="text-white">Vezi detalii →</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Rezervări</h5>
                        <h2><?php echo $rezervari; ?></h2>
                        <a href="rezervari.php" class="text-white">Vezi detalii →</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Clienți</h5>
                        <h2><?php echo $clienti; ?></h2>
                        <a href="clienti.php" class="text-white">Vezi detalii →</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Încasări Totale</h5>
                        <h2><?php echo number_format($incasari, 2); ?> €</h2>
                        <a href="rapoarte.php" class="text-white">Vezi detalii →</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Acțiuni Rapide</h5>
                        <div class="list-group">
                            <a href="adauga_excursie.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-plus-circle"></i> Adaugă excursie nouă
                            </a>
                            <a href="rezervari.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-calendar-check"></i> Vezi rezervări recente
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
