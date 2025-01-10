<?php
require_once 'conexiune.php';

// Definim categoriile la plural, cum vrem să apară pe UI
$categorii = ['Sejururi', 'Circuite', 'Croaziere'];

// Modificăm query-ul pentru a prelua toate excursiile
$sql = "SELECT e.*, s.nume as nume_sezon 
        FROM excursii e
        LEFT JOIN sezoane s ON e.sezon_id = s.id
        ORDER BY e.nume";
$result = $conn->query($sql);

// Grupăm excursiile după tip, convertind tipul pentru grupare
$excursii_grupate = [];
while ($excursie = $result->fetch_assoc()) {
    $tip = strtolower($excursie['tip']);
    if ($tip === 'sejur') {
        $excursii_grupate['Sejururi'][] = $excursie;
    } elseif ($tip === 'circuit') {
        $excursii_grupate['Circuite'][] = $excursie;
    } elseif ($tip === 'croaziera') {
        $excursii_grupate['Croaziere'][] = $excursie;
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acasă</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Oferte disponibile</h1>

        <?php foreach ($categorii as $categorie): ?>
            <h3 class="mt-4 mb-3"><?php echo $categorie; ?></h3>
            
            <?php if (isset($excursii_grupate[$categorie]) && !empty($excursii_grupate[$categorie])): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($excursii_grupate[$categorie] as $excursie): ?>
                        <div class="col">
                            <div class="card h-100">
                                <?php if ($excursie['poza1']): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($excursie['nume']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($excursie['nume']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        <?php echo ucfirst($excursie['tip']); ?>
                                        <?php if (!empty($excursie['nume_sezon'])): ?>
                                            | <?php echo htmlspecialchars($excursie['nume_sezon']); ?>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="card-text">
                                        <?php echo nl2br(htmlspecialchars(substr($excursie['descriere'], 0, 150))); ?>...
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <?php 
                                            echo date('d.m.Y', strtotime($excursie['data_inceput'])) . ' - ' . 
                                                 date('d.m.Y', strtotime($excursie['data_sfarsit']));
                                            ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <strong>Preț:</strong> 
                                        <?php echo number_format($excursie['pret_cazare_per_persoana'], 2); ?> EUR
                                    </p>
                                    <a href="rezervare.php?id=<?php echo $excursie['id']; ?>" 
                                       class="btn btn-primary">Vezi detalii</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Nu există excursii în această categorie.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <hr>
                    <a href="admin_login.php" class="text-muted text-decoration-none">
                        <small><i class="bi bi-shield-lock"></i> Administrator</small>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

