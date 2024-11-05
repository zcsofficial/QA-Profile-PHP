<?php
session_start();

// Include database connection file
include('db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Fetch user data from users table
$userQuery = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();

// Fetch cadet data from cadets table
$cadetQuery = "SELECT * FROM cadets WHERE user_id = ?";
$stmt = $conn->prepare($cadetQuery);
$stmt->bind_param("i", $userData['id']);
$stmt->execute();
$cadetResult = $stmt->get_result();
$cadetData = $cadetResult->fetch_assoc();

// Display messages if any
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        .profile-header {
            background: #007bff;
            color: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: left;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .profile-header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            margin-left: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .card-header {
            background: #343a40;
            color: #fff;
            font-size: 1.25rem;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: bold;
            margin-left: 10px;
        }
        @media (max-width: 768px) {
            .profile-header h1 {
                font-size: 2rem;
            }
            .profile-image {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <div>
                <h1>User Profile <i class="fas fa-user-circle"></i></h1>
                <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                <button class="btn btn-warning mt-2" data-toggle="modal" data-target="#editProfileModal">Edit Profile <i class="fas fa-edit"></i></button>
                <a href="logout.php" class="btn logout-btn mt-2">Logout <i class="fas fa-sign-out-alt"></i></a>
            </div>
            <img src="<?php echo htmlspecialchars($userData['profile_picture']); ?>" alt="Profile Picture" class="profile-image"> <!-- Display user profile picture -->
        </div>

        <div class="row mt-5">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        Cadet Information
                    </div>
                    <div class="card-body">
                        <p><strong>First Name:</strong> <?php echo htmlspecialchars($cadetData['first_name']); ?></p>
                        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($cadetData['last_name']); ?></p>
                        <p><strong>Rank:</strong> <?php echo htmlspecialchars($cadetData['cadet_rank']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($cadetData['date_of_birth']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($cadetData['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($cadetData['address']); ?></p>
                        <p><strong>Emergency Contact Name:</strong> <?php echo htmlspecialchars($cadetData['emergency_contact_name']); ?></p>
                        <p><strong>Emergency Contact Phone:</strong> <?php echo htmlspecialchars($cadetData['emergency_contact_phone']); ?></p>
                        <p><strong>Relation:</strong> <?php echo htmlspecialchars($cadetData['emergency_contact_relation']); ?></p>
                        <p><strong>Medical History:</strong> <?php echo htmlspecialchars($cadetData['medical_history']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        Attendance Status
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch attendance records for the logged-in user
                        $attendanceQuery = "SELECT events.event_name, events.event_date, attendance.status 
                                            FROM attendance 
                                            JOIN events ON attendance.event_id = events.id 
                                            WHERE attendance.cadet_id = ?";
                        $stmt = $conn->prepare($attendanceQuery);
                        $stmt->bind_param("i", $cadetData['id']);
                        $stmt->execute();
                        $attendanceResult = $stmt->get_result();

                        if ($attendanceResult->num_rows > 0) {
                            echo "<table class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th>Event Name</th>
                                            <th>Event Date</th>
                                            <th>Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                            while ($row = $attendanceResult->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['event_name']) . "</td>
                                        <td>" . htmlspecialchars($row['event_date']) . "</td>
                                        <td style='color:" . ($row['status'] == 'present' ? 'green' : 'red') . "'>" . htmlspecialchars($row['status']) . "</td>
                                      </tr>";
                            }
                            echo "</tbody></table>";
                        } else {
                            echo "<p>No attendance records found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Edit Profile Modal -->
   <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" method="POST" action="update_profile.php">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($cadetData['first_name']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($cadetData['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="cadet_rank">Rank</label>
                                <input type="text" class="form-control" id="cadet_rank" name="cadet_rank" value="<?php echo htmlspecialchars($cadetData['cadet_rank']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($cadetData['phone']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($cadetData['address']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo htmlspecialchars($cadetData['emergency_contact_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_phone">Emergency Contact Phone</label>
                            <input type="text" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo htmlspecialchars($cadetData['emergency_contact_phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="emergency_contact_relation">Relation</label>
                            <input type="text" class="form-control" id="emergency_contact_relation" name="emergency_contact_relation" value="<?php echo htmlspecialchars($cadetData['emergency_contact_relation']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="medical_history">Medical History</label>
                            <textarea class="form-control" id="medical_history" name="medical_history" rows="3"><?php echo htmlspecialchars($cadetData['medical_history']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and jQuery scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
