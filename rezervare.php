<?php
require_once 'conexiune.php';

// Verifică dacă a fost transmis un ID valid
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$excursie_id = $_GET['id'];

// Query pentru a lua detaliile excursiei
$sql = "SELECT *, e.id as ex_id, e.nume as nume_ex, s.nume as nume_sezon , l.nume as nume_loc, t.nume as nume_tara
        FROM excursii e
        JOIN sezoane s ON s.id = e.sezon_id
        JOIN locatii l ON e.locatie_id = l.id
        JOIN tari t ON l.tara_id = t.id
        WHERE e.id= ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $excursie_id);
$stmt->execute();
$result = $stmt->get_result();
$excursie = $result->fetch_assoc();

if (!$excursie) {
    header('Location: index.php');
    exit;
}
?>

    <!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caută excursie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .carousel-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .carousel-indicators {
            margin-bottom: 0;
        }
        
        .carousel {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .rezervare-card {
            position: sticky;
            top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .form-control-lg, .form-select-lg {
            height: 50px;
        }

        .btn-rezervare {
            height: 50px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>    

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Carusel poze -->
                <div id="carouselExcursie" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php if (!empty($excursie['poza1'])): ?>
                            <button type="button" data-bs-target="#carouselExcursie" data-bs-slide-to="0" class="active"></button>
                        <?php endif; ?>
                        <?php if (!empty($excursie['poza2'])): ?>
                            <button type="button" data-bs-target="#carouselExcursie" data-bs-slide-to="1"></button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="carousel-inner">
                        <?php if (!empty($excursie['poza1'])): ?>
                        <div class="carousel-item active">
                            <img src="uploads/<?php echo htmlspecialchars($excursie['poza1']); ?>" 
                                 class="d-block carousel-image" 
                                 alt="Prima imagine">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($excursie['poza2'])): ?>
                        <div class="carousel-item">
                            <img src="uploads/<?php echo htmlspecialchars($excursie['poza2']); ?>" 
                                 class="d-block carousel-image" 
                                 alt="A doua imagine">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($excursie['poza1']) && !empty($excursie['poza2'])): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExcursie" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExcursie" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Detalii excursie -->
                <!-- Titlu excursie -->
                <h2 class="mb-4"><?php echo htmlspecialchars($excursie['nume_ex']); ?></h2>

                <!-- Detalii excursie -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-sun"></i> <strong> Tip excursie: </strong> <?php echo htmlspecialchars($excursie['tip']); ?></p>
                        <p class="mb-1"><i class="bi bi-calendar"></i> <strong> Sezon: </strong> <?php echo htmlspecialchars($excursie['nume_sezon']); ?></p>
                        <p class="mb-1"><i class="bi bi-globe"></i> <strong> Țară: </strong> <?php echo htmlspecialchars($excursie['nume_tara']); ?></p>
                        <p class="mb-1"><i class="bi bi-geo-alt"></i> <strong> Locație: </strong> <?php echo htmlspecialchars($excursie['nume_loc']); ?></p>
                        <p class="mb-1"><i class="bi bi-moon"></i> <strong> Numar nopti: </strong> <?php echo $excursie['numar_nopti']; ?></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-calendar-event"></i> <strong> Data plecării: </strong> 
                            <?php echo date('d.m.Y', strtotime($excursie['data_inceput'])); ?>
                        </p>
                        <p class="mb-1"><i class="bi bi-calendar-event"></i> <strong> Data întoarcerii: </strong> 
                            <?php echo date('d.m.Y', strtotime($excursie['data_sfarsit'])); ?>
                        </p>
                        <p class="mb-1"><i class="bi bi-currency-euro"></i> <strong> Preț: </strong> <?php echo number_format($excursie['pret_cazare_per_persoana'], 2); ?> EUR</p>
                        <p class="mb-1"><i class="bi bi-hypnotize"></i> <strong> Masă: </strong> <?php echo htmlspecialchars($excursie['tip_masa']); ?></p>
                        <p class="mb-1">
                            <i class="bi bi-gift"></i> 
                            <strong> Oferta specială: </strong> 
                            <?php echo !empty($excursie['oferta_speciala']) ? htmlspecialchars($excursie['oferta_speciala']) : "Fără"; ?>
                        </p>
                    </div>
                </div>
                <h2>Descriere</h2>
                <p><?php echo htmlspecialchars($excursie['descriere']); ?></p>
                <?php 
                    if (isset($excursie["tip"]) && strtolower($excursie["tip"]) == "circuit") { 
                        // Verificăm dacă excursie_id este setat și valid
                        if (isset($excursie_id) && !empty($excursie_id)) {
                            $sqlCircuit = "SELECT * FROM circuite WHERE excursie_id = ?";
                            $stmt = $conn->prepare($sqlCircuit);
                            
                            if ($stmt) {
                                $stmt->bind_param("i", $excursie_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result && $circuit = $result->fetch_assoc()) {
                                    echo "<h2>Detalii circuit</h2>";
                                    echo "<p><strong>Traseu: </strong>" . htmlspecialchars($circuit['descriere_traseu']) . "</p>";
                                    echo "<p>strong>Vizite incluse:</strong>" . htmlspecialchars($circuit['vizite_incluse']) . "</p>";
                                } else {
                                    echo "<p>Nu au fost găsite detalii pentru acest circuit.</p>";
                                }

                                $stmt->close();
                            } else {
                                echo "<p>Eroare la pregătirea interogării pentru detalii circuit.</p>";
                            }
                        } else {
                            echo "<p>ID-ul excursiei nu este valid.</p>";
                        }
                    } else if (isset($excursie["tip"]) && strtolower($excursie["tip"]) == "sejur") {
                        // Verificăm dacă excursie_id este setat și valid
                        if (isset($excursie_id) && !empty($excursie_id)) {
                            $sqlSejur = "SELECT * FROM sejururi WHERE excursie_id = ?";
                            $stmt = $conn->prepare($sqlSejur);
                            
                            if ($stmt) {
                                $stmt->bind_param("i", $excursie_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result && $sejur = $result->fetch_assoc()) {
                                    echo "<h2>Detalii sejur</h2>";
                                    echo "<p><strong>Hotel: </strong>" . htmlspecialchars($sejur['rating_hotel']) . " <i class=\"bi bi-star-fill\"></i></p>";
                                    echo "<p><strong>Tip cameră: </strong>" . htmlspecialchars($sejur['tip_camera']) . "</p>";
                                    echo "<p><strong>Facilități: </strong>" . htmlspecialchars($sejur['facilitati_hotel']) . "</p>";
                                } else {
                                    echo "<p>Nu au fost găsite detalii pentru acest sejur.</p>";
                                }

                                $stmt->close();
                            } else {
                                echo "<p>Eroare la pregătirea interogării pentru detalii sejur.</p>";
                            }
                        } else {
                            echo "<p>ID-ul excursiei nu este valid.</p>";
                        }
                    }
                    else if (isset($excursie["tip"]) && strtolower($excursie["tip"]) == "croaziera") {
                        // Verificăm dacă excursie_id este setat și valid
                        if (isset($excursie_id) && !empty($excursie_id)) {
                            $sqlCroaziera = "SELECT * FROM croaziere WHERE excursie_id = ?";
                            $stmt = $conn->prepare($sqlCroaziera);
                            $stmt->bind_param("i", $excursie_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && $croaziera = $result->fetch_assoc()) {
                                echo "<h2>Detalii croazieră</h2>";
                                echo "<p><strong> Categoria: </strong>" . htmlspecialchars($croaziera['categorie_nava']) . "</p>";
                                echo "<p><strong> Facilitati vas: </strong>" . htmlspecialchars($croaziera['facilitati_vas']) . "</p>";
                                echo "<p><strong> Activitati bord: </strong>" . htmlspecialchars($croaziera['activitati_bord']) . "</p>";
                                echo "<p><strong> Descriere traseu: </strong>" . htmlspecialchars($croaziera['descriere_traseu']) . "</p>";
                                echo "<p><strong> Porturi de escala: </strong>" . htmlspecialchars($croaziera['porturi_oprire']) . "</p>";
                                echo "<p><strong> Vizite incluse: </strong>"  . htmlspecialchars($croaziera['vizite_incluse']) . "</p>";
                                $stmt->close();
                            } else {
                                echo "<p>Eroare la pregătirea interogării pentru detalii croaziera.</p>";
                            }
                        } else {
                            echo "<p>ID-ul excursiei nu este valid.</p>";
                        }
                    }
                ?>
                
            </div>

            <!-- Formular rezervare -->
            <div class="col-md-4">
                <div class="card rezervare-card">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Pasul 1: Detalii rezervare</h4>
                        
                        <!-- Adăugăm mesajul informativ despre reducere -->
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i> 
                            Clienții cu status "Client Top" beneficiază de o reducere de 2% la prețul total al rezervării.
                            Reducerea se aplică o singură dată per rezervare, indiferent de numărul de clienți top participanți.
                        </div>
                        
                        <form action="participanti.php" method="POST">
                            <input type="hidden" name="excursie_id" value="<?php echo $excursie_id; ?>">
                            
                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="form-label">Număr adulți</label>
                                    <input type="number" class="form-control form-control-lg" name="adulti" value="1" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Număr copii</label>
                                    <input type="number" class="form-control form-control-lg" name="copii" value="0" min="0">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Opțiune transport</label>
                                <select class="form-select form-select-lg" name="transport_id" required>
                                    <option value="">Alegeți opțiunea de transport</option>
                                    <?php
                                    $sql_transport = "SELECT * FROM optiuni_transport_excursii WHERE excursie_id = ?";
                                    $stmt = $conn->prepare($sql_transport);
                                    $stmt->bind_param("i", $excursie_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while($transport = $result->fetch_assoc()) {
                                        echo "<option value='" . $transport['id'] . "'>" . 
                                             htmlspecialchars($transport['tip_transport']) . 
                                             " - " . number_format($transport['pret_per_persoana'], 2) . 
                                             " EUR</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Tip plată</label>
                                <select class="form-select form-select-lg" name="tip_plata" required>
                                    <option value="integral">Integral (reducere 5%)</option>
                                    <option value="avans">Avans 20%</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-rezervare">
                                Continuă cu datele participanților
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
