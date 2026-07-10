<?php
/**
 * TechHub — Job Listings API
 * File: api/jobs.php
 *
 * GET /api/jobs.php                         → all open jobs
 * GET /api/jobs.php?department=Engineering  → filter by department
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$department = htmlspecialchars(trim($_GET['department'] ?? ''));

try {
    $where  = "WHERE status = 'open'";
    $params = [];

    if ($department) {
        $where .= " AND department = :department";
        $params[':department'] = $department;
    }

    $stmt = $pdo->prepare("
        SELECT id, title, department, location_type, employment_type, salary_range, description, created_at
        FROM job_listings
        $where
        ORDER BY department ASC, created_at DESC
    ");
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    echo json_encode(['success' => true, 'jobs' => $jobs, 'total' => count($jobs)]);

} catch (PDOException $e) {
    error_log('Jobs API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
