<?php
session_start();
require_once 'conexiune.php';

// Salvăm datele din primul formular în sesiune
$_SESSION['rezervare'] = $_POST;

$adulti = $_POST['adulti'];
$copii = $_POST['copii'];
$total_participanti = $adulti + $copii;
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Participanți</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Date Participanți</h1>
        
        <form action="procesare_rezervare.php" method="POST">
            <!-- Primul adult - va fi titularul rezervării -->
            <div class='card mb-4'>
                <div class='card-body'>
                    <h5 class='card-title'>Adult 1 (Titular Rezervare)</h5>
                    <div class='row'>
                        <div class='col-md-6 mb-3'>
                            <label class='form-label'>Nume</label>
                            <input type='text' class='form-control' name='adult[1][nume]' required>
                        </div>
                        <div class='col-md-6 mb-3'>
                            <label class='form-label'>Prenume</label>
                            <input type='text' class='form-control' name='adult[1][prenume]' required>
                        </div>
                        <div class='col-md-6 mb-3'>
                            <label class='form-label'>Email</label>
                            <input type='email' class='form-control' name='adult[1][email]' required>
                        </div>
                        <div class='col-md-6 mb-3'>
                            <label class='form-label'>Telefon</label>
                            <input type='tel' class='form-control' name='adult[1][telefon]' required>
                        </div>
                        <div class='col-12 mb-3'>
                            <label class='form-label'>CNP sau Serie Buletin</label>
                            <input type='text' class='form-control' name='adult[1][numar_identitate]' required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restul adulților -->
            <?php
            for ($i = 2; $i <= $adulti; $i++) {
                echo "<div class='card mb-4'>
                        <div class='card-body'>
                            <h5 class='card-title'>Adult $i</h5>
                            <div class='row'>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Nume</label>
                                    <input type='text' class='form-control' name='adult[$i][nume]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Prenume</label>
                                    <input type='text' class='form-control' name='adult[$i][prenume]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Email</label>
                                    <input type='email' class='form-control' name='adult[$i][email]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Telefon</label>
                                    <input type='tel' class='form-control' name='adult[$i][telefon]' required>
                                </div>
                                <div class='col-12 mb-3'>
                                    <label class='form-label'>CNP sau Serie Buletin</label>
                                    <input type='text' class='form-control' name='adult[$i][numar_identitate]' required>
                                </div>
                            </div>
                        </div>
                    </div>";
            }

            // Formular pentru copii
            for ($i = 1; $i <= $copii; $i++) {
                echo "<div class='card mb-4'>
                        <div class='card-body'>
                            <h5 class='card-title'>Copil $i</h5>
                            <div class='row'>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Nume</label>
                                    <input type='text' class='form-control' name='copil[$i][nume]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Prenume</label>
                                    <input type='text' class='form-control' name='copil[$i][prenume]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Email Părinte</label>
                                    <input type='email' class='form-control' name='copil[$i][email]' required>
                                </div>
                                <div class='col-md-6 mb-3'>
                                    <label class='form-label'>Telefon Părinte</label>
                                    <input type='tel' class='form-control' name='copil[$i][telefon]' required>
                                </div>
                                <div class='col-12 mb-3'>
                                    <label class='form-label'>CNP</label>
                                    <input type='text' class='form-control' name='copil[$i][numar_identitate]' required>
                                </div>
                            </div>
                        </div>
                    </div>";
            }
            ?>
            
            <button type="submit" class="btn btn-primary">Finalizează Rezervarea</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
