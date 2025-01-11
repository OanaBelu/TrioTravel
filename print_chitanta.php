<?php
require_once 'conexiune.php';

if (!isset($_GET['id'])) {
    die('ID rezervare lipsă');
}

$rezervare_id = $_GET['id'];

// Mai întâi găsim chitanța asociată rezervării
$sql_chitanta = "SELECT id FROM chitante WHERE rezervare_id = ? AND tip_operatie = 'plata' LIMIT 1";
$stmt = $conn->prepare($sql_chitanta);
$stmt->bind_param("i", $rezervare_id);
$stmt->execute();
$result_chitanta = $stmt->get_result();
$chitanta = $result_chitanta->fetch_assoc();
$chitanta_id = $chitanta['id'];

// Preluăm toate datele necesare pentru chitanță
$sql = "SELECT r.*, 
        e.nume as nume_excursie, 
        e.pret_cazare_per_persoana,
        e.data_inceput, 
        e.data_sfarsit,
        COALESCE(
            (SELECT CONCAT(p.prenume, ' ', p.nume)
             FROM participanti p 
             WHERE p.rezervare_id = r.id 
             ORDER BY p.id ASC
             LIMIT 1),
            CONCAT(c.prenume, ' ', c.nume)
        ) as nume_client,
        c.email,
        c.telefon,
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

// Calculăm totalurile
$pret_cazare_adult = $rezervare['pret_cazare_per_persoana']; // Preț de bază pentru un adult
$pret_cazare_adulti = $pret_cazare_adult * $rezervare['numar_adulti'];
$pret_cazare_copii = $rezervare['numar_copii'] > 0 ? 
    ($pret_cazare_adult * 0.5 * $rezervare['numar_copii']) : 0; // 50% reducere pentru copii

// Calculăm prețul transportului
$pret_transport = 0;
if ($rezervare['transport_id']) {
    $pret_transport = $rezervare['pret_transport_per_persoana'] * 
        ($rezervare['numar_adulti'] + $rezervare['numar_copii']);
}

// Inițializăm variabilele pentru reduceri
$reducere_plata_integrala = 0;
$reducere_client_top = 0;

// Calculăm subtotalul
$subtotal = $pret_cazare_adulti + $pret_cazare_copii + $pret_transport;

// Aplicăm reducerea de client top (2%) indiferent de tipul plății
if ($rezervare['este_client_top']) {
    $reducere_client_top = $subtotal * 0.02;
    $suma_dupa_reducere_top = $subtotal - $reducere_client_top;
} else {
    $suma_dupa_reducere_top = $subtotal;
}

// Apoi aplicăm reducerea pentru plata integrală (5%) sau calculăm avansul
if ($rezervare['status_plata'] == 'integral') {
    $reducere_plata_integrala = $suma_dupa_reducere_top * 0.05;
    $total_final = $suma_dupa_reducere_top - $reducere_plata_integrala;
} else {
    // Pentru avans - aplicăm 20% la suma după reducerea de client top
    $total_final = $suma_dupa_reducere_top * 0.20;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Chitanță</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .detalii-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .logo {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .chitanta-header {
            text-align: center;
            margin-bottom: 40px;
        }
        @media print {
            .btn {
                display: none;
            }
            .detalii-container {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="detalii-container">
        <!-- Header Chitanță -->
        <div class="chitanta-header">
            <img src="images/logo.png" alt="TrioTravel Logo" class="logo">
            <h2>CHITANȚĂ</h2>
            <p>Număr: #<?php echo $chitanta_id; ?></p>
            <p>Data: <?php echo date('d.m.Y'); ?></p>
        </div>

        <!-- Detalii Client -->
        <div class="mb-4">
            <h4>Client:</h4>
            <p>Nume: <?php echo htmlspecialchars($rezervare['nume_client']); ?></p>
            <p>Email: <?php echo htmlspecialchars($rezervare['email']); ?></p>
            <p>Telefon: <?php echo htmlspecialchars($rezervare['telefon']); ?></p>
        </div>

        <!-- Detalii Excursie -->
        <div class="mb-4">
            <h4>Detalii Excursie:</h4>
            <p>Excursie: <?php echo htmlspecialchars($rezervare['nume_excursie']); ?></p>
            <p>Perioada: <?php echo date('d.m.Y', strtotime($rezervare['data_inceput'])) . ' - ' . 
                              date('d.m.Y', strtotime($rezervare['data_sfarsit'])); ?></p>
            <p>Număr persoane: <?php echo $rezervare['numar_adulti']; ?> adulți
               <?php if ($rezervare['numar_copii'] > 0) echo ' și ' . $rezervare['numar_copii'] . ' copii'; ?></p>
        </div>

        <!-- Detalii Plată -->
        <div class="mb-4">
            <h4>Detalii Plată:</h4>
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
                
                <tr>
                    <td>Transport:</td>
                    <td class="text-end"><?php echo number_format($pret_transport, 2); ?> €</td>
                </tr>

                <tr class="table-secondary">
                    <td>Subtotal:</td>
                    <td class="text-end"><?php echo number_format($subtotal, 2); ?> €</td>
                </tr>

                <?php if ($rezervare['este_client_top']): ?>
                <tr class="text-success">
                    <td>Reducere client top (2%):</td>
                    <td class="text-end">-<?php echo number_format($reducere_client_top, 2); ?> €</td>
                </tr>
                <?php endif; ?>

                <?php if ($rezervare['status_plata'] == 'integral'): ?>
                <tr class="text-success">
                    <td>Reducere plată integrală (5%):</td>
                    <td class="text-end">-<?php echo number_format($reducere_plata_integrala, 2); ?> €</td>
                </tr>
                <?php endif; ?>

                <tr class="table-primary">
                    <td><strong>Total de plată<?php echo $rezervare['status_plata'] == 'avans' ? ' (avans 20%)' : ''; ?>:</strong></td>
                    <td class="text-end"><strong><?php echo number_format($total_final, 2); ?> €</strong></td>
                </tr>
            </table>
        </div>

        <!-- Butoane Print -->
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Tipărește
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-house"></i> Înapoi la Pagina Principală
            </a>
        </div>
    </div>
</body>
</html>