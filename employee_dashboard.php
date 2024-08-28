<?php
session_start();
include 'db.php';

// Redirect if not logged in or not an employee
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'employee') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch employee name using a prepared statement
$query = $conn->prepare("SELECT name FROM users WHERE username=?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();
$employee_name = $row['name'];

// Check if today is marked as a holiday
$today = date("Y-m-d");
$holiday_query = $conn->prepare("SELECT * FROM holidays WHERE holiday_date = ?");
$holiday_query->bind_param("s", $today);
$holiday_query->execute();
$is_holiday = $holiday_query->get_result()->num_rows > 0;

// Handle attendance actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($is_holiday) {
        echo "<script>alert('Today is a holiday. Attendance cannot be marked.');</script>";
    } else {
        if (isset($_POST['attendance_action'])) {
            $action = $_POST['attendance_action'];
            $date = date("Y-m-d");
            $time = date("H:i:s");

            // Check if attendance is already marked for today
            $check_query = $conn->prepare("SELECT * FROM attendance WHERE username=? AND date=?");
            $check_query->bind_param("ss", $username, $date);
            $check_query->execute();
            $check_result = $check_query->get_result();
            $attendance_exists = $check_result->fetch_assoc();

            if ($action == "Present") {
                if ($attendance_exists) {
                    echo "<script>alert('Attendance already marked for today!');</script>";
                } else {
                    $insert_query = $conn->prepare("INSERT INTO attendance (username, date, status, time_in) VALUES (?, ?, 'Present', ?)");
                    $insert_query->bind_param("sss", $username, $date, $time);
                    $insert_query->execute();
                    echo "<script>alert('Present marked successfully!');</script>";
                }
            } elseif ($action == "Leave") {
                if ($attendance_exists && $attendance_exists['status'] == 'Present') {
                    $update_query = $conn->prepare("UPDATE attendance SET time_out=? WHERE username=? AND date=?");
                    $update_query->bind_param("sss", $time, $username, $date);
                    $update_query->execute();
                    echo "<script>alert('Leave marked successfully!');</script>";
                } else {
                    echo "<script>alert('You need to mark Present before leaving!');</script>";
                }
            } elseif ($action == "Absent") {
                if ($attendance_exists) {
                    if ($attendance_exists['status'] == 'Present') {
                        echo "<script>alert('Cannot mark Absent after marking Present!');</script>";
                    } elseif ($attendance_exists['status'] == 'Absent') {
                        echo "<script>alert('You have already marked Absent today!');</script>";
                    }
                } else {
                    $reason = $_POST['reason'];
                    $insert_query = $conn->prepare("INSERT INTO attendance (username, date, status, reason) VALUES (?, ?, 'Absent', ?)");
                    $insert_query->bind_param("sss", $username, $date, $reason);
                    $insert_query->execute();
                    echo "<script>alert('Absent marked successfully!');</script>";
                }
            }
        }
    }


    // Handle sending message
    if (isset($_POST['message'])) {
        $message = $_POST['message'];
        $created_at = date('Y-m-d H:i:s');

        $insert_message_query = $conn->prepare("INSERT INTO messages (username, message, created_at) VALUES (?, ?, ?)");
        $insert_message_query->bind_param("sss", $username, $message, $created_at);
        $insert_message_query->execute();
        echo "<script>alert('Message sent successfully!');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="Emp_Style.css">

    <script>
        function showAbsentSection() {
            document.querySelector('.absent-section').style.display = 'block';
        }

        function hideAbsentSection() {
            document.querySelector('.absent-section').style.display = 'none';
        }

        function updateTime() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Kathmandu',
                hour12: false
            };
            const date = now.toLocaleDateString('en-CA', options);
            const time = now.toLocaleTimeString('en-GB', options);
            document.getElementById('datetime').innerHTML = `<strong>Date:</strong> ${date} | <strong>Current Time:</strong> ${time}`;
        }

        setInterval(updateTime, 1000);
        updateTime();
    </script>
</head>

<body>

    <div class="dashboard-container">
        <div id="datetime" style="text-align: center; margin-bottom: 20px; font-weight: bold;"></div>

        <h2>Welcome, <?php echo htmlspecialchars($employee_name); ?></h2>



        <!-- Attendance Section -->
        <div class="attendance-section">
            <h3>Mark Attendance</h3>
            <p id="location-status" style="color: red;">Checking your location...</p>
            <form method="POST">
                <button id="presentButton" type="submit" name="attendance_action" value="Present" disabled>Present</button>
                <button type="submit" name="attendance_action" value="Leave" onclick="hideAbsentSection();">Leave</button>
                <button type="button" onclick="showAbsentSection();">Absent</button>
                <div class="absent-section">
                    <input type="text" name="reason" placeholder="Reason (optional)">
                    <button type="submit" name="attendance_action" value="Absent">Submit</button>
                </div>
            </form>
        </div>

        <!-- Message Section -->
        <div class="message-section">
            <h3>Send Message to Admin</h3>
            <form method="POST">
                <textarea name="message" rows="4" placeholder="Write your message here..." style="width:100%; padding:8px; border-radius:5px; border:1px solid #ccc;"></textarea>
                <button type="submit" style="margin-top:10px;">Send Message</button>
            </form>
        </div>
        <div class="logout-section">
            <form method="POST" action="logout.php">
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const presentButton = document.getElementById("presentButton");
            const locationStatus = document.getElementById("location-status");

            // Target location
            const targetLatitude = 28.184099;
            const targetLongitude = 84.0491707;
            const locationAccuracy = 0.0005; // Adjust this for required accuracy (in degrees)

            // Function to calculate the distance between two points using the Haversine formula
            function areCoordinatesClose(lat1, lon1, lat2, lon2, accuracy) {
                const distance = Math.sqrt(Math.pow(lat2 - lat1, 2) + Math.pow(lon2 - lon1, 2));
                return distance < accuracy;
            }

            // Get the user's current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLatitude = position.coords.latitude;
                        const userLongitude = position.coords.longitude;

                        if (areCoordinatesClose(userLatitude, userLongitude, targetLatitude, targetLongitude, locationAccuracy)) {
                            presentButton.disabled = false;
                            locationStatus.textContent = "You are in the correct location!";
                            locationStatus.style.color = "green";
                        } else {
                            locationStatus.textContent = "You are not in the correct location.";
                        }
                    },
                    function(error) {
                        locationStatus.textContent = "Unable to retrieve your location.";
                    }
                );
            } else {
                locationStatus.textContent = "Geolocation is not supported by your browser.";
            }
        });
    </script>
</body>

</html>