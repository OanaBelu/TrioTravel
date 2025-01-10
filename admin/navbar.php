<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="../images/logo.png" alt="TrioTravel Logo" height="30" class="d-inline-block align-text-top me-2">
            TrioTravel
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Admin Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="excursii.php">Excursii</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rezervari.php">Rezervări</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clienti.php">Clienți</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rapoarte.php">Rapoarte</a>
                </li>
                <li class="nav-item">
                    <?php
                    require_once '../conexiune.php';
                    $query_mesaje_necitite = "SELECT COUNT(*) as numar FROM mesaje_contact WHERE citit = 0";
                    $result_mesaje = $conn->query($query_mesaje_necitite);
                    $mesaje_necitite = $result_mesaje->fetch_assoc()['numar'];
                    ?>
                    <a class="nav-link position-relative" href="mesaje.php">
                        Mesaje
                        <?php if ($mesaje_necitite > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $mesaje_necitite; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Vezi Site</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Ieșire</a>
                </li>
            </ul>
        </div>
    </div>
</nav> 