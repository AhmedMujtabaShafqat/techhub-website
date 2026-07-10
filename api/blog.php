<?php
/**
 * TechHub — Blog Posts API
 * File: api/blog.php
 *
 * GET /api/blog.php              → returns all published posts
 * GET /api/blog.php?category=Cloud  → filter by category
 * GET /api/blog.php?slug=some-slug  → single post
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$category = htmlspecialchars(trim($_GET['category'] ?? ''));
$slug     = htmlspecialchars(trim($_GET['slug']     ?? ''));
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 6;
$offset   = ($page - 1) * $perPage;

try {
    if ($slug) {
        // Single post by slug
        $stmt = $pdo->prepare("
            SELECT * FROM blog_posts
            WHERE slug = :slug AND status = 'published'
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Post not found']);
            exit;
        }

        echo json_encode(['success' => true, 'post' => $post]);

    } else {
        // List posts
        $where  = "WHERE status = 'published'";
        $params = [];

        if ($category) {
            $where .= " AND category = :category";
            $params[':category'] = $category;
        }

        // Total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Posts
        $params[':limit']  = $perPage;
        $params[':offset'] = $offset;
        $stmt = $pdo->prepare("
            SELECT id, title, slug, excerpt, category, author_name, author_role, featured, published_at
            FROM blog_posts
            $where
            ORDER BY featured DESC, published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        // PDO requires separate bindValue for int params with LIMIT/OFFSET
        foreach ($params as $k => $v) {
            if ($k === ':limit' || $k === ':offset') {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
        $stmt->execute();
        $posts = $stmt->fetchAll();

        echo json_encode([
            'success'    => true,
            'posts'      => $posts,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => ceil($total / $perPage),
        ]);
    }

} catch (PDOException $e) {
    error_log('Blog API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
