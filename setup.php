<?php
/**
 * TechHub — Setup & Environment Check
 * File: setup.php
 *
 * Run this ONCE after uploading to your server:
 *   http://yoursite.com/techhub/setup.php
 *
 * DELETE this file after setup is confirmed!
 */

$checks = [];

// PHP version
$phpOk = version_compare(PHP_VERSION, '7.4.0', '>=');
$checks[] = ['PHP Version >= 7.4', $phpOk, PHP_VERSION];

// PDO MySQL
$pdoOk = extension_loaded('pdo_mysql');
$checks[] = ['PDO MySQL Extension', $pdoOk, $pdoOk ? 'Loaded' : 'MISSING — enable pdo_mysql in php.ini'];

// mail() function
$mailOk = function_exists('mail');
$checks[] = ['mail() Function', $mailOk, $mailOk ? 'Available' : 'Not available'];

// Config files exist
$dbConfOk = file_exists(__DIR__ . '/config/db.php');
$checks[] = ['config/db.php exists', $dbConfOk, $dbConfOk ? 'Found' : 'MISSING'];

$mailerConfOk = file_exists(__DIR__ . '/config/mailer.php');
$checks[] = ['config/mailer.php exists', $mailerConfOk, $mailerConfOk ? 'Found' : 'MISSING'];

// Database connection
$dbConnOk = false;
$dbMsg    = '';
if ($pdoOk && $dbConfOk) {
    try {
        require_once __DIR__ . '/config/db.php';
        $pdo->query('SELECT 1');
        $dbConnOk = true;
        $dbMsg    = 'Connected to ' . DB_NAME . ' on ' . DB_HOST;
    } catch (Exception $e) {
        $dbMsg = 'FAILED: ' . $e->getMessage();
    }
}
$checks[] = ['Database Connection', $dbConnOk, $dbMsg];

// Tables exist
$tablesOk = false;
$tablesMsg = '';
if ($dbConnOk) {
    try {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['contact_submissions', 'newsletter_subscribers', 'blog_posts', 'team_members', 'job_listings'];
        $missing  = array_diff($required, $tables);
        if (empty($missing)) {
            $tablesOk  = true;
            $tablesMsg = 'All 5 tables found';
        } else {
            $tablesMsg = 'Missing: ' . implode(', ', $missing) . '. Run database/schema.sql';
        }
    } catch (Exception $e) {
        $tablesMsg = 'Could not check tables';
    }
}
$checks[] = ['Database Tables', $tablesOk, $tablesMsg];

// Schema file
$schemaOk = file_exists(__DIR__ . '/database/schema.sql');
$checks[] = ['database/schema.sql exists', $schemaOk, $schemaOk ? 'Found' : 'MISSING'];

// .htaccess
$htaccessOk = file_exists(__DIR__ . '/.htaccess');
$checks[] = ['.htaccess exists', $htaccessOk, $htaccessOk ? 'Found' : 'Not found (optional on nginx)'];

$allOk = !in_array(false, array_column($checks, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechHub Setup Check</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0a0c10;font-family:Arial,sans-serif;color:#e6edf3;padding:40px 20px;}
    .wrap{max-width:760px;margin:0 auto;}
    h1{font-size:28px;font-weight:800;color:#fff;margin-bottom:4px;}
    h1 span{color:#00d4ff;}
    .sub{color:#64748b;margin-bottom:32px;font-size:14px;}
    .status-box{padding:16px 20px;border-radius:10px;margin-bottom:24px;font-weight:600;font-size:15px;}
    .ok{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#34d399;}
    .fail{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171;}
    table{width:100%;border-collapse:collapse;background:#161b22;border-radius:10px;overflow:hidden;border:1px solid #21262d;margin-bottom:32px;}
    th{background:#1e293b;padding:12px 16px;text-align:left;font-size:12px;color:#64748b;letter-spacing:.05em;text-transform:uppercase;}
    td{padding:12px 16px;border-bottom:1px solid #21262d;font-size:14px;}
    tr:last-child td{border-bottom:none;}
    .pass{color:#34d399;font-weight:700;}
    .fail-cell{color:#f87171;font-weight:700;}
    .note{background:#161b22;border:1px solid #21262d;border-radius:10px;padding:20px;font-size:13px;color:#64748b;line-height:1.8;}
    .note strong{color:#fbbf24;}
    code{background:#0a0c10;padding:2px 6px;border-radius:4px;color:#00d4ff;font-size:12px;}
  </style>
</head>
<body>
<div class="wrap">
  <h1>⚡ Tech<span>Hub</span> — Setup Check</h1>
  <p class="sub">Verifying your server environment before launch.</p>

  <div class="status-box <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk
      ? '✅ All checks passed! Your server is ready. Delete this file before going live.'
      : '❌ Some checks failed. Fix the issues below, then reload this page.' ?>
  </div>

  <table>
    <thead><tr><th>Check</th><th>Result</th><th>Detail</th></tr></thead>
    <tbody>
      <?php foreach ($checks as $check): ?>
      <tr>
        <td><?= htmlspecialchars($check[0]) ?></td>
        <td class="<?= $check[1] ? 'pass' : 'fail-cell' ?>"><?= $check[1] ? 'PASS' : 'FAIL' ?></td>
        <td style="color:<?= $check[1] ? '#94a3b8' : '#f87171' ?>;"><?= htmlspecialchars($check[2]) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if (!$tablesOk && $dbConnOk): ?>
  <div class="note">
    <strong>⚠️ Database tables missing.</strong> To create them, run the SQL schema in phpMyAdmin:<br><br>
    1. Open <strong>phpMyAdmin</strong><br>
    2. Select (or create) the database: <code><?= DB_NAME ?></code><br>
    3. Click <strong>Import</strong> → choose <code>database/schema.sql</code><br>
    4. Click <strong>Go</strong><br><br>
    Or from the MySQL command line:<br>
    <code>mysql -u <?= DB_USER ?> -p &lt; database/schema.sql</code>
  </div>
  <?php endif; ?>

  <div class="note" style="margin-top:16px;">
    <strong>🔐 Security reminder:</strong> Delete <code>setup.php</code> from your server once everything is working.
    Never leave setup/diagnostic scripts accessible in production.
  </div>
</div>
</body>
</html>
