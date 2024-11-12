<?php
include_once("utilities.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get form data from POST request
$email = $_POST['email'];
$password = $_POST['password'];

// Prepare SQL query to fetch user with the provided email
$stmt = $conn->prepare("SELECT UserID, Username, Name, Password, Role FROM Users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify the provided password with the hashed password in the database
    if (password_verify($password, $user['Password'])) {
        // Set session variables
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['Username'] = $user['Username'];
        $_SESSION['Name'] = $user['Name'];
        $_SESSION['Role'] = $user['Role'];
        $_SESSION['logged_in'] = true;  // Set the logged_in session variable
        $_SESSION['Email'] = $email;
        

        // Redirect to the homepage or dashboard
        header("Location: index.php");
        exit();
    } else {
        echo "Incorrect password. Please try again.";
    }
} else {
    echo "No account found with that email address. Please register first.";
}

// Close the statement and connection
$stmt->close();
closeConnection($conn);
?>

