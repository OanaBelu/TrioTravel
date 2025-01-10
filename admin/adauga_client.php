<?php
require_once 'check_auth.php';
require_once '../conexiune.php';

$mesaj = '';
$tip_mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = trim($_POST['nume']);
    $prenume = trim($_POST['prenume']);
    $email = trim($_POST['email']);
    $telefon = trim($_POST['telefon']);
    $numar_identitate = trim($_POST['numar_identitate']);

    // Validare
    $erori = [];
    if (empty($nume)) $erori[] = "Numele este obligatoriu";
    if (empty($prenume)) $erori[] = "Prenumele este obligatoriu";
    if (empty($email)) $erori[] = "Email-ul este obligatoriu";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erori[] = "Adresa de email nu este validă";
    }
    if (!empty($telefon) && !preg_match("/^[0-9]{10}$/", $telefon)) {
        $erori[] = "Numărul de telefon trebuie să conțină 10 cifre";
    }
    if (empty($numar_identitate)) {
        $erori[] = "Numărul de identitate este obligatoriu";
    }

    if (empty($erori)) {
        // Verificăm dacă clientul există deja
        $stmt = $conn->prepare("SELECT id FROM clienti WHERE numar_identitate = ?");
        $stmt->bind_param("s", $numar_identitate);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mesaj = "Există deja un client cu acest număr de identitate!";
            $tip_mesaj = "warning";
        } else {
            // Inserăm clientul nou
            $sql = "INSERT INTO clienti (nume, prenume, email, telefon, numar_identitate) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $nume, $prenume, $email, $telefon, $numar_identitate);
            
            if ($stmt->execute()) {
                $mesaj = "Client adăugat cu succes!";
                $tip_mesaj = "success";
                // Redirect după 2 secunde
                header("refresh:2;url=clienti.php");
            } else {
                $mesaj = "Eroare la adăugarea clientului: " . $conn->error;
                $tip_mesaj = "danger";
            }
        }
    } else {
        $mesaj = "Vă rugăm să corectați următoarele erori:<br>" . implode("<br>", $erori);
        $tip_mesaj = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adaugă Client Nou</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Adaugă Client Nou</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-<?php echo $tip_mesaj; ?>" role="alert">
                                <?php echo $mesaj; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nume" class="form-label">Nume *</label>
                                <input type="text" class="form-control" id="nume" name="nume" 
                                       value="<?php echo isset($_POST['nume']) ? htmlspecialchars($_POST['nume']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="prenume" class="form-label">Prenume *</label>
                                <input type="text" class="form-control" id="prenume" name="prenume" 
                                       value="<?php echo isset($_POST['prenume']) ? htmlspecialchars($_POST['prenume']) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="telefon" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="telefon" name="telefon" 
                                       value="<?php echo isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : ''; ?>"
                                       pattern="[0-9]{10}">
                                <div class="form-text">Format: 10 cifre (ex: 0722333444)</div>
                            </div>

                            <div class="mb-3">
                                <label for="numar_identitate" class="form-label">Număr Identitate (CI/Pașaport) *</label>
                                <input type="text" class="form-control" id="numar_identitate" name="numar_identitate" 
                                       value="<?php echo isset($_POST['numar_identitate']) ? htmlspecialchars($_POST['numar_identitate']) : ''; ?>"
                                       required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="clienti.php" class="btn btn-secondary">Înapoi la Lista Clienți</a>
                                <button type="submit" class="btn btn-primary">Adaugă Client</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 