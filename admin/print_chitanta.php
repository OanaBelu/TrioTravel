<?php
require_once '../conexiune.php';

// Verificăm ambele posibilități pentru id
if (!isset($_GET['id']) && !isset($_GET['chitanta_id'])) {
    die('ID rezervare lipsă');
}

// Luăm ID-ul rezervării din chitanță
if (isset($_GET['chitanta_id'])) {
    $sql = "SELECT rezervare_id FROM chitante WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['chitanta_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $chitanta = $result->fetch_assoc();
    $rezervare_id = $chitanta['rezervare_id'];
    $chitanta_id = $_GET['chitanta_id'];
} else {
    $rezervare_id = $_GET['id'];
    // Găsim chitanța asociată rezervării
    $sql_chitanta = "SELECT id FROM chitante WHERE rezervare_id = ? AND tip_operatie = 'plata' LIMIT 1";
    $stmt = $conn->prepare($sql_chitanta);
    $stmt->bind_param("i", $rezervare_id);
    $stmt->execute();
    $result_chitanta = $stmt->get_result();
    $chitanta = $result_chitanta->fetch_assoc();
    $chitanta_id = $chitanta['id'];
}

// Preluăm toate datele necesare pentru chitanță
$sql = "SELECT r.*, 
        e.nume as nume_excursie, 
        e.data_inceput, 
        e.data_sfarsit,
        CONCAT(c.prenume, ' ', c.nume) as nume_client,
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
$pret_cazare_adulti = $rezervare['pret_cazare'] * $rezervare['numar_adulti'];
$pret_cazare_copii = $rezervare['numar_copii'] > 0 ? 
    ($rezervare['pret_cazare'] * $rezervare['numar_copii'] * 0.5) : 0; // 50% reducere la cazare copii
$pret_transport = $rezervare['pret_transport_per_persoana'] * 
    ($rezervare['numar_adulti'] + $rezervare['numar_copii']); // transport full price pentru toți

$subtotal = $pret_cazare_adulti + $pret_cazare_copii + $pret_transport;

// Verificăm dacă clientul era client top la momentul rezervării
$sql_verificare = "SELECT COUNT(*) as rezervari_anterioare 
                  FROM rezervari 
                  WHERE client_id = ? 
                  AND data_creare < ? 
                  AND status_plata != 'anulata'";
$stmt = $conn->prepare($sql_verificare);
$stmt->bind_param("is", $rezervare['client_id'], $rezervare['data_creare']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$era_client_top = ($row['rezervari_anterioare'] >= 2);

// Calculăm reducerile
$suma_intermediara = $subtotal;
$reducere_client_top = 0;
$reducere_plata_integrala = 0;

// Mai întâi calculăm reducerea de client top
if ($era_client_top) {  // Folosim era_client_top în loc de este_client_top
    $reducere_client_top = $subtotal * 0.02;
    $suma_intermediara = $subtotal - $reducere_client_top;
}

// Apoi calculăm reducerea pentru plata integrală sau avans
if ($rezervare['status_plata'] == 'integral') {
    $reducere_plata_integrala = $suma_intermediara * 0.05;
    $suma_intermediara -= $reducere_plata_integrala;
} elseif ($rezervare['status_plata'] == 'avans') {
    $suma_intermediara = $suma_intermediara * 0.2;
}

// Total final
$total_final = $suma_intermediara;
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
            <img src="../images/logo.png" alt="TrioTravel Logo" class="logo">
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
                    <td>Transport (<?php echo $rezervare['numar_adulti'] + $rezervare['numar_copii']; ?> persoane):</td>
                    <?php if ($rezervare['pret_transport_per_persoana'] > 0): ?>
                        <td class="text-end"><?php echo number_format($pret_transport, 2); ?> €</td>
                    <?php else: ?>
                        <td class="text-end">Transport personal</td>
                    <?php endif; ?>
                </tr>

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

                <?php if ($reducere_client_top > 0): ?>
                <tr class="text-success">
                    <td>Reducere client top (2%):</td>
                    <td class="text-end">-<?php echo number_format($reducere_client_top, 2); ?> €</td>
                </tr>
                <?php endif; ?>

                <tr class="table-primary fw-bold">
                    <?php if ($rezervare['status_plata'] == 'avans'): ?>
                        <td>Total de plată (avans 20%):</td>
                        <td class="text-end"><?php echo number_format($rezervare['suma_plata'], 2); ?> €</td>
                    <?php else: ?>
                        <td>Total de plată:</td>
                        <td class="text-end"><?php echo number_format($rezervare['suma_plata'], 2); ?> €</td>
                    <?php endif; ?>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5">
            <p>Vă mulțumim pentru rezervare!</p>
            <p><small>TrioTravel - Agenție de Turism</small></p>
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