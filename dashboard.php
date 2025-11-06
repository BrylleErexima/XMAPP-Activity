<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
include 'db.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$pending = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND status = 'pending'");
$pending->execute([$user_id]);
$pending_count = $pending->fetchColumn();

$overdue = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND due_date < CURDATE() AND
status != 'completed'");
$overdue->execute([$user_id]);
$overdue_count = $overdue->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2>Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong>!</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5>Pending Tasks</h5>
                        <h2><?php echo $pending_count; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5>Overdue</h5>
                        <h2><?php echo $overdue_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="todos.php" class="btn btn-primary">Go to TODOs</a>
        </div>
    </div>
</body>
</html>