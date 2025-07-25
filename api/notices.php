<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include '../config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Fetch notices
        $query = "SELECT * FROM notices ORDER BY date_created DESC";
        $result = mysqli_query($conn, $query);
        
        $notices = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $notices[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'content' => $row['content'],
                    'priority' => $row['priority'],
                    'date' => $row['date_created'],
                    'created_at' => $row['created_at']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $notices
        ]);
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>