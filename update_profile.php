<?php
session_start();

// Include database connection file
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Get the logged-in user's ID
$username = $_SESSION['username'];
$userQuery = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$userId = $userData['id'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $cadetRank = $_POST['cadet_rank'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $emergencyContactName = $_POST['emergency_contact_name'];
    $emergencyContactPhone = $_POST['emergency_contact_phone'];
    $emergencyContactRelation = $_POST['emergency_contact_relation'];
    $medicalHistory = $_POST['medical_history'];

    // Update cadet information in the database
    $updateQuery = "UPDATE cadets SET first_name = ?, last_name = ?, cadet_rank = ?, phone = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ?, emergency_contact_relation = ?, medical_history = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssssssis", $firstName, $lastName, $cadetRank, $phone, $address, $emergencyContactName, $emergencyContactPhone, $emergencyContactRelation, $medicalHistory, $userId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update profile. Please try again.";
    }
    // Redirect back to profile page
    header("Location: profile.php");
    exit();
}
?>
