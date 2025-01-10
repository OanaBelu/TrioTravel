<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

// Preluăm parametrii de filtrare
$tip = isset($_GET['tip']) ? $_GET['tip'] : '';
$sezon_id = isset($_GET['sezon_id']) ? $_GET['sezon_id'] : '';
$tara_id = isset($_GET['tara_id']) ? $_GET['tara_id'] : '';
$tip_cazare_id = isset($_GET['tip_cazare_id']) ? $_GET['tip_cazare_id'] : '';
$transport = isset($_GET['transport']) ? $_GET['transport'] : '';

// Construim query-ul de bază
$sql = "SELECT e.*, 
        t.nume as nume_tara,
        s.nume as nume_sezon,
        tc.nume as tip_cazare,
        l.nume as nume_locatie
        FROM excursii e
        LEFT JOIN sejururi sj ON e.id = sj.excursie_id
        LEFT JOIN locatii l ON sj.locatie_id = l.id
        LEFT JOIN tari t ON l.tara_id = t.id
        LEFT JOIN sezoane s ON e.sezon_id = s.id
        LEFT JOIN tipuri_cazare tc ON sj.tip_cazare_id = tc.id
        WHERE 1=1";

// Adăugăm condițiile de filtrare
if ($tip) {
    $sql .= " AND e.tip = ?";
}
if ($sezon_id) {
    $sql .= " AND e.sezon_id = ?";
}
if ($tara_id) {
    $sql .= " AND t.id = ?";
}
if ($tip_cazare_id) {
    $sql .= " AND sj.tip_cazare_id = ?";
}
if ($transport) {
    $sql .= " AND EXISTS (SELECT 1 FROM optiuni_transport_excursii ot 
              WHERE ot.excursie_id = e.id AND ot.tip_transport = ?)";
}

$sql .= " ORDER BY e.data_inceput";

// Pregătim și executăm query-ul
$stmt = $conn->prepare($sql);

// Construim array-ul de parametri pentru bind
$types = '';
$params = [];
if ($tip) {
    $types .= 's';
    $params[] = $tip;
}
if ($sezon_id) {
    $types .= 'i';
    $params[] = $sezon_id;
}
if ($tara_id) {
    $types .= 'i';
    $params[] = $tara_id;
}
if ($tip_cazare_id) {
    $types .= 'i';
    $params[] = $tip_cazare_id;
}
if ($transport) {
    $types .= 's';
    $params[] = $transport;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Preluăm datele pentru dropdown-uri
$query_tari = "SELECT id, nume FROM tari ORDER BY nume";
$tari = $conn->query($query_tari)->fetch_all(MYSQLI_ASSOC);

$query_sezoane = "SELECT id, nume FROM sezoane ORDER BY data_inceput";
$sezoane = $conn->query($query_sezoane)->fetch_all(MYSQLI_ASSOC);

$query_tipuri_cazare = "SELECT id, nume FROM tipuri_cazare ORDER BY nume";
$tipuri_cazare = $conn->query($query_tipuri_cazare)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listă Excursii</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Listă Excursii</h1>

        <!-- Filtre -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tip</label>
                        <select name="tip" class="form-select">
                            <option value="">Toate</option>
                            <option value="sejur" <?php echo $tip == 'sejur' ? 'selected' : ''; ?>>Sejur</option>
                            <option value="circuit" <?php echo $tip == 'circuit' ? 'selected' : ''; ?>>Circuit</option>
                            <option value="croaziera" <?php echo $tip == 'croaziera' ? 'selected' : ''; ?>>Croazieră</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sezon</label>
                        <select name="sezon_id" class="form-select">
                            <option value="">Toate</option>
                            <?php foreach ($sezoane as $sezon): ?>
                            <option value="<?php echo $sezon['id']; ?>" 
                                    <?php echo $sezon_id == $sezon['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sezon['nume']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Țară</label>
                        <select name="tara_id" class="form-select">
                            <option value="">Toate</option>
                            <?php foreach ($tari as $tara): ?>
                            <option value="<?php echo $tara['id']; ?>"
                                    <?php echo $tara_id == $tara['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tara['nume']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tip Cazare</label>
                        <select name="tip_cazare_id" class="form-select">
                            <option value="">Toate</option>
                            <?php foreach ($tipuri_cazare as $tip_cazare): ?>
                            <option value="<?php echo $tip_cazare['id']; ?>"
                                    <?php echo $tip_cazare_id == $tip_cazare['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tip_cazare['nume']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Transport</label>
                        <select name="transport" class="form-select">
                            <option value="">Toate</option>
                            <option value="autocar" <?php echo $transport == 'autocar' ? 'selected' : ''; ?>>Autocar</option>
                            <option value="avion" <?php echo $transport == 'avion' ? 'selected' : ''; ?>>Avion</option>
                            <option value="transport propriu" <?php echo $transport == 'transport propriu' ? 'selected' : ''; ?>>Transport Propriu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filtrează</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista excursii -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nume</th>
                        <th>Tip</th>
                        <th>Locație</th>
                        <th>Perioadă</th>
                        <th>Sezon</th>
                        <th>Preț/pers</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($excursie = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $excursie['id']; ?></td>
                        <td><?php echo htmlspecialchars($excursie['nume']); ?></td>
                        <td><?php echo ucfirst($excursie['tip']); ?></td>
                        <td>
                            <?php 
                            echo htmlspecialchars($excursie['nume_locatie'] ?? '');
                            if (!empty($excursie['nume_tara'])) {
                                echo ', ' . htmlspecialchars($excursie['nume_tara']);
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            echo date('d.m.Y', strtotime($excursie['data_inceput'])) . ' - ' . 
                                 date('d.m.Y', strtotime($excursie['data_sfarsit']));
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($excursie['nume_sezon'] ?? ''); ?></td>
                        <td><?php echo number_format($excursie['pret_cazare_per_persoana'], 2); ?> €</td>
                        <td>
                            <a href="detalii_excursie.php?id=<?php echo $excursie['id']; ?>" 
                               class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>