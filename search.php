<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = 'politipaldb-snapshot.c98isu0yixau.us-east-2.rds.amazonaws.com';
$dbname = 'ebdb';
$user = 'aaron';
$password = 'Aaron10iyana-16';

// Attempt to connect to the database
$conn = new mysqli($host, $user, $password, $dbname, 3306);

// Check if the connection was successful
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Function to execute a query and fetch results
function fetchQueryResults($conn, $query) {
    error_log("Executing query: " . $query); // Log the query
    $result = $conn->query($query);
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        die("Query failed: " . $conn->error);
    }
    $resultsArray = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $resultsArray[] = $row;
        }
    }
    error_log("Query results: " . print_r($resultsArray, true)); // Log the results
    return $resultsArray;
}

// Handle GET request to fetch politician names for small circles by town
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['town'])) {
    $town = $conn->real_escape_string($_GET['town']);
    
    // Fetch local politicians
    $localQuery = "SELECT id, name, party FROM Politicians WHERE town = '$town' AND LOWER(distance) = 'local'";
    $localPoliticians = fetchQueryResults($conn, $localQuery);

    // Fetch state politicians
    $stateQuery = "SELECT id, name, party FROM Politicians WHERE LOWER(distance) = 'state'";
    $statePoliticians = fetchQueryResults($conn, $stateQuery);

    // Fetch federal politicians
    $federalQuery = "SELECT id, name, party FROM Politicians WHERE LOWER(distance) = 'federal'";
    $federalPoliticians = fetchQueryResults($conn, $federalQuery);

    // Debug output
    error_log("Local Politicians: " . print_r($localPoliticians, true));
    error_log("State Politicians: " . print_r($statePoliticians, true));
    error_log("Federal Politicians: " . print_r($federalPoliticians, true));

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'local' => $localPoliticians,
        'state' => $statePoliticians,
        'federal' => $federalPoliticians
    ]);
    exit;
}

// Handle GET request to fetch valid towns from the database
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchTowns'])) {
    $query = "SELECT DISTINCT town FROM Politicians";
    $result = $conn->query($query);
    $towns = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $towns[] = $row['town'];
        }
    }
    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($towns);
    exit;
}

// Handle POST request to fetch politician details based on circleId
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['circleId'])) {
    $circleId = intval($_POST['circleId']);
    $query = "SELECT * FROM Politicians WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $circleId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $politicianDetails = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $politicianDetails[] = $row;
        }
    }

    // Debug output
    error_log("Politician Details: " . print_r($politicianDetails, true));

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($politicianDetails);
    exit;
}

// Handle POST request for general search by name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generalSearch'])) {
    $generalSearch = $conn->real_escape_string($_POST['generalSearch']);
    $query = "SELECT * FROM Politicians WHERE LOWER(name) LIKE LOWER(?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Prepare failed: " . $conn->error);
    }
    $searchTerm = '%' . $generalSearch . '%';
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $searchResults = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $searchResults[] = $row;
        }
    }

    // Debug output
    error_log("General Search Results: " . print_r($searchResults, true));

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($searchResults);
    exit;
}

$conn->close();
?>
