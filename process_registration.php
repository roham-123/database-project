<?php
include_once("utilities.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get form data from POST request
$accountType = $_POST['accountType'];
$email = $_POST['email'];
$password = $_POST['password'];
$passwordConfirmation = $_POST['passwordConfirmation'];
$username = $_POST['username']; // Get the username from the form

// Check if the passwords match
if ($password !== $passwordConfirmation) {
    die("Passwords do not match. Please go back and try again.");
}

// Hash the password for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare SQL query to insert user data into the Users table
$stmt = $conn->prepare("INSERT INTO Users (Name, Email, Password, Role, Username) VALUES (?, ?, ?, ?, ?)");

// Bind the parameters, using username for both Name and Username fields
$stmt->bind_param("sssss", $username, $email, $hashedPassword, $accountType, $username);

// Execute the query and check if it was successful
if ($stmt->execute()) {
    echo "Registration successful. You can now <a href=\"index.php\" data-toggle=\"modal\" data-target=\"#loginModal\">log in</a>.";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
closeConnection($conn);
?>