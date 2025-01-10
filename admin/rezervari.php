<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

// Adaugă această funcție la începutul fișierului, după require_once
function calculeazaPretFinalCuReduceri($pret_total, $rezervare_id, $conn) {
    // Verificăm dacă există cel puțin un client top
    $are_client_top = areClientTop($rezervare_id, $conn);
    
    // Aplicăm reducerea de 2% dacă există cel puțin un client top
    if ($are_client_top) {
        $reducere = $pret_total * 0.02;
        $pret_total = $pret_total - $reducere;
    }
    
    return $pret_total;
}

// Preluăm toate rezervările cu detalii despre excursie și client
$sql = "SELECT r.*, 
        e.nume as nume_excursie, 
        e.data_inceput, 
        e.data_sfarsit,
        r.pret_cazare,
        CONCAT(c.prenume, ' ', c.nume) as nume_client, 
        c.email,
        c.telefon,
        c.este_client_top,
        ot.tip_transport,
        ot.pret_per_persoana as pret_transport_per_persoana,
        (SELECT COALESCE(SUM(suma), 0) 
         FROM chitante 
         WHERE rezervare_id = r.id 
         AND tip_operatie = 'plata') as suma_platita,
        -- Verificăm dacă clientul era top la momentul rezervării
        (SELECT 
            COUNT(*) >= 3 OR 
            (SELECT COALESCE(SUM(ch.suma), 0)
             FROM chitante ch
             JOIN rezervari r_anterior ON ch.rezervare_id = r_anterior.id
             WHERE r_anterior.client_id = r.client_id
             AND r_anterior.data_creare < r.data_creare
             AND ch.tip_operatie = 'plata'
             AND r_anterior.status_plata != 'anulata') >= 5000
         FROM rezervari r_anterior
         WHERE r_anterior.client_id = r.client_id 
         AND r_anterior.data_creare < r.data_creare
         AND r_anterior.status_plata != 'anulata'
        ) as era_client_top
        FROM rezervari r
        LEFT JOIN excursii e ON r.excursie_id = e.id
        LEFT JOIN clienti c ON r.client_id = c.id
        LEFT JOIN optiuni_transport_excursii ot ON r.transport_id = ot.id
        ORDER BY r.data_creare DESC";
$result = $conn->query($sql);

// Funcție pentru verificarea dacă există cel puțin un client top în rezervare
function areClientTop($rezervare_id, $conn) {
    // Verificăm clientul principal
    $sql = "SELECT c.este_client_top 
            FROM rezervari r 
            JOIN clienti c ON r.client_id = c.id 
            WHERE r.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rezervare_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['este_client_top'] == 1) {
            return true;
        }
    }
    
    // Verificăm participanții
    $sql = "SELECT c.este_client_top 
            FROM participanti p 
            JOIN clienti c ON (p.nume = c.nume AND p.prenume = c.prenume 
                             AND (p.email = c.email OR p.telefon = c.telefon))
            WHERE p.rezervare_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rezervare_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['este_client_top'] == 1) {
            return true;
        }
    }
    
    return false;
}

// Funcție pentru verificarea participanților
function verificaParticipantExistent($nume, $prenume, $numar_identitate, $rezervare_id, $conn) {
    $sql = "SELECT id FROM participanti 
            WHERE numar_identitate = ? 
            AND rezervare_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $numar_identitate, $rezervare_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// În secțiunea de procesare a rezervării:
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrare Rezervări</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table th, .table td {
            padding: 0.5rem;
        }
        .table th.col-excursie {
            width: 20%;
        }
        .table th.col-client {
            width: 15%;
        }
        .table th.col-contact {
            width: 15%;
        }
        .table th.col-persoane {
            width: 10%;
        }
        .table th.col-transport {
            width: 10%;
        }
        .table th.col-pret {
            width: 10%;
        }
        .table th.col-status {
            width: 10%;
        }
        .table th.col-data {
            width: 10%;
        }
        .table td.detalii-pret {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Administrare Rezervări</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-excursie">Excursie</th>
                        <th class="col-client">Client</th>
                        <th class="col-contact">Contact</th>
                        <th class="col-persoane">Persoane</th>
                        <th class="col-transport">Transport</th>
                        <th class="col-pret">Preț Total</th>
                        <th class="col-status">Status Plată</th>
                        <th class="col-data">Data Rezervării</th>
                        <th class="col-actiuni">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rezervare = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $rezervare['id']; ?></td>
                        <td>
                            <?php echo $rezervare['nume_excursie'] ? htmlspecialchars($rezervare['nume_excursie']) : 'N/A'; ?>
                            <br>
                            <small class="text-muted">
                                <?php 
                                if ($rezervare['data_inceput'] && $rezervare['data_sfarsit']) {
                                    echo date('d.m.Y', strtotime($rezervare['data_inceput'])) . ' - ' . 
                                         date('d.m.Y', strtotime($rezervare['data_sfarsit']));
                                } else {
                                    echo 'Perioada nedefinită';
                                }
                                ?>
                            </small>
                        </td>
                        <td><?php echo htmlspecialchars($rezervare['nume_client']); ?></td>
                        <td>
                            <small>
                                <?php echo htmlspecialchars($rezervare['email']); ?><br>
                                <?php echo htmlspecialchars($rezervare['telefon']); ?>
                            </small>
                        </td>
                        <td>
                            <?php 
                            echo "Adulți: " . $rezervare['numar_adulti'] . "<br>";
                            if ($rezervare['numar_copii'] > 0) {
                                echo "Copii: " . $rezervare['numar_copii'];
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($rezervare['transport_id']) {
                                echo htmlspecialchars($rezervare['tip_transport']) . '<br>';
                                echo '<small class="text-muted">' . number_format($rezervare['pret_transport_per_persoana'], 2) . ' €</small>';
                            } else {
                                echo '<span class="text-muted">Transport propriu</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $pret_total = $rezervare['pret_total'];
                            $pret_cazare_adulti = $rezervare['pret_cazare'] * $rezervare['numar_adulti'];
                            $pret_cazare_copii = $rezervare['pret_cazare'] * $rezervare['numar_copii'] * 0.5;
                            $pret_transport = $rezervare['pret_transport_per_persoana'] * 
                                ($rezervare['numar_adulti'] + $rezervare['numar_copii']);
                            $subtotal = $pret_cazare_adulti + $pret_cazare_copii + $pret_transport;
                            ?>
                            <?php echo number_format($pret_total, 2); ?> €
                            <br>
                            <small class="text-muted">
                                Cazare: <?php echo number_format($pret_cazare_adulti + $pret_cazare_copii, 2); ?> €
                                <?php if ($rezervare['pret_transport_per_persoana'] > 0): ?>
                                    <br>Transport: <?php echo number_format($pret_transport, 2); ?> €
                                <?php endif; ?>
                                
                                <?php if ($rezervare['status_plata'] == 'integral'): ?>
                                    <br>
                                    <?php
                                    // Calculăm numărul de rezervări anterioare pentru acest client
                                    $sql_anterior = "SELECT COUNT(*) as numar_rezervari
                                        FROM rezervari 
                                        WHERE client_id = ? 
                                        AND data_creare < ? 
                                        AND status_plata != 'anulata'";
                                    $stmt = $conn->prepare($sql_anterior);
                                    $stmt->bind_param("is", $rezervare['client_id'], $rezervare['data_creare']);
                                    $stmt->execute();
                                    $result_anterior = $stmt->get_result();
                                    $rezervari_anterioare = $result_anterior->fetch_assoc()['numar_rezervari'];
                                    ?>
                                    
                                    <span class="text-success">Reducere plată integrală: -5%</span>
                                    <?php if ($rezervari_anterioare >= 2): // A treia rezervare sau mai mult ?>
                                        <br><span class="text-success">Reducere client top: -2%</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $rezervare['status_plata'] == 'integral' ? 'success' : 
                                    ($rezervare['status_plata'] == 'avans' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($rezervare['status_plata']); ?>
                            </span>
                            <?php if ($rezervare['status_plata'] == 'avans'): ?>
                                <br>
                                <small class="text-muted">
                                    Plătit: <?php echo number_format($rezervare['suma_plata'], 2); ?> €
                                </small>
                            <?php elseif ($rezervare['status_plata'] == 'integral'): ?>
                                <br>
                                <small class="text-muted">
                                    Plătit: <?php echo number_format($rezervare['pret_total'], 2); ?> €
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('d.m.Y H:i', strtotime($rezervare['data_creare'])); ?>
                        </td>
                        <td>
                            <?php if ($rezervare['status_plata'] == 'avans'): ?>
                                <a href="aplica_plata.php?id=<?php echo $rezervare['id']; ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="Aplică plata integrală">
                                    <i class="bi bi-check2-circle"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($rezervare['status_plata'] != 'platit'): ?>
                                <button onclick="if(confirm('Sigur doriți să anulați această rezervare?')) 
                                       window.location='anuleaza_rezervare.php?id=<?php echo $rezervare['id']; ?>'" 
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            <?php endif; ?>
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
