<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Database connection details
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "mb_edu_app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error, 3, 'error.log');
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
$sessionToken = $input['sessionToken'] ?? '';
$courseName = $input['courseName'] ?? '';

// Log the received data
error_log("Received sessionToken: " . var_export($sessionToken, true), 3, 'error.log');
error_log("Received courseName: " . var_export($courseName, true), 3, 'error.log');

// Validate input
if (empty($sessionToken) || empty($courseName)) {
    $errorMessage = sprintf(
        "Invalid input: sessionToken - %s, courseName - %s",
        var_export($sessionToken, true),
        var_export($courseName, true)
    );
    error_log($errorMessage, 3, 'error.log');
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Delete the course from the user's schedule using sessionToken as id
$stmt = $conn->prepare("DELETE FROM user_courses WHERE id = ? AND name = ?");
$stmt->bind_param("ss", $sessionToken, $courseName);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Course dropped successfully']);
} else {
    $error_message = $stmt->error;
    error_log("Failed to drop course: " . $error_message, 3, 'error.log');
    echo json_encode(['success' => false, 'message' => 'Failed to drop course', 'error' => $error_message]);
}

$stmt->close();
$conn->close();
?>