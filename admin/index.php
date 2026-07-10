<?php
/**
 * TechHub — Simple Admin Dashboard
 * File: admin/index.php
 *
 * Protect this page with HTTP Basic Auth on your server,
 * or add session-based login. For demo purposes, a password
 * check is shown below.
 *
 * Access: http://yoursite.com/admin/
 */

session_start();
require_once '../config/db.php';

// Simple password gate — replace with proper auth in production
define('ADMIN_PASSWORD', 'techhub2025!');
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['password'] ?? '' === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        if (isset($_POST['password'])) {
            $loginError = 'Incorrect password';
        }
        ?><!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>TechHub Admin Login</title>
          <style>
            *{box-sizing:border-box;margin:0;padding:0}
            body{background:#0a0c10;font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;}
            .card{background:#161b22;border:1px solid #21262d;border-radius:12px;padding:40px;width:360px;text-align:center;}
            .logo{font-size:32px;font-weight:800;color:#fff;margin-bottom:4px;}
            .logo span{color:#00d4ff;}
            .sub{color:#64748b;font-size:13px;margin-bottom:32px;}
            input{width:100%;padding:12px 16px;background:#0a0c10;border:1px solid #21262d;border-radius:8px;color:#e6edf3;font-size:14px;margin-bottom:16px;}
            input:focus{outline:none;border-color:#00d4ff;}
            button{width:100%;padding:12px;background:linear-gradient(135deg,#1a56db,#7c3aed);color:#fff;font-weight:700;border:none;border-radius:8px;font-size:15px;cursor:pointer;}
            .error{color:#f87171;font-size:13px;margin-bottom:12px;}
          </style>
        </head>
        <body>
          <div class="card">
            <div class="logo">⚡ Tech<span>Hub</span></div>
            <div class="sub">Admin Dashboard</div>
            <?php if (isset($loginError)): ?>
              <div class="error"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <form method="POST">
              <input type="password" name="password" placeholder="Admin password" autofocus required />
              <button type="submit">Sign In</button>
            </form>
          </div>
        </body></html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Fetch stats
try {
    $totalContacts      = $pdo->query("SELECT COUNT(*) FROM contact_submissions")->fetchColumn();
    $newContacts        = $pdo->query("SELECT COUNT(*) FROM contact_submissions WHERE status='new'")->fetchColumn();
    $totalSubscribers   = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='active'")->fetchColumn();
    $totalBlogPosts     = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'")->fetchColumn();
    $openJobs           = $pdo->query("SELECT COUNT(*) FROM job_listings WHERE status='open'")->fetchColumn();

    $recentContacts = $pdo->query("
        SELECT id, first_name, last_name, email, enquiry_type, status, created_at
        FROM contact_submissions ORDER BY created_at DESC LIMIT 10
    ")->fetchAll();

    $recentSubscribers = $pdo->query("
        SELECT email, created_at FROM newsletter_subscribers
        WHERE status='active' ORDER BY created_at DESC LIMIT 5
    ")->fetchAll();

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id     = (int)$_POST['contact_id'];
    $status = in_array($_POST['new_status'], ['new','read','replied','closed'])
              ? $_POST['new_status'] : 'new';
    $pdo->prepare("UPDATE contact_submissions SET status=:s WHERE id=:id")
        ->execute([':s' => $status, ':id' => $id]);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechHub Admin Dashboard</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0a0c10;color:#e6edf3;font-family:Arial,sans-serif;font-size:14px;}
    a{color:#00d4ff;text-decoration:none;}
    /* Sidebar */
    .sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:#111418;border-right:1px solid #21262d;padding:24px 0;display:flex;flex-direction:column;}
    .sidebar-logo{padding:0 20px 24px;font-size:20px;font-weight:800;color:#fff;border-bottom:1px solid #21262d;}
    .sidebar-logo span{color:#00d4ff;}
    .sidebar-nav{padding:16px 0;flex:1;}
    .nav-item{display:block;padding:10px 20px;color:#8b949e;transition:.2s;}
    .nav-item:hover,.nav-item.active{color:#fff;background:rgba(255,255,255,.04);}
    .sidebar-footer{padding:16px 20px;border-top:1px solid #21262d;}
    /* Main */
    .main{margin-left:220px;padding:32px;}
    .page-title{font-size:24px;font-weight:700;color:#fff;margin-bottom:8px;}
    .page-sub{color:#64748b;margin-bottom:28px;}
    /* Stat cards */
    .stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:32px;}
    .stat-card{background:#161b22;border:1px solid #21262d;border-radius:10px;padding:20px;}
    .stat-card .label{font-size:12px;color:#64748b;font-weight:600;letter-spacing:.05em;text-transform:uppercase;margin-bottom:8px;}
    .stat-card .value{font-size:28px;font-weight:800;color:#00d4ff;}
    .stat-card .sub{font-size:12px;color:#64748b;margin-top:4px;}
    /* Table */
    .card{background:#161b22;border:1px solid #21262d;border-radius:10px;overflow:hidden;margin-bottom:24px;}
    .card-header{padding:16px 20px;border-bottom:1px solid #21262d;display:flex;justify-content:space-between;align-items:center;}
    .card-header h3{font-size:15px;font-weight:700;color:#fff;}
    table{width:100%;border-collapse:collapse;}
    th{background:#0a0c10;padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;letter-spacing:.05em;text-transform:uppercase;}
    td{padding:10px 16px;border-bottom:1px solid #21262d;color:#e6edf3;}
    tr:last-child td{border-bottom:none;}
    tr:hover td{background:rgba(255,255,255,.02);}
    /* Badges */
    .badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;}
    .badge-new{background:rgba(0,212,255,.12);color:#00d4ff;}
    .badge-read{background:rgba(100,116,139,.15);color:#94a3b8;}
    .badge-replied{background:rgba(16,185,129,.12);color:#34d399;}
    .badge-closed{background:rgba(239,68,68,.12);color:#f87171;}
    select{background:#0a0c10;border:1px solid #21262d;color:#e6edf3;padding:4px 8px;border-radius:6px;font-size:12px;}
    button[type=submit]{background:#1a56db;color:#fff;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:12px;}
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">⚡ Tech<span>Hub</span></div>
  <nav class="sidebar-nav">
    <a class="nav-item active" href="index.php">📊 Dashboard</a>
    <a class="nav-item" href="index.php">✉️ Contact Submissions</a>
    <a class="nav-item" href="index.php">📧 Subscribers</a>
    <a class="nav-item" href="index.php">📝 Blog Posts</a>
    <a class="nav-item" href="index.php">👥 Team Members</a>
    <a class="nav-item" href="index.php">💼 Job Listings</a>
  </nav>
  <div class="sidebar-footer">
    <a href="?logout=1" style="color:#f87171;font-size:13px;">🚪 Log Out</a>
  </div>
</aside>

<main class="main">
  <div class="page-title">Dashboard</div>
  <div class="page-sub">Welcome back. Here's what's happening at TechHub today.</div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="label">Total Enquiries</div>
      <div class="value"><?= $totalContacts ?></div>
      <div class="sub">All time</div>
    </div>
    <div class="stat-card">
      <div class="label">New Enquiries</div>
      <div class="value" style="color:#f59e0b;"><?= $newContacts ?></div>
      <div class="sub">Awaiting response</div>
    </div>
    <div class="stat-card">
      <div class="label">Subscribers</div>
      <div class="value" style="color:#a78bfa;"><?= $totalSubscribers ?></div>
      <div class="sub">Active newsletter</div>
    </div>
    <div class="stat-card">
      <div class="label">Blog Posts</div>
      <div class="value" style="color:#34d399;"><?= $totalBlogPosts ?></div>
      <div class="sub">Published</div>
    </div>
    <div class="stat-card">
      <div class="label">Open Jobs</div>
      <div class="value" style="color:#00d4ff;"><?= $openJobs ?></div>
      <div class="sub">Active listings</div>
    </div>
  </div>

  <!-- Contact Submissions -->
  <div class="card">
    <div class="card-header">
      <h3>Recent Contact Submissions</h3>
    </div>
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Enquiry Type</th>
          <th>Date</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentContacts as $row): ?>
        <tr>
          <td style="color:#64748b;">#<?= str_pad($row['id'], 5, '0', STR_PAD_LEFT) ?></td>
          <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
          <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
          <td><?= htmlspecialchars($row['enquiry_type']) ?></td>
          <td style="color:#64748b;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td>
            <form method="POST" style="display:inline-flex;gap:6px;align-items:center;">
              <input type="hidden" name="contact_id" value="<?= $row['id'] ?>">
              <select name="new_status">
                <option value="new"     <?= $row['status']==='new'?'selected':'' ?>>New</option>
                <option value="read"    <?= $row['status']==='read'?'selected':'' ?>>Read</option>
                <option value="replied" <?= $row['status']==='replied'?'selected':'' ?>>Replied</option>
                <option value="closed"  <?= $row['status']==='closed'?'selected':'' ?>>Closed</option>
              </select>
              <button type="submit" name="update_status">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentContacts)): ?>
        <tr><td colspan="7" style="text-align:center;color:#64748b;padding:32px;">No submissions yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Subscribers -->
  <div class="card">
    <div class="card-header"><h3>Recent Newsletter Subscribers</h3></div>
    <table>
      <thead><tr><th>Email</th><th>Subscribed</th></tr></thead>
      <tbody>
        <?php foreach ($recentSubscribers as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td style="color:#64748b;"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentSubscribers)): ?>
        <tr><td colspan="2" style="text-align:center;color:#64748b;padding:32px;">No subscribers yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
