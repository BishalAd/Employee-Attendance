<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
}

if (!isset($_GET['username'])) {
    header("Location: admin_dashboard.php");
}

$employee_username = $_GET['username'];

// Fetch employee details
$employee_query = "SELECT * FROM users WHERE username='$employee_username' AND role='employee'";
$employee_result = mysqli_query($conn, $employee_query);
$employee = mysqli_fetch_assoc($employee_result);

// Fetch employee attendance records
$attendance_query = "SELECT * FROM attendance WHERE username='$employee_username' ORDER BY date DESC";
$attendance_result = mysqli_query($conn, $attendance_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details - <?php echo $employee['name']; ?></title>
    <link rel="stylesheet" href="Employee_Details_Style.css">
</head>
<body>
    <div class="employee-details-container">
        <header>
        <div id="datetime" style="text-align: center; margin-bottom: 20px; font-weight: bold;"></div>

            <h1>Employee Details - <?php echo $employee['name']; ?></h1>
        </header>
        <main>
            <!-- Attendance Records Section -->
            <section class="attendance-records">
                <h2>Attendance Records</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Reason (if Absent)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($attendance_result)) { ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['status']; ?></td>
                                <td><?php echo $row['time_in']; ?></td>
                                <td><?php echo $row['time_out']; ?></td>
                                <td><?php echo $row['reason']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <!-- Attendance Chart Section -->
            <section class="chart-section">
                <h2>Attendance Overview</h2>
                <div class="chart-container">
                    <canvas id="employeeAttendanceChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <!-- Include external libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dynamic Employee Chart -->
    <script>
    const ctx = document.getElementById('employeeAttendanceChart').getContext('2d');
    const employeeAttendanceChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                label: 'Attendance',
                data: [<?php
                    // Calculate attendance percentages dynamically
                    $present_query = "SELECT COUNT(*) AS total FROM attendance WHERE username='$employee_username' AND status='Present'";
                    $present_result = mysqli_query($conn, $present_query);
                    $present_count = mysqli_fetch_assoc($present_result)['total'];

                    $absent_query = "SELECT COUNT(*) AS total FROM attendance WHERE username='$employee_username' AND status='Absent'";
                    $absent_result = mysqli_query($conn, $absent_query);
                    $absent_count = mysqli_fetch_assoc($absent_result)['total'];

                    echo $present_count . ", " . $absent_count;
                ?>],
                backgroundColor: ['#4CAF50', '#FF6384']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
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
