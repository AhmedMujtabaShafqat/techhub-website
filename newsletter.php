<?php
/**
 * TechHub — Newsletter Subscribe Handler
 * File: newsletter.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Check if already subscribed
    $check = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = :email");
    $check->execute([':email' => $email]);
    $existing = $check->fetch();

    if ($existing) {
        if ($existing['status'] === 'active') {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
        } else {
            // Re-activate unsubscribed user
            $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', updated_at = NOW() WHERE email = :email")
                ->execute([':email' => $email]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
        }
        exit;
    }

    // Insert new subscriber
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, status, unsubscribe_token, ip_address, created_at)
        VALUES (:email, 'active', :token, :ip, NOW())
    ");
    $stmt->execute([
        ':email' => $email,
        ':token' => $token,
        ':ip'    => $_SERVER['REMOTE_ADDR'],
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thanks for subscribing! Check your inbox for a confirmation email.',
    ]);

} catch (PDOException $e) {
    error_log('Newsletter DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}
