<?php
require_once 'conexiune.php';

$tip = isset($_GET['tip']) ? $_GET['tip'] : '';
$tara = isset($_GET['tara']) ? $_GET['tara'] : '';
$sezon_id = isset($_GET['sezon']) ? $_GET['sezon'] : '';
$data_inceput = isset($_GET['data_inceput']) ? $_GET['data_inceput'] : '';
$pret_max = isset($_GET['pret_max']) ? (float)$_GET['pret_max'] : 0;

// Query pentru a lista toate excursiile disponibile
$sql = "SELECT e.*, 
        t.nume as nume_tara,
        s.nume as nume_sezon,
        l.nume as nume_locatie
        FROM excursii e
        LEFT JOIN locatii l ON e.locatie_id = l.id
        LEFT JOIN tari t ON l.tara_id = t.id
        LEFT JOIN sezoane s ON e.sezon_id = s.id
        WHERE 1=1";

if ($tip) {
    $sql .= " AND e.tip = '" . $conn->real_escape_string($tip) . "'";
}
if ($tara) {
    $sql .= " AND t.id = " . (int)$tara;
}
if ($sezon_id) {
    $sql .= " AND e.sezon_id = " . (int)$sezon_id;
}
if ($data_inceput) {
    $sql .= " AND e.data_inceput >= '" . $conn->real_escape_string($data_inceput) . "'";
}
if ($pret_max > 0) {
    $sql .= " AND e.pret_cazare_per_persoana <= " . (float)$pret_max;
}

$sql .= " ORDER BY e.data_inceput";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excursii Disponibile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Excursii Disponibile</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tip excursie</label>
                        <select name="tip" class="form-select">
                            <option value="">Toate tipurile</option>
                            <option value="sejur" <?php echo $tip == 'sejur' ? 'selected' : ''; ?>>Sejur</option>
                            <option value="circuit" <?php echo $tip == 'circuit' ? 'selected' : ''; ?>>Circuit</option>
                            <option value="croaziera" <?php echo $tip == 'croaziera' ? 'selected' : ''; ?>>Croazieră</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Țară</label>
                        <select name="tara" class="form-select">
                            <option value="">Toate țările</option>
                            <?php
                            $query_tari = "SELECT DISTINCT t.id, t.nume 
                                          FROM tari t 
                                          JOIN locatii l ON l.tara_id = t.id 
                                          JOIN excursii e ON e.locatie_id = l.id 
                                          ORDER BY t.nume";
                            $tari = $conn->query($query_tari);
                            while ($tara_opt = $tari->fetch_assoc()) {
                                echo '<option value="' . $tara_opt['id'] . '"' . 
                                     ($tara == $tara_opt['id'] ? ' selected' : '') . '>' . 
                                     htmlspecialchars($tara_opt['nume']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sezon</label>
                        <select name="sezon" class="form-select">
                            <option value="">Toate sezoanele</option>
                            <?php
                            $query_sezoane = "SELECT DISTINCT s.id, s.nume 
                                            FROM sezoane s 
                                            JOIN excursii e ON e.sezon_id = s.id 
                                            ORDER BY s.data_inceput";
                            $sezoane = $conn->query($query_sezoane);
                            while ($sezon = $sezoane->fetch_assoc()) {
                                echo '<option value="' . $sezon['id'] . '"' . 
                                     ($sezon_id == $sezon['id'] ? ' selected' : '') . '>' . 
                                     htmlspecialchars($sezon['nume']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data început</label>
                        <input type="date" name="data_inceput" class="form-control" 
                               value="<?php echo $data_inceput ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Preț maxim</label>
                        <input type="number" name="pret_max" class="form-control" 
                               placeholder="€" min="0" step="100"
                               value="<?php echo $pret_max ?: ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Caută</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <?php while ($excursie = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card">
                    <?php if (!empty($excursie['poza1'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($excursie['nume']); ?>">
                    <?php else: ?>
                        <img src="uploads/default.jpg" class="card-img-top" alt="Imagine implicită">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($excursie['nume']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?php echo htmlspecialchars($excursie['nume_locatie'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($excursie['nume_tara'] ?? ''); ?>
                        </h6>
                        
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo ucfirst($excursie['tip']); ?> | 
                                <?php echo htmlspecialchars($excursie['nume_sezon'] ?? ''); ?>
                            </small>
                            <br>
                            <?php 
                            echo date('d.m.Y', strtotime($excursie['data_inceput'])) . ' - ' . 
                                 date('d.m.Y', strtotime($excursie['data_sfarsit']));
                            ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="price">
                                <strong><?php echo number_format($excursie['pret_cazare_per_persoana'], 2); ?> €</strong>
                                <small class="text-muted">/persoană</small>
                            </div>
                            <a href="detalii_excursie.php?id=<?php echo $excursie['id']; ?>" 
                               class="btn btn-primary">Vezi detalii</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
