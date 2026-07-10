<?php
/**
 * TechHub — Team Members API
 * File: api/team.php
 *
 * GET /api/team.php                       → all active members
 * GET /api/team.php?department=Leadership → filter by department
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$department = htmlspecialchars(trim($_GET['department'] ?? ''));

try {
    $where  = "WHERE active = 1";
    $params = [];

    if ($department) {
        $where .= " AND department = :department";
        $params[':department'] = $department;
    }

    $stmt = $pdo->prepare("
        SELECT id, name, role, department, bio, linkedin_url, twitter_url, github_url
        FROM team_members
        $where
        ORDER BY display_order ASC
    ");
    $stmt->execute($params);
    $members = $stmt->fetchAll();

    echo json_encode(['success' => true, 'members' => $members, 'total' => count($members)]);

} catch (PDOException $e) {
    error_log('Team API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
