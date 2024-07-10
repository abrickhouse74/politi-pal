<?php
// Password protection
$valid_password = "iyana";
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    header('WWW-Authenticate: Basic realm="Admin Interface"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = 'politipaldb-snapshot.c98isu0yixau.us-east-2.rds.amazonaws.com';
$dbname = 'ebdb'; // Ensure this is the correct database name
$user = 'aaron';
$password = 'Aaron10iyana-16';

// Attempt to connect to the database
$conn = new mysqli($host, $user, $password, $dbname, 3306);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if it doesn't exist
$tableCheck = "SHOW TABLES LIKE 'Politicians'";
$result = $conn->query($tableCheck);
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE Politicians (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        town VARCHAR(30) NOT NULL,
        state VARCHAR(30) NOT NULL,
        distance VARCHAR(30),
        title VARCHAR(50),
        link VARCHAR(100)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table Politicians created successfully.";
    } else {
        echo "Error creating table: " . $conn->error;
    }
}

// Handle form submissions for add, update, delete
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $name = $_POST['name'];
        $town = $_POST['town'];
        $state = $_POST['state'];
        $distance = $_POST['distance'];
        $title = $_POST['title'];
        $link = $_POST['link'];

        $sql = "INSERT INTO Politicians (name, town, state, distance, title, link)
                VALUES ('$name', '$town', '$state', '$distance', '$title', '$link')";

        if ($conn->query($sql) === TRUE) {
            $message = "New record created successfully.";
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $town = $_POST['town'];
        $state = $_POST['state'];
        $distance = $_POST['distance'];
        $title = $_POST['title'];
        $link = $_POST['link'];

        $sql = "UPDATE Politicians SET name='$name', town='$town', state='$state', distance='$distance', title='$title', link='$link' WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            $message = "Record updated successfully.";
        } else {
            $message = "Error updating record: " . $conn->error;
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        $sql = "DELETE FROM Politicians WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            $message = "Record deleted successfully.";
        } else {
            $message = "Error deleting record: " . $conn->error;
        }
    }
}

// Fetch all records
$sql = "SELECT * FROM Politicians";
$result = $conn->query($sql);
$politicians = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $politicians[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .form-container, .table-container {
            max-width: 800px;
            margin: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="submit"], select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .button {
            background-color: blue;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
    <script>
        function editRecord(id, name, town, state, distance, title, link) {
            document.getElementById('id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('town').value = town;
            document.getElementById('state').value = state;
            document.getElementById('distance').value = distance;
            document.getElementById('title').value = title;
            document.getElementById('link').value = link;
        }
    </script>
</head>
<body>

<div class="form-container">
    <h1>Admin Interface</h1>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="id">ID (for update/delete):</label>
            <input type="text" id="id" name="id" readonly>
        </div>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="town">Town:</label>
            <input type="text" id="town" name="town" required>
        </div>
        <div class="form-group">
            <label for="state">State:</label>
            <input type="text" id="state" name="state" required>
        </div>
        <div class="form-group">
            <label for="distance">Distance:</label>
            <input type="text" id="distance" name="distance">
        </div>
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title">
        </div>
        <div class="form-group">
            <label for="link">Link:</label>
            <input type="text" id="link" name="link">
        </div>
        <input type="submit" name="add" value="Add" class="button">
        <input type="submit" name="update" value="Update" class="button">
        <input type="submit" name="delete" value="Delete" class="button">
    </form>
</div>

<div class="table-container">
    <h2>Politicians List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Town</th>
            <th>State</th>
            <th>Distance</th>
            <th>Title</th>
            <th>Link</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($politicians as $politician): ?>
        <tr>
            <td><?php echo $politician['id']; ?></td>
            <td><?php echo $politician['name']; ?></td>
            <td><?php echo $politician['town']; ?></td>
            <td><?php echo $politician['state']; ?></td>
            <td><?php echo $politician['distance']; ?></td>
            <td><?php echo $politician['title']; ?></td>
            <td><?php echo $politician['link']; ?></td>
            <td>
                <button class="button" onclick="editRecord('<?php echo $politician['id']; ?>', '<?php echo $politician['name']; ?>', '<?php echo $politician['town']; ?>', '<?php echo $politician['state']; ?>', '<?php echo $politician['distance']; ?>', '<?php echo $politician['title']; ?>', '<?php echo $politician['link']; ?>')">Edit</button>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $politician['id']; ?>">
                    <input type="submit" name="delete" value="Delete" class="button">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
