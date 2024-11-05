<?php
session_start();

// Include database connection file
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $cadet_rank = $_POST['cadet_rank']; // New field for cadet rank
    $date_of_birth = $_POST['date_of_birth']; // New field for date of birth
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $emergency_contact_phone = $_POST['emergency_contact_phone'];
    $emergency_contact_relation = $_POST['emergency_contact_relation'];
    $medical_history = $_POST['medical_history'];
    $profile_picture = null;

    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validate file type and size
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions) && $fileSize < 2000000) { // 2MB limit
            // Move the file to the desired directory
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . uniqid() . '.' . $fileExtension;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profile_picture = $dest_path;
            } else {
                $errorMessage = 'There was an error uploading the profile picture.';
            }
        } else {
            $errorMessage = 'Invalid file type or file too large. Only JPG, GIF, PNG are allowed with a maximum size of 2MB.';
        }
    }

    // Check if username already exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = "Username already exists. Please choose a different username.";
    } else {
        // Insert user data into the users table
        $query = "INSERT INTO users (username, password, role, profile_picture) 
                  VALUES (?, ?, 'cadet', ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $username, $password, $profile_picture);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id; // Get the inserted user ID

            // Insert cadet details into the cadets table
            $query = "INSERT INTO cadets (user_id, first_name, last_name, cadet_rank, date_of_birth, phone, address, emergency_contact_name, emergency_contact_phone, emergency_contact_relation, medical_history) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssssssss", $user_id, $first_name, $last_name, $cadet_rank, $date_of_birth, $phone, $address, $emergency_contact_name, $emergency_contact_phone, $emergency_contact_relation, $medical_history);

            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("Location: profile.php"); // Redirect to profile page after successful registration
                exit;
            } else {
                $errorMessage = "Error saving cadet details: " . $stmt->error;
            }
        } else {
            $errorMessage = "Error saving user data: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Register</h2>
        <?php if (isset($errorMessage)) echo "<div class='alert alert-danger'>$errorMessage</div>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="cadet_rank">Cadet Rank</label>
                <input type="text" class="form-control" id="cadet_rank" name="cadet_rank" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="emergency_contact_name">Emergency Contact Name</label>
                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" required>
            </div>
            <div class="form-group">
                <label for="emergency_contact_phone">Emergency Contact Phone</label>
                <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" required>
            </div>
            <div class="form-group">
                <label for="emergency_contact_relation">Relation</label>
                <input type="text" class="form-control" id="emergency_contact_relation" name="emergency_contact_relation" required>
            </div>
            <div class="form-group">
                <label for="medical_history">Medical History</label>
                <textarea class="form-control" id="medical_history" name="medical_history" required></textarea>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
