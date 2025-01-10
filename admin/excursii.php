<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

// Procesare filtre
$conditions = [];
if (!empty($_GET['filter_nume'])) {
    $conditions[] = "e.nume LIKE '%" . $conn->real_escape_string($_GET['filter_nume']) . "%'";
}
if (!empty($_GET['filter_tip']) && $_GET['filter_tip'] !== 'Toate') {
    $conditions[] = "e.tip = '" . $conn->real_escape_string($_GET['filter_tip']) . "'";
}
if (!empty($_GET['filter_tip_masa']) && $_GET['filter_tip_masa'] !== 'Toate') {
    $conditions[] = "e.tip_masa = '" . $conn->real_escape_string($_GET['filter_tip_masa']) . "'";
}
if (!empty($_GET['filter_oferta_speciala']) && $_GET['filter_oferta_speciala'] !== 'Toate') {
    $conditions[] = "e.oferta_speciala = '" . $conn->real_escape_string($_GET['filter_oferta_speciala']) . "'";
}
if (!empty($_GET['filter_sezon']) && $_GET['filter_sezon'] !== 'Toate') {
    $conditions[] = "e.sezon_id = '" . $conn->real_escape_string($_GET['filter_sezon']) . "'";
}
if (!empty($_GET['filtru_numar_nopti'])) {
    $conditions[] = "e.numar_nopti = " . $conn->real_escape_string($_GET['filtru_numar_nopti']);
}
if (!empty($_GET['filter_pret_maxim'])) {
    $conditions[] = "e.pret_cazare_per_persoana <= " . $conn->real_escape_string($_GET['filter_pret_maxim']);
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(' AND ', $conditions);
}

// Query pentru date
$sql = "SELECT e.*, s.nume AS sezon_nume FROM excursii e 
        LEFT JOIN sezoane s ON e.sezon_id = s.id $whereClause";
$result = $conn->query($sql);

// Salvăm rezultatele într-un array
$excursii = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $excursii[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Admin - Excursii</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Administrare Excursii</h1>
            <a href="adauga_excursie.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Adaugă Excursie
            </a>
        </div>

        <!-- Formular filtre -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="filter_nume" class="form-label">Nume</label>
                    <input type="text" name="filter_nume" id="filter_nume" 
                           class="form-control" value="<?php echo $_GET['filter_nume'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter_tip" class="form-label">Tip</label>
                    <select name="filter_tip" id="filter_tip" class="form-control">
                        <option value="Toate">Toate</option>
                        <option value="Sejur" <?php echo (($_GET['filter_tip'] ?? '') == 'Sejur') ? 'selected' : ''; ?>>Sejur</option>
                        <option value="Circuit" <?php echo (($_GET['filter_tip'] ?? '') == 'Circuit') ? 'selected' : ''; ?>>Circuit</option>
                        <option value="Croaziera" <?php echo (($_GET['filter_tip'] ?? '') == 'Croaziera') ? 'selected' : ''; ?>>Croaziera</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_tip_masa" class="form-label">Tip Masă</label>
                    <select name="filter_tip_masa" id="filter_tip_masa" class="form-control">
                        <option value="Toate">Toate</option>
                        <option value="All inclusive" <?php echo (($_GET['filter_tip_masa'] ?? '') == 'All inclusive') ? 'selected' : ''; ?>>All inclusive</option>
                        <option value="Mic dejun" <?php echo (($_GET['filter_tip_masa'] ?? '') == 'Mic dejun') ? 'selected' : ''; ?>>Mic dejun</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_oferta_speciala" class="form-label">Ofertă Specială</label>
                    <select name="filter_oferta_speciala" id="filter_oferta_speciala" class="form-control">
                        <option value="Toate">Toate</option>
                        <option value="Revelion" <?php echo (($_GET['filter_oferta_speciala'] ?? '') == 'Revelion') ? 'selected' : ''; ?>>Revelion</option>
                        <option value="Paste" <?php echo (($_GET['filter_oferta_speciala'] ?? '') == 'Paste') ? 'selected' : ''; ?>>Paste</option>
                        <option value="1 Mai" <?php echo (($_GET['filter_oferta_speciala'] ?? '') == '1 Mai') ? 'selected' : ''; ?>>1 Mai</option>
                        <option value="Craciun" <?php echo (($_GET['filter_oferta_speciala'] ?? '') == 'Craciun') ? 'selected' : ''; ?>>Craciun</option>
                        <option value="Last minute" <?php echo (($_GET['filter_oferta_speciala'] ?? '') == 'Last minute') ? 'selected' : ''; ?>>Last minute</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_sezon" class="form-label">Sezon</label>
                    <select name="filter_sezon" id="filter_sezon" class="form-control">
                        <option value="Toate">Toate</option>
                        <?php
                        $sqlSezoane = "SELECT id, nume FROM sezoane";
                        $resultSezoane = $conn->query($sqlSezoane);
                        if ($resultSezoane && $resultSezoane->num_rows > 0) {
                            while ($row = $resultSezoane->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "' " . ((isset($_GET['filter_sezon']) && $_GET['filter_sezon'] == $row['id']) ? 'selected' : '') . ">" . $row['nume'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtru_numar_nopti" class="form-label">Număr Nopți</label>
                    <input type="number" name="filtru_numar_nopti" id="filtru_numar_nopti" 
                           class="form-control" value="<?php echo $_GET['filtru_numar_nopti'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="filter_pret_maxim" class="form-label">Preț Maxim</label>
                    <input type="number" name="filter_pret_maxim" id="filter_pret_maxim" 
                           class="form-control" value="<?php echo $_GET['filter_pret_maxim'] ?? ''; ?>">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrează</button>
                </div>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nume</th>
                    <th>Tip</th>
                    <th>Ofertă Specială</th>
                    <th>Tip Masă</th>
                    <th>Sezon</th>
                    <th>Număr nopți</th>
                    <th>Perioada</th>
                    <th>Preț</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($excursii)) {
                    foreach ($excursii as $row) {
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nume']); ?></td>
                            <td><?php echo htmlspecialchars($row['tip']); ?></td>
                            <td><?php echo htmlspecialchars($row['oferta_speciala']); ?></td>
                            <td><?php echo htmlspecialchars($row['tip_masa']); ?></td>
                            <td><?php echo htmlspecialchars($row['sezon_nume']); ?></td>
                            <td><?php echo $row['numar_nopti']; ?></td>
                            <td>
                                <?php 
                                echo date('d.m.Y', strtotime($row['data_inceput'])) . 
                                     " - " . 
                                     date('d.m.Y', strtotime($row['data_sfarsit'])); 
                                ?>
                            </td>
                            <td><?php echo number_format($row['pret_cazare_per_persoana'], 2); ?> €</td>
                            <td>
                                <a href="editeaza_excursie.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="detalii_excursie.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button onclick="if(confirm('Sigur doriți să ștergeți această excursie?')) 
                                               window.location='sterge_excursie.php?id=<?php echo $row['id']; ?>'" 
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>Nu există excursii</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
