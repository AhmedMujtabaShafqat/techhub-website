<?php
/**
 * TechHub — Email Helper Functions
 * File: config/mailer.php
 *
 * Uses PHP's built-in mail() function.
 * For production, replace with PHPMailer + SMTP (Gmail / SendGrid / Mailgun).
 */

define('ADMIN_EMAIL',   'hello@techhub.io');
define('FROM_EMAIL',    'no-reply@techhub.io');
define('FROM_NAME',     'TechHub');
define('SITE_URL',      'https://www.techhub.io');

/**
 * Send a confirmation email to the person who submitted the contact form.
 */
function sendConfirmationEmail(string $toEmail, string $firstName, int $submissionId): void {
    $subject = "We received your message — TechHub";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";

    $body = '
    <!DOCTYPE html>
    <html lang="en">
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
    <body style="margin:0;padding:0;background:#0a0c10;font-family:Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0c10;padding:40px 20px;">
        <tr><td align="center">
          <table width="600" cellpadding="0" cellspacing="0" style="background:#161b22;border-radius:12px;overflow:hidden;border:1px solid #21262d;">
            <!-- Header -->
            <tr><td style="background:linear-gradient(135deg,#1a56db,#7c3aed);padding:32px;text-align:center;">
              <div style="font-size:28px;font-weight:800;color:#fff;">⚡ TechHub</div>
              <div style="color:rgba(255,255,255,.8);font-size:14px;margin-top:4px;">Enterprise Technology Solutions</div>
            </td></tr>
            <!-- Body -->
            <tr><td style="padding:40px 32px;">
              <h1 style="color:#fff;font-size:22px;margin:0 0 16px;">Hi ' . htmlspecialchars($firstName) . ', thanks for reaching out!</h1>
              <p style="color:#8b949e;font-size:15px;line-height:1.7;margin:0 0 20px;">
                We have received your message (Reference: <strong style="color:#00d4ff;">#TH-' . str_pad($submissionId, 5, '0', STR_PAD_LEFT) . '</strong>) and a member of our team will respond within <strong style="color:#fff;">4 business hours</strong>.
              </p>
              <p style="color:#8b949e;font-size:15px;line-height:1.7;margin:0 0 32px;">
                If your enquiry is urgent, please call us on <a href="tel:+441612001234" style="color:#00d4ff;">+44 (0) 161 200 1234</a>.
              </p>
              <div style="text-align:center;">
                <a href="' . SITE_URL . '" style="display:inline-block;background:linear-gradient(135deg,#00d4ff,#0099bb);color:#000;font-weight:700;padding:14px 32px;border-radius:10px;text-decoration:none;font-size:15px;">Visit TechHub</a>
              </div>
            </td></tr>
            <!-- Footer -->
            <tr><td style="padding:24px 32px;border-top:1px solid #21262d;text-align:center;">
              <p style="color:#64748b;font-size:12px;margin:0;">© 2025 TechHub Ltd. · 12 Innovation Square, Manchester, M1 7PQ</p>
            </td></tr>
          </table>
        </td></tr>
      </table>
    </body></html>';

    mail($toEmail, $subject, $body, $headers);
}

/**
 * Send an internal notification email to the admin when a new enquiry arrives.
 */
function sendAdminNotification(
    string $firstName, string $lastName, string $email,
    string $company, string $enquiry, string $message
): void {
    $subject  = "New Contact Enquiry: $enquiry — $firstName $lastName";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: TechHub Website <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: $email\r\n";

    $body = '
    <!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f1f5f9;padding:30px;">
      <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;">
        <div style="background:#1a56db;padding:20px 24px;">
          <div style="color:#fff;font-size:18px;font-weight:700;">⚡ New Contact Form Submission</div>
        </div>
        <div style="padding:24px;">
          <table width="100%" cellpadding="8" cellspacing="0">
            <tr style="background:#f8fafc;"><td style="font-weight:600;color:#64748b;width:140px;">Name</td><td>' . htmlspecialchars("$firstName $lastName") . '</td></tr>
            <tr><td style="font-weight:600;color:#64748b;">Email</td><td><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></td></tr>
            <tr style="background:#f8fafc;"><td style="font-weight:600;color:#64748b;">Company</td><td>' . htmlspecialchars($company ?: '—') . '</td></tr>
            <tr><td style="font-weight:600;color:#64748b;">Enquiry Type</td><td><strong>' . htmlspecialchars($enquiry) . '</strong></td></tr>
            <tr style="background:#f8fafc;"><td style="font-weight:600;color:#64748b;vertical-align:top;">Message</td><td>' . nl2br(htmlspecialchars($message)) . '</td></tr>
          </table>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
          Submitted: ' . date('d M Y, H:i') . ' GMT · IP: ' . $_SERVER['REMOTE_ADDR'] . '
        </div>
      </div>
    </body></html>';

    mail(ADMIN_EMAIL, $subject, $body, $headers);
}
