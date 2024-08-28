<?php
include 'db.php';

$current_date = date("Y-m-d");
$current_time = date("H:i:s");
$current_day_of_week = date("l"); // Get the current day of the week

// Check if today is a holiday
$holiday_query = $conn->prepare("SELECT * FROM holidays WHERE date=?");
$holiday_query->bind_param("s", $current_date);
$holiday_query->execute();
$holiday_result = $holiday_query->get_result();
$is_holiday = $holiday_result->num_rows > 0;
$is_saturday = ($current_day_of_week == 'Saturday');

// Fetch all employees
$employees_query = $conn->prepare("SELECT username FROM users WHERE role='employee'");
$employees_query->execute();
$employees_result = $employees_query->get_result();

$check_query = $conn->prepare("SELECT * FROM attendance WHERE username=? AND date=?");
$insert_query = $conn->prepare("INSERT INTO attendance (username, date, status) VALUES (?, ?, 'Absent')");

// Mark absent for employees who haven't marked attendance by 2 PM
while ($employee = $employees_result->fetch_assoc()) {
    $username = $employee['username'];

    // Check if attendance is already marked
    $check_query->bind_param("ss", $username, $current_date);
    $check_query->execute();
    $check_result = $check_query->get_result();
    $attendance = $check_result->fetch_assoc();

    // Only mark as absent if not a holiday, not a Saturday, and time is after 2 PM
    if ($check_result->num_rows == 0 && !$is_holiday && !$is_saturday && $current_time >= '14:00:00') {
        $insert_query->bind_param("ss", $username, $current_date);
        $insert_query->execute();
    }
}

$holiday_query->close();
$check_query->close();
$insert_query->close();
$conn->close();
?>
