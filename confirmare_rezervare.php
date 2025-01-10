<?php
require_once 'conexiune.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$rezervare_id = $_GET['id'];

// Query simplu pentru detaliile rezervării
$sql = "SELECT r.*, 
        e.nume as nume_excursie, 
        e.data_inceput, 
        e.data_sfarsit,
        CONCAT(c.prenume, ' ', c.nume) as nume_client,
        c.email,
        c.este_client_top,
        ot.tip_transport,
        ot.pret_per_persoana as pret_transport_per_persoana
        FROM rezervari r
        LEFT JOIN excursii e ON r.excursie_id = e.id
        LEFT JOIN clienti c ON r.client_id = c.id
        LEFT JOIN optiuni_transport_excursii ot ON r.transport_id = ot.id
        WHERE r.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rezervare_id);
$stmt->execute();
$rezervare = $stmt->get_result()->fetch_assoc();

// Calculele pentru prețuri
$pret_cazare_adulti = $rezervare['pret_cazare'] * $rezervare['numar_adulti'];
$pret_cazare_copii = $rezervare['numar_copii'] > 0 ? $rezervare['pret_cazare'] * $rezervare['numar_copii'] * 0.5 : 0;
$pret_transport = $rezervare['pret_transport'];
$subtotal = $pret_cazare_adulti + $pret_cazare_copii + $pret_transport;

// Calculăm reducerile
$reducere_plata_integrala = $rezervare['status_plata'] == 'integral' ? $subtotal * 0.05 : 0;
$reducere_client_fidel = $rezervare['este_client_top'] ? $subtotal * 0.02 : 0;

// Total final după reduceri
$total_final = $subtotal - $reducere_plata_integrala - $reducere_client_fidel;

// Preluăm participanții
$sql_participanti = "SELECT nume, prenume, tip_participant 
                    FROM participanti 
                    WHERE rezervare_id = ?
                    ORDER BY tip_participant, id";
$stmt = $conn->prepare($sql_participanti);
$stmt->bind_param("i", $rezervare_id);
$stmt->execute();
$participanti = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Confirmare Rezervare - TrioTravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center text-success mb-3">Rezervare Confirmată!</h1>
        
        <div class="text-center mb-2">
            <h5>Număr rezervare: #<?php echo $rezervare_id; ?></h5>
            <p><strong>Client:</strong> <?php echo htmlspecialchars($rezervare['nume_client']); ?></p>
        </div>

        <hr>

        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Detalii Excursie</h4>
                <p><strong>Excursie:</strong> <?php echo htmlspecialchars($rezervare['nume_excursie']); ?></p>
                <p><strong>Perioada:</strong> <?php echo date('d.m.Y', strtotime($rezervare['data_inceput'])) . ' - ' . 
                                                  date('d.m.Y', strtotime($rezervare['data_sfarsit'])); ?></p>
                <p><strong>Transport:</strong> <?php echo $rezervare['tip_transport'] ?? 'Transport propriu'; ?></p>
                
                <h5 class="mt-3">Participanți:</h5>
                <ul>
                <?php foreach ($participanti as $participant): ?>
                    <li>
                        <?php echo htmlspecialchars($participant['nume'] . ' ' . $participant['prenume']); ?>
                        <small class="text-muted">(<?php echo $participant['tip_participant']; ?>)</small>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h4>Detalii Plată</h4>
                <table class="table table-borderless">
                    <tr>
                        <td>Cazare adulți (<?php echo $rezervare['numar_adulti']; ?> persoane):</td>
                        <td class="text-end"><?php echo number_format($pret_cazare_adulti, 2); ?> €</td>
                    </tr>
                    
                    <?php if ($rezervare['numar_copii'] > 0): ?>
                    <tr>
                        <td>Cazare copii (<?php echo $rezervare['numar_copii']; ?> copii):</td>
                        <td class="text-end"><?php echo number_format($pret_cazare_copii, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if ($pret_transport > 0): ?>
                    <tr>
                        <td>Transport:</td>
                        <td class="text-end"><?php echo number_format($pret_transport, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>

                    <tr class="table-secondary">
                        <td>Subtotal:</td>
                        <td class="text-end"><?php echo number_format($subtotal, 2); ?> €</td>
                    </tr>

                    <?php if ($reducere_plata_integrala > 0): ?>
                    <tr class="text-success">
                        <td>Reducere plată integrală (5%):</td>
                        <td class="text-end">-<?php echo number_format($reducere_plata_integrala, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>

                    <?php if ($reducere_client_fidel > 0): ?>
                    <tr class="text-success">
                        <td>Reducere client fidel (2%):</td>
                        <td class="text-end">-<?php echo number_format($reducere_client_fidel, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>

                    <tr class="table-primary fw-bold">
                        <?php if ($rezervare['status_plata'] == 'avans'): ?>
                            <td>Avans de plată (20%):</td>
                            <td class="text-end"><?php echo number_format($rezervare['suma_plata'], 2); ?> €</td>
                        <?php else: ?>
                            <td>Total de plată:</td>
                            <td class="text-end"><?php echo number_format($rezervare['suma_plata'], 2); ?> €</td>
                        <?php endif; ?>
                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="print_chitanta.php?id=<?php echo $rezervare_id; ?>" 
               class="btn btn-primary">
                <i class="bi bi-printer"></i> Tipărește Chitanța
            </a>
            
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-house"></i> Înapoi la Pagina Principală
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>