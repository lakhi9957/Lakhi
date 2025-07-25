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
        // Fetch gallery images
        $query = "SELECT * FROM gallery ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);
        
        $images = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $images[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'image' => $row['image_path'],
                    'category' => $row['category'],
                    'created_at' => $row['created_at']
                ];
            }
        }
        
        // If no images in database, return sample data
        if (empty($images)) {
            $images = [
                [
                    'id' => 1,
                    'title' => 'School Building',
                    'description' => 'Main school building exterior',
                    'image' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400&h=300&fit=crop',
                    'category' => 'Infrastructure'
                ],
                [
                    'id' => 2,
                    'title' => 'Science Laboratory',
                    'description' => 'Modern science lab with equipment',
                    'image' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400&h=300&fit=crop',
                    'category' => 'Facilities'
                ],
                [
                    'id' => 3,
                    'title' => 'Library',
                    'description' => 'Spacious library with reading areas',
                    'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=300&fit=crop',
                    'category' => 'Facilities'
                ],
                [
                    'id' => 4,
                    'title' => 'Sports Field',
                    'description' => 'Athletic field for sports activities',
                    'image' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
                    'category' => 'Sports'
                ],
                [
                    'id' => 5,
                    'title' => 'Computer Lab',
                    'description' => 'High-tech computer laboratory',
                    'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=300&fit=crop',
                    'category' => 'Technology'
                ],
                [
                    'id' => 6,
                    'title' => 'Graduation Ceremony',
                    'description' => 'Annual graduation celebration',
                    'image' => 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=400&h=300&fit=crop',
                    'category' => 'Events'
                ]
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $images
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