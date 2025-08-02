<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include '../config.php';

try {
    // Handle different request types
    if (isset($_GET['count_only'])) {
        // Return job count only
        $sql = "SELECT COUNT(*) as count FROM jobs WHERE status = 'active'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        echo json_encode(['count' => (int)$row['count']]);
        exit;
    }

    // Build the query
    $sql = "SELECT j.*, u.name as posted_by FROM jobs j 
            LEFT JOIN users u ON j.created_by = u.id 
            WHERE j.status = 'active'";
    
    $params = [];
    $types = '';

    // Add search filter
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $sql .= " AND (j.title LIKE ? OR j.description LIKE ? OR j.location LIKE ?)";
        $searchTerm = '%' . $_GET['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    // Add job type filter
    if (isset($_GET['job_type']) && !empty($_GET['job_type'])) {
        $sql .= " AND j.job_type = ?";
        $params[] = $_GET['job_type'];
        $types .= 's';
    }

    // Add location filter
    if (isset($_GET['location']) && !empty($_GET['location'])) {
        $sql .= " AND j.location LIKE ?";
        $params[] = '%' . $_GET['location'] . '%';
        $types .= 's';
    }

    // Add ordering
    $sql .= " ORDER BY j.posted_date DESC";

    // Add limit for featured jobs
    if (isset($_GET['featured']) || isset($_GET['limit'])) {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
    }

    // Prepare and execute statement
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    $jobs = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobs[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'requirements' => $row['requirements'],
                'location' => $row['location'],
                'salary_range' => $row['salary_range'],
                'job_type' => $row['job_type'],
                'posted_date' => $row['posted_date'],
                'deadline' => $row['deadline'],
                'posted_by' => $row['posted_by']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'total' => count($jobs)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>