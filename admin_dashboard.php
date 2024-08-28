<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
}

// Handle adding a new employee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    $query = "INSERT INTO users (username, password, name, role) VALUES ('$username', '$password', '$name', 'employee')";
    mysqli_query($conn, $query);
}

// Handle employee removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_employee'])) {
    $username = $_POST['username'];

    $query = "DELETE FROM users WHERE username='$username'";
    mysqli_query($conn, $query);
}

// Fetch all employees
$employees = mysqli_query($conn, "SELECT * FROM users WHERE role='employee'");

// Fetch total users, holidays, and today's attendance data
$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role='employee'"));
$holidays = mysqli_query($conn, "SELECT * FROM holidays");
$today_attendance = mysqli_query($conn, "SELECT * FROM attendance WHERE date = CURDATE()");

// Fetch messages
$messages = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC");

// Handle marking today as a holiday
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_holiday'])) {
    $query = "INSERT INTO holidays (holiday_date) VALUES (CURDATE())";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Today is marked as a holiday. Employees cannot mark attendance today.');</script>";
    } else {
        echo "<script>alert('Failed to mark today as a holiday.');</script>";
    }
}


// Fetch all employees present today
$present_today = mysqli_query($conn, "SELECT u.name, u.username FROM users u JOIN attendance a ON u.username = a.username WHERE a.date = CURDATE() AND a.status = 'Present'");

// Fetch all employees with their present and absent days
$attendance_summary = mysqli_query($conn, "
    SELECT u.name, u.username, 
           SUM(a.status = 'Present') AS present_days, 
           SUM(a.status = 'Absent') AS absent_days 
    FROM users u 
    LEFT JOIN attendance a ON u.username = a.username 
    WHERE u.role = 'employee' 
    GROUP BY u.username, u.name
");
// Handle deleting a message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];

    $query = "DELETE FROM messages WHERE id='$message_id'";
    mysqli_query($conn, $query);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    
    <div class="admin-container">
        <header>
        <div id="datetime" style="text-align: center; margin-bottom: 20px; font-weight: bold;"></div>

            <h1>Admin Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="#home" class="active" onclick="showSection('home')">Home</a></li>
                    <li><a href="#employees" onclick="showSection('employees')">Employees</a></li>
                    <li><a href="#messages" onclick="showSection('messages')">Messages</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <!-- Home Section -->
            <section id="home" class="dashboard-section active">
                <h2>Dashboard Overview</h2>
                <div class="dashboard-overview">
                    <div class="overview-box">
                        <h3>Total Employees</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                    <div class="overview-box">
                        <h3>Holidays This Month</h3>
                        <p><?php echo mysqli_num_rows($holidays); ?></p>
                    </div>
                    <div class="overview-box">
                        <h3>Today's Attendance</h3>
                        <p><?php echo mysqli_num_rows($today_attendance); ?> Present</p>
                    </div>
                </div>

                <!-- Mark Today as Holiday Button -->
                <form method="POST">
                    <button type="submit" name="mark_holiday" class="mark-holiday-btn">Mark Today as Holiday</button>
                </form>

                <!-- Employees Present Today -->
                <div class="employee-today-section">
                    <h3>Employees Present Today</h3>
                    <table class="employee-today-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Arrival Time</th>
                                <th>Leave Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($present_today)) {
                                // Fetch arrival and leave times
                                $username = $row['username'];
                                $attendance_query = $conn->prepare("SELECT time_in, time_out FROM attendance WHERE username=? AND date=CURDATE()");
                                $attendance_query->bind_param("s", $username);
                                $attendance_query->execute();
                                $attendance_result = $attendance_query->get_result();
                                $attendance = $attendance_result->fetch_assoc();
                            ?>
                                <tr>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $attendance['time_in'] ? $attendance['time_in'] : 'N/A'; ?></td>
                                    <td><?php echo $attendance['time_out'] ? $attendance['time_out'] : 'N/A'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>


                <!-- Employee Attendance Summary -->
                <div class="employee-summary-section">
                    <h3>Employee Attendance Summary</h3>
                    <table class="employee-summary-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Present Days</th>
                                <th>Absent Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($attendance_summary)) { ?>
                                <tr>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['present_days']; ?></td>
                                    <td><?php echo $row['absent_days']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </section>

            <!-- Employee Management Section -->
            <section id="employees" class="dashboard-section">
                <h2>Employee Management</h2>
                <button class="add-employee-btn" onclick="showAddEmployeeForm()">Add Employee</button>

                <div id="add-employee-form" style="display: none;">
                    <form method="POST" class="add-employee-form" onsubmit="return confirm('Are you sure you want to add this employee?');">
                        <input type="text" name="username" placeholder="Employee Username" required>
                        <input type="password" name="password" placeholder="Employee Password" required>
                        <input type="text" name="name" placeholder="Employee Name" required>
                        <button type="submit" name="add_employee">Add Employee</button>
                    </form>
                </div>

                <ul class="employee-list">
                    <?php while ($row = mysqli_fetch_assoc($employees)) { ?>
                        <li class="employee-item">
                            <span><?php echo $row['name']; ?> (<?php echo $row['username']; ?>)</span>
                            <div class="employee-actions">
                                <a href="employee_details.php?username=<?php echo $row['username']; ?>">View Details</a>
                                <a href="edit_employee.php?username=<?php echo $row['username']; ?>">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this employee?');">
                                    <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                                    <button type="submit" name="remove_employee">Remove</button>
                                </form>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </section>


            <!-- Messages Section -->
            <section id="messages" class="dashboard-section">
                <h2>Messages from Employees</h2>
                <ul class="message-list">
                    <?php while ($msg = mysqli_fetch_assoc($messages)) { ?>
                        <li>
                            <strong><?php echo $msg['username']; ?></strong>: <?php echo $msg['message']; ?> (<?php echo $msg['created_at']; ?>)
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="delete_message" class="delete-message-btn">Delete</button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            </section>


            <!-- Logout Button -->
            <a href="logout.php" class="logout-button">Logout</a>
            <!-- Go Back Button -->
            <a href="javascript:void(0);" class="back-button" onclick="showSection('home')">Go Back</a>
        </main>
    </div>

    <!-- Include external JavaScript for tab navigation -->
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.dashboard-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';

            const navLinks = document.querySelectorAll('nav ul li a');
            navLinks.forEach(link => link.classList.remove('active'));
            document.querySelector(`a[href="#${sectionId}"]`).classList.add('active');
        }

        function showAddEmployeeForm() {
            document.getElementById('add-employee-form').style.display = 'block';
        }
        function updateTime() {
        const now = new Date();
        const options = { timeZone: 'Asia/Kathmandu', hour12: false };
        const date = now.toLocaleDateString('en-CA', options);
        const time = now.toLocaleTimeString('en-GB', options);
        document.getElementById('datetime').innerHTML = `<strong>Date:</strong> ${date} | <strong>Current Time:</strong> ${time}`;
    }

    setInterval(updateTime, 1000);
    updateTime();
    </script>
</body>

</html>