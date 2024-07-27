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
        $fields = array();
        $values = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'add' && $key != 'id') {
                $fields[] = $key;
                $values[] = "'" . $conn->real_escape_string($value) . "'";
            }
        }
        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT INTO Politicians ($fields) VALUES ($values)";

        if ($conn->query($sql) === TRUE) {
            $message = "New record created successfully.";
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $updates = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'update' && $key != 'id') {
                $updates[] = "$key='" . $conn->real_escape_string($value) . "'";
            }
        }
        $updates = implode(',', $updates);

        $sql = "UPDATE Politicians SET $updates WHERE id='$id'";

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
    } elseif (isset($_POST['add_field'])) {
        $newField = $_POST['new_field'];

        $sql = "ALTER TABLE Politicians ADD $newField VARCHAR(255)";

        if ($conn->query($sql) === TRUE) {
            $message = "New field '$newField' added successfully.";
        } else {
            $message = "Error adding new field: " . $conn->error;
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

// Fetch all fields
$columns = [];
$columnsResult = $conn->query("SHOW COLUMNS FROM Politicians");
if ($columnsResult->num_rows > 0) {
    while($row = $columnsResult->fetch_assoc()) {
        $columns[] = $row['Field'];
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
        input[type="text"], select {
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
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .add-button {
            background-color: blue;
            color: white;
        }
        .update-button {
            background-color: green;
            color: white;
        }
        .delete-button {
            background-color: red;
            color: white;
        }
        .add-field-button {
            background-color: purple;
            color: white;
            display: block;
            margin: 20px auto;
            width: fit-content;
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
            <?php foreach ($columns as $column): ?>
            <?php if ($column != 'id' && $column != 'name' && $column != 'town' && $column != 'state' && $column != 'distance' && $column != 'title' && $column != 'link'): ?>
            document.getElementById('<?php echo $column; ?>').value = arguments[<?php echo array_search($column, $columns); ?>];
            <?php endif; ?>
            <?php endforeach; ?>
        }

        function addField() {
            var fieldName = prompt("Enter the name of the new field:");
            if (fieldName) {
                var form = document.createElement("form");
                form.method = "POST";
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "new_field";
                input.value = fieldName;
                form.appendChild(input);
                var addFieldInput = document.createElement("input");
                addFieldInput.type = "hidden";
                addFieldInput.name = "add_field";
                form.appendChild(addFieldInput);
                document.body.appendChild(form);
                form.submit();
            }
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
        <?php foreach ($columns as $column): ?>
        <?php if ($column != 'id'): ?>
        <div class="form-group">
            <label for="<?php echo $column; ?>"><?php echo ucfirst($column); ?>:</label>
            <input type="text" id="<?php echo $column; ?>" name="<?php echo $column; ?>" required>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <input type="submit" name="add" value="Add" class="button add-button">
        <input type="submit" name="update" value="Update" class="button update-button">
        <input type="submit" name="delete" value="Delete" class="button delete-button">
    </form>
    <button class="button add-field-button" onclick="addField()">Add Field</button>
</div>

<div class="table-container">
    <h2>Politicians List</h2>
    <table>
        <tr>
            <?php foreach ($columns as $column): ?>
            <th><?php echo ucfirst($column); ?></th>
            <?php endforeach; ?>
            <th>Actions</th>
        </tr>
        <?php foreach ($politicians as $politician): ?>
        <tr>
            <?php foreach ($columns as $column): ?>
            <td><?php echo htmlspecialchars($politician[$column]); ?></td>
            <?php endforeach; ?>
            <td>
                <button class="button" onclick="editRecord(
                    '<?php echo htmlspecialchars($politician['id']); ?>',
                    '<?php echo htmlspecialchars($politician['name']); ?>',
                    '<?php echo htmlspecialchars($politician['town']); ?>',
                    '<?php echo htmlspecialchars($politician['state']); ?>',
                    '<?php echo htmlspecialchars($politician['distance']); ?>',
                    '<?php echo htmlspecialchars($politician['title']); ?>',
                    '<?php echo htmlspecialchars($politician['link']); ?>'
                    <?php foreach ($columns as $column): ?>
                    <?php if ($column != 'id' && $column != 'name' && $column != 'town' && $column != 'state' && $column != 'distance' && $column != 'title' && $column != 'link'): ?>
                    , '<?php echo htmlspecialchars($politician[$column]); ?>'
                    <?php endif; ?>
                    <?php endforeach; ?>
                )">Edit</button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $politician['id']; ?>">
                    <input type="submit" name="delete" value="Delete" class="button delete-button">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
