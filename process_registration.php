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

// Check if the email is already registered
$emailCheckStmt = $conn->prepare("SELECT UserID FROM Users WHERE Email = ?");
$emailCheckStmt->bind_param("s", $email);
$emailCheckStmt->execute();
$emailCheckResult = $emailCheckStmt->get_result();

if ($emailCheckResult->num_rows > 0) {
    // Email already exists
    echo "This email is already registered. Please use a different email or log in.";
    $emailCheckStmt->close();
} else {
    // Prepare SQL query to insert user data into the Users table
    $stmt = $conn->prepare("INSERT INTO Users (Username, Email, Password, Role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $accountType);

    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        echo "Registration successful. You can now <a href=\"index.php\" data-toggle=\"modal\" data-target=\"#loginModal\">log in</a>.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the email check statement and connection
$emailCheckStmt->close();
closeConnection($conn);
?>