<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

</html>

<?php
// ... (inside your while loop)

$morning_hours = '00:00'; // Default if no morning times
$afternoon_hours = '00:00'; // Default if no afternoon times

// Calculate morning hours if available
if ($row2["timein_morning"] && $row2["first_timeout"]) {
    $timein_morning = new DateTime($row2["timein_morning"]);
    $first_timeout = new DateTime($row2["first_timeout"]);
    $interval_morning = $timein_morning->diff($first_timeout);
    $morning_hours = $interval_morning->format('%H:%I');
}

// Calculate afternoon hours if available
if ($row2["timein_afternoon"] && $row2["second_timeout"]) {
    $timein_afternoon = new DateTime($row2["timein_afternoon"]);
    $second_timeout = new DateTime($row2["second_timeout"]);
    $interval_afternoon = $timein_afternoon->diff($second_timeout);
    $afternoon_hours = $interval_afternoon->format('%H:%I');
}

// Calculate total hours
$time_morning = DateTime::createFromFormat('H:i', $morning_hours);
$time_afternoon = DateTime::createFromFormat('H:i', $afternoon_hours);

if ($time_morning && $time_afternoon) {
    $total_time = new DateTime();
    $total_time->setTime(0, 0);

    $total_time->add(new DateInterval('PT' . $time_morning->format('H') . 'H' . $time_morning->format('i') . 'M'));
    $total_time->add(new DateInterval('PT' . $time_afternoon->format('H') . 'H' . $time_afternoon->format('i') . 'M'));

    $total_hours = $total_time->format('H:i');
} else {
    $total_hours = '--:--'; // If something went wrong, show '--:--'
}
?>