<?php
require_once 'conexiune.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - TrioTravel</title>
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
        <h1 class="text-center mb-4">Contact</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informații Contact</h5>
                        <p class="card-text">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            <strong>Adresă:</strong> Bulevardul Vasile Pârvan nr. 2, Timișoara, România
                        </p>
                        <p class="card-text">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <strong>Telefon:</strong> 0256 403 000
                        </p>
                        <p class="card-text">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <strong>Email:</strong> contact@triotravel.ro
                        </p>
                        <p class="card-text">
                            <i class="bi bi-globe me-2"></i>
                            <strong>Website:</strong> www.triotravel.ro
                        </p>
                        
                        <h5 class="card-title mt-4">Program de lucru</h5>
                        <p class="card-text">
                            <i class="bi bi-clock-fill me-2"></i>
                            Luni - Vineri: 09:00 - 17:00<br>
                            <i class="bi bi-clock me-2"></i>
                            Sâmbătă: 10:00 - 14:00<br>
                            <i class="bi bi-clock me-2"></i>
                            Duminică: Închis
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Trimite-ne un mesaj</h5>
                        <div id="formAlert" class="alert" style="display: none;"></div>
                        <form id="contactForm">
                            <div class="mb-3">
                                <label for="nume" class="form-label">Nume</label>
                                <input type="text" class="form-control" id="nume" name="nume" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="subiect" class="form-label">Subiect</label>
                                <input type="text" class="form-control" id="subiect" name="subiect" required>
                            </div>
                            <div class="mb-3">
                                <label for="mesaj" class="form-label">Mesaj</label>
                                <textarea class="form-control" id="mesaj" name="mesaj" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send-fill"></i> Trimite mesaj
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Google Maps -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Locația noastră</h5>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2783.8754746071627!2d21.225524776271837!3d45.74726791081462!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47455d84610655bf%3A0xfd169ff24d29f192!2sUniversitatea%20Politehnica%20Timisoara!5e0!3m2!1sro!2sro!4v1701234567890!5m2!1sro!2sro" 
                                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted'); // Debug log
        
        const formData = new FormData(this);
        const alert = document.getElementById('formAlert');
        const submitBtn = this.querySelector('button[type="submit"]');
        
        // Debug log pentru date
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Se trimite...';
        
        fetch('process_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Raw response:', response); // Debug log
            return response.json();
        })
        .then(data => {
            console.log('Processed data:', data); // Debug log
            alert.style.display = 'block';
            if (data.success) {
                alert.className = 'alert alert-success';
                alert.textContent = data.message;
                this.reset();
            } else {
                alert.className = 'alert alert-danger';
                alert.textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            alert.style.display = 'block';
            alert.className = 'alert alert-danger';
            alert.textContent = 'A apărut o eroare la trimiterea mesajului. Vă rugăm încercați din nou.';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill"></i> Trimite mesaj';
        });
    });
    </script>
</body>
</html> 