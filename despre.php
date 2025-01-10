<?php
require_once 'conexiune.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despre Noi - TrioTravel</title>
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

    <div class="container mt-5">
        <h1 class="text-center mb-4">Despre TrioTravel</h1>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cine suntem noi?</h5>
                        <p class="card-text">
                            TrioTravel este rezultatul colaborării a trei studenți pasionați de la 
                            Universitatea Politehnica Timișoara, care și-au propus să revoluționeze 
                            modul în care oamenii își planifică vacanțele.
                        </p>
                        
                        <div class="row mt-4 text-center">
                            <h5 class="card-title mb-4">Echipa noastră</h5>
                            <div class="col-md-4">
                                <img src="images/membru1.jpg" class="rounded-circle mb-3" alt="Membru 1" style="width: 150px; height: 150px; object-fit: cover;">
                                <h6>Belu Ioana</h6>
                                <p class="text-muted">Web Developer</p>
                            </div>
                            <div class="col-md-4">
                                <img src="images/membru2.jpg" class="rounded-circle mb-3" alt="Membru 2" style="width: 150px; height: 150px; object-fit: cover;">
                                <h6>David Dan-Catalin</h6>
                                <p class="text-muted">Web Developer</p>
                            </div>
                            <div class="col-md-4">
                                <img src="images/membru3.jpg" class="rounded-circle mb-3" alt="Membru 3" style="width: 150px; height: 150px; object-fit: cover;">
                                <h6>Dobritan Luminita-Elena</h6>
                                <p class="text-muted">Web Developer</p>
                            </div>
                        </div>
                        
                        <h5 class="card-title mt-5">Misiunea noastră</h5>
                        <p class="card-text">
                            Ne-am propus să oferim o experiență simplă și plăcută în rezervarea 
                            vacanțelor, combinând tehnologia modernă cu servicii personalizate 
                            de înaltă calitate.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 