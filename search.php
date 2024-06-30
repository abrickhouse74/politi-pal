<?php
// Database connection details
$host = 'localhost';
$dbname = 'iyanadb';
$user = 'root';
$password = 'iyana';

// Attempt to connect to the database
$conn = new mysqli($host, $user, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute a query and fetch results
function fetchQueryResults($conn, $query) {
    $result = $conn->query($query);
    $resultsArray = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resultsArray[] = $row;
        }
    }
    return $resultsArray;
}

// Handle GET request to fetch politician names for small circles by town
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['town'])) {
    $town = $conn->real_escape_string($_GET['town']);
    
    // Fetch local politicians
    $localQuery = "SELECT id, name FROM politicians WHERE town = '$town' AND distance = 'local'";
    $localPoliticians = fetchQueryResults($conn, $localQuery);

    // Fetch state politicians
    $stateQuery = "SELECT id, name FROM politicians WHERE distance = 'state'";
    $statePoliticians = fetchQueryResults($conn, $stateQuery);

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'local' => $localPoliticians,
        'state' => $statePoliticians
    ]);
    exit;
}

// Handle POST request to fetch politician details based on circleId
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['circleId'])) {
    $circleId = intval($_POST['circleId']);
    $query = "SELECT * FROM politicians WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $circleId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $politicianDetails = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $politicianDetails[] = $row;
        }
    }

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($politicianDetails);
    exit;
}

$conn->close();
?>
