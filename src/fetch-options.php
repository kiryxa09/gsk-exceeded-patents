<?php
// Connect to your MySQL database
$servername = 'your_servername';
$username = 'your_username';
$password = 'your_password';
$dbname = 'your_database';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the options based on the search query
$searchQuery = $_GET['searchQuery'];

$sql = "SELECT option FROM options_table WHERE option LIKE '%$searchQuery%'";
$result = $conn->query($sql);

$options = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options[] = $row['option'];
    }
}

// Close the database connection
$conn->close();

// Return the options as a JSON response
header('Content-Type: application/json');
echo json_encode($options);
?>