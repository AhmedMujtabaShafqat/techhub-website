<?php
/**
 * TechHub — Contact Form Handler
 * File: contact.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'config/db.php';
require_once 'config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitise inputs
$firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
$lastName  = htmlspecialchars(trim($_POST['lastName']  ?? ''));
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$company   = htmlspecialchars(trim($_POST['company']  ?? ''));
$enquiry   = htmlspecialchars(trim($_POST['enquiry']  ?? ''));
$message   = htmlspecialchars(trim($_POST['message']  ?? ''));
$consent   = isset($_POST['consent']) ? 1 : 0;

// Validate
$errors = [];
if (empty($firstName)) $errors[] = 'First name is required';
if (empty($lastName))  $errors[] = 'Last name is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
if (empty($enquiry))   $errors[] = 'Enquiry type is required';
if (empty($message))   $errors[] = 'Message is required';
if (!$consent)         $errors[] = 'You must agree to the privacy policy';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    // Save to database
    $stmt = $pdo->prepare("
        INSERT INTO contact_submissions
            (first_name, last_name, email, company, enquiry_type, message, consent, ip_address, created_at)
        VALUES
            (:first_name, :last_name, :email, :company, :enquiry_type, :message, :consent, :ip, NOW())
    ");
    $stmt->execute([
        ':first_name'   => $firstName,
        ':last_name'    => $lastName,
        ':email'        => $email,
        ':company'      => $company,
        ':enquiry_type' => $enquiry,
        ':message'      => $message,
        ':consent'      => $consent,
        ':ip'           => $_SERVER['REMOTE_ADDR'],
    ]);

    $submissionId = $pdo->lastInsertId();

    // Send confirmation email to user
    sendConfirmationEmail($email, $firstName, $submissionId);

    // Send notification to admin
    sendAdminNotification($firstName, $lastName, $email, $company, $enquiry, $message);

    echo json_encode([
        'success' => true,
        'message' => "Thank you, $firstName! Your message has been received. We'll respond within 4 business hours.",
        'id'      => $submissionId,
    ]);

} catch (PDOException $e) {
    error_log('Contact form DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A server error occurred. Please try again.']);
}
