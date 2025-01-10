<?php
require_once '../conexiune.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Marchează mesajul ca citit
if (isset($_POST['mark_read']) && isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];
    $sql = "UPDATE mesaje_contact SET citit = 1 WHERE id = $message_id";
    $conn->query($sql);
    header('Location: mesaje.php');
    exit;
}

// Șterge mesajul
if (isset($_POST['delete']) && isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];
    $sql = "DELETE FROM mesaje_contact WHERE id = $message_id";
    $conn->query($sql);
    header('Location: mesaje.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Mesaje Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Mesaje de Contact</h2>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Nume</th>
                        <th>Email</th>
                        <th>Subiect</th>
                        <th>Mesaj</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Facem query-ul aici, nu la început
                    $sql = "SELECT * FROM mesaje_contact ORDER BY citit ASC, data_trimitere DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr class="<?php echo $row['citit'] ? '' : 'table-warning'; ?>">
                                <td>
                                    <?php if ($row['citit']): ?>
                                        <i class="bi bi-envelope-open text-muted"></i>
                                    <?php else: ?>
                                        <i class="bi bi-envelope-fill text-warning"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($row['data_trimitere'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nume']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['subiect']); ?></td>
                                <td><?php echo htmlspecialchars($row['mesaj']); ?></td>
                                <td>
                                    <?php if (!$row['citit']): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="mark_read" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" class="d-inline ms-1">
                                        <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">Nu există mesaje</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 