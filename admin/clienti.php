<?php

ini_set('display_errors', 1); // Activează afișarea erorilor
ini_set('display_startup_errors', 1); // Activează afișarea erorilor la pornirea PHP
error_reporting(E_ALL); // Rapoartează toate erorile (notițe, avertismente și erori fatale)


require_once 'check_auth.php';
require_once '../conexiune.php';

// Modificăm query-ul pentru a obține informații precise despre clienți
$sql = "SELECT 
    c.*,
    (SELECT COUNT(DISTINCT r.id) 
     FROM rezervari r
     JOIN participanti p ON r.id = p.rezervare_id
     WHERE p.numar_identitate = c.numar_identitate 
     AND p.id = (
         SELECT MIN(p2.id) 
         FROM participanti p2 
         WHERE p2.rezervare_id = r.id
     )
    ) as numar_rezervari,
    (SELECT COALESCE(SUM(ch.suma), 0)
     FROM chitante ch
     JOIN rezervari r2 ON ch.rezervare_id = r2.id
     JOIN participanti p ON r2.id = p.rezervare_id
     WHERE p.numar_identitate = c.numar_identitate 
     AND p.id = (
         SELECT MIN(p2.id) 
         FROM participanti p2 
         WHERE p2.rezervare_id = r2.id
     )
     AND ch.tip_operatie = 'plata'
     AND r2.status_plata != 'anulata'
    ) as total_platit,
    NOT EXISTS (
        SELECT 1 
        FROM participanti p 
        WHERE p.numar_identitate = c.numar_identitate 
        AND p.id = (
            SELECT MIN(p2.id) 
            FROM participanti p2 
            WHERE p2.rezervare_id = p.rezervare_id
        )
    ) as este_doar_participant,
    (SELECT COUNT(*) >= 3 OR 
            (SELECT COALESCE(SUM(ch.suma), 0)
             FROM chitante ch
             JOIN rezervari r ON ch.rezervare_id = r.id
             JOIN participanti p ON r.id = p.rezervare_id
             WHERE p.numar_identitate = c.numar_identitate 
             AND p.id = (
                 SELECT MIN(p2.id) 
                 FROM participanti p2 
                 WHERE p2.rezervare_id = r.id
             )
             AND ch.tip_operatie = 'plata'
             AND r.status_plata != 'anulata') >= 5000
     FROM rezervari r2
     WHERE r2.client_id = c.id 
     AND r2.status_plata != 'anulata'
    ) as este_eligibil
FROM clienti c
ORDER BY c.nume, c.prenume";

$result = $conn->query($sql);

if (!$result) {
    die("Eroare query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Clienți</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Clienți</h2>
            <a href="adauga_client.php" class="btn btn-primary">+ Adaugă Client</a>
        </div>

        <!-- Tabel Clienți Activi -->
        <h4 class="mb-3">Clienți Activi</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Rezervări</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($client = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['nume'] . ' ' . $client['prenume']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['telefon']); ?></td>
                        <td>
                            <?php if ($client['numar_rezervari'] > 0): ?>
                                <?php echo $client['numar_rezervari']; ?> rezervări
                                <br>
                                <small class="text-muted"><?php echo number_format($client['total_platit'], 2); ?> EUR total</small>
                                <?php if ($client['este_eligibil']): ?>
                                    <br>
                                    <small class="text-success">Eligibil pentru reducere 2%</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Însoțitor</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $client['este_client_top'] ? 'success' : 'secondary'; ?>">
                                <?php echo $client['este_client_top'] ? 'Top' : 'Standard'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_client.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 