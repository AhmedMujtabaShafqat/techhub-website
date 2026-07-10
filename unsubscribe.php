<?php
/**
 * TechHub — Newsletter Unsubscribe
 * File: unsubscribe.php
 * URL: https://yoursite.com/unsubscribe.php?token=XXXX
 */

require_once 'config/db.php';

$token = htmlspecialchars(trim($_GET['token'] ?? ''));
$message = '';
$success = false;

if (empty($token)) {
    $message = 'Invalid unsubscribe link.';
} else {
    try {
        $stmt = $pdo->prepare("SELECT id, email, status FROM newsletter_subscribers WHERE unsubscribe_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $subscriber = $stmt->fetch();

        if (!$subscriber) {
            $message = 'This unsubscribe link is invalid or has already been used.';
        } elseif ($subscriber['status'] === 'unsubscribed') {
            $message = 'You are already unsubscribed.';
            $success = true;
        } else {
            $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed', updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $subscriber['id']]);
            $message = 'You have been successfully unsubscribed from TechHub newsletters.';
            $success = true;
        }
    } catch (PDOException $e) {
        error_log('Unsubscribe error: ' . $e->getMessage());
        $message = 'A server error occurred. Please contact support.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unsubscribe — TechHub</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
  <nav class="nav-container">
    <a href="index.html" class="logo"><div class="logo-icon">⚡</div>Tech<span>Hub</span></a>
  </nav>
</header>
<main class="page-wrapper">
  <div style="min-height:70vh;display:flex;align-items:center;justify-content:center;">
    <div style="text-align:center;max-width:480px;padding:2rem;">
      <div style="font-size:4rem;margin-bottom:1.5rem;"><?= $success ? '✅' : '❌' ?></div>
      <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--clr-white);margin-bottom:1rem;">
        <?= $success ? 'Unsubscribed' : 'Oops' ?>
      </h1>
      <p style="color:var(--clr-muted);font-size:1rem;line-height:1.7;margin-bottom:2rem;">
        <?= htmlspecialchars($message) ?>
      </p>
      <a href="index.html" class="btn btn-primary">Back to TechHub</a>
    </div>
  </div>
</main>
<script src="js/main.js"></script>
</body>
</html>
