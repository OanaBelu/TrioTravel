<?php
require_once 'conexiune.php';

// Inițializare variabile pentru filtre
$tip = isset($_GET['tip']) ? $_GET['tip'] : '';
$sezon_id = isset($_GET['sezon_id']) ? $_GET['sezon_id'] : '';
$oferta_speciala = isset($_GET['oferta_speciala']) ? $_GET['oferta_speciala'] : '';
$tara = isset($_GET['tara']) ? $_GET['tara'] : '';
$tip_cazare = isset($_GET['tip_cazare']) ? $_GET['tip_cazare'] : '';
$transport = isset($_GET['transport']) ? $_GET['transport'] : '';

// Construire query de bază
$sql = "SELECT DISTINCT e.*, s.nume as nume_sezon 
        FROM excursii e 
        LEFT JOIN sezoane s ON e.sezon_id = s.id
        LEFT JOIN optiuni_transport_excursii ot ON e.id = ot.excursie_id
        WHERE 1=1";

// Adăugare condiții pentru filtre
$params = array();
$types = "";

if (!empty($tip)) {
    $sql .= " AND e.tip = ?";
    $params[] = $tip;
    $types .= "s";
}

if (!empty($sezon_id)) {
    $sql .= " AND e.sezon_id = ?";
    $params[] = $sezon_id;
    $types .= "i";
}

if (!empty($oferta_speciala)) {
    $sql .= " AND e.oferta_speciala IS NOT NULL";
}

if (!empty($tara)) {
    $sql .= " AND e.tara LIKE ?";
    $params[] = "%$tara%";
    $types .= "s";
}

if (!empty($tip_cazare)) {
    $sql .= " AND e.tip_cazare = ?";
    $params[] = $tip_cazare;
    $types .= "s";
}

if (!empty($transport)) {
    $sql .= " AND ot.tip_transport = ?";
    $params[] = $transport;
    $types .= "s";
}

$sql .= " ORDER BY e.data_inceput ASC";

// Pregătire și executare query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listă Excursii - TrioTravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            /* Ascunde elementele care nu trebuie printate */
            .filters, .navbar, .btn-primary, .no-print {
                display: none !important;
            }

            /* Header raport */
            .print-header {
                display: flex !important;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #333;
                padding-bottom: 10px;
            }

            .print-header img {
                height: 40px;
                margin-right: 15px;
            }

            .print-header h1 {
                margin: 0;
                font-size: 20px;
            }

            /* Format listă excursii pentru print */
            .excursie-item {
                display: flex !important;
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .excursie-imagine {
                width: 120px;
                height: 80px;
                object-fit: cover;
                margin-right: 15px;
            }

            .excursie-detalii {
                flex-grow: 1;
            }

            .excursie-pret {
                width: 150px;
                text-align: right;
                font-weight: bold;
            }

            /* Formatare pagină */
            @page {
                margin: 1.5cm;
            }

            .print-date {
                text-align: right;
                margin-bottom: 15px;
                font-size: 12px;
            }
        }

        /* Stil normal pentru pagină */
        .excursie-item {
            display: flex;
            align-items: start;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .excursie-imagine {
            width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Adaugă header-ul pentru printare (ascuns în mod normal) -->
    <div class="d-none print-header">
        <img src="images/logo.png" alt="TrioTravel Logo">
        <div>
            <h1>TrioTravel - Listă Excursii</h1>
            <div class="print-date">Data: <?php echo date('d.m.Y'); ?></div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Buton printare și titlu -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h3>Rezultate căutare</h3>
            <button onclick="window.print();" class="btn btn-secondary">
                <i class="bi bi-printer"></i> Printează lista
            </button>
        </div>

        <!-- Filtre -->
        <div class="filters mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Tip excursie</label>
                    <select name="tip" class="form-select">
                        <option value="">Toate tipurile</option>
                        <option value="Sejur" <?php echo $tip == 'Sejur' ? 'selected' : ''; ?>>Sejur</option>
                        <option value="Circuit" <?php echo $tip == 'Circuit' ? 'selected' : ''; ?>>Circuit</option>
                        <option value="Croaziera" <?php echo $tip == 'Croaziera' ? 'selected' : ''; ?>>Croazieră</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sezon</label>
                    <select name="sezon_id" class="form-select">
                        <option value="">Toate sezoanele</option>
                        <?php
                        $sezoane = $conn->query("SELECT * FROM sezoane");
                        while ($sezon = $sezoane->fetch_assoc()) {
                            $selected = $sezon_id == $sezon['id'] ? 'selected' : '';
                            echo "<option value='{$sezon['id']}' {$selected}>{$sezon['nume']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Țară</label>
                    <input type="text" name="tara" class="form-control" value="<?php echo htmlspecialchars($tara); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Oferte speciale</label>
                    <select name="oferta_speciala" class="form-select">
                        <option value="">Toate ofertele</option>
                        <option value="1" <?php echo $oferta_speciala ? 'selected' : ''; ?>>Doar oferte speciale</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrează</button>
                </div>
            </form>
        </div>

        <!-- Lista excursii -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($excursie = $result->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 position-relative">
                    <?php if (!empty($excursie['oferta_speciala'])): ?>
                    <div class="badge bg-danger badge-oferta-speciala">Ofertă Specială</div>
                    <?php endif; ?>

                    <?php if (!empty($excursie['poza1'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($excursie['nume']); ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($excursie['nume']); ?></h5>
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo htmlspecialchars($excursie['tip']); ?> | 
                                <?php echo date('d.m.Y', strtotime($excursie['data_inceput'])); ?> - 
                                <?php echo date('d.m.Y', strtotime($excursie['data_sfarsit'])); ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <strong>Preț:</strong> <?php echo number_format($excursie['pret_cazare_per_persoana'], 2); ?> EUR
                        </p>
                        <a href="rezervare.php?id=<?php echo $excursie['id']; ?>" class="btn btn-primary no-print">Vezi detalii</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 