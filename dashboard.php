<?php

session_start();
include("./database/connection.php");

if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}


if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST["edit_profile"])) {
    $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);

    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);

    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    $confirm_password = filter_input(INPUT_POST, "confirm_password", FILTER_SANITIZE_SPECIAL_CHARS);

    $image = "";

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {

        $image = basename($_FILES["image"]["name"]);
        $image_tmp_name = $_FILES["image"]["tmp_name"];
        $image_folder = "./uploads/" . $image;

        if (!move_uploaded_file($image_tmp_name, $image_folder)) {
            $_SESSION["error"] = "Failed to upload";
        }

    } else {

        $_SESSION["error"] = "No upload";
    }

    if (empty($password) && empty($confirm_password)) {

        $edit_admin = mysqli_query($conn, "UPDATE login SET username = '$username', image = '$image' WHERE id = '$id'");

        if ($edit_admin) {
            $_SESSION["success"] = "Edit profile successfully!";
            header("Location: dashboard.php");
            exit();
        }

    } else if ($password != $confirm_password) {

        $_SESSION["error"] = "Password are not the same!";

    } else {

        $edit_admin2 = mysqli_query($conn, "UPDATE admin_login SET , username = '$username', password = '$password_hash', image = '$image' WHERE id = '$id'");

        if ($edit_admin2) {
            $_SESSION["success"] = "Edit profile successfully!";
            header("Location: dashboard.php");
            exit();
        }
    }




}

if (isset($_POST["reset_password"])) {

    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $old_password = filter_input(INPUT_POST, "old_password", FILTER_SANITIZE_SPECIAL_CHARS);
    $new_password = filter_input(INPUT_POST, "new_password", FILTER_SANITIZE_SPECIAL_CHARS);
    $confirm_new_password = filter_input(INPUT_POST, "confirm_new_password", FILTER_SANITIZE_SPECIAL_CHARS);

    $check_email = mysqli_query($conn, "SELECT username FROM login WHERE username = '$username'");

    $old_hash_password = $_SESSION["password"];

    if ($new_password != $confirm_new_password) {

        $_SESSION["error"] = "New password and confirm new password are not the same!";

    } else if (!mysqli_num_rows($check_email) > 0) {

        $_SESSION["error"] = "Username is not found!";

    } else {

        if (password_verify($old_password, $old_hash_password)) {

            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            $reset_password = mysqli_query($conn, "UPDATE login SET password = '$hashed_new_password' WHERE username = '$username'");

            if ($reset_password) {
                $_SESSION["success"] = "Password reset successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION["error"] = "Error updating password. Please try again.";
            }
        } else {
            $_SESSION["error"] = "Incorrect old password!";
        }
    }
}

$yearsQuery = mysqli_query($conn, "SELECT DISTINCT YEAR(date) AS year FROM attendance ORDER BY year DESC");
$years = mysqli_fetch_all($yearsQuery, MYSQLI_ASSOC);

$selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y');

$monthlyOnTimeCounts = array_fill(1, 12, 0);
$monthlyLateCounts = array_fill(1, 12, 0);

for ($month = 1; $month <= 12; $month++) {
    $startDate = date("{$selectedYear}-{$month}-01");
    $endDate = date("{$selectedYear}-{$month}-t");

    // Query to count 'On Time' attendance for the selected year and month
    $onTimeQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM attendance WHERE YEAR(date) = '$selectedYear' AND MONTH(date) = '$month' AND status = 'On Time'");
    $onTimeResult = mysqli_fetch_assoc($onTimeQuery);
    $monthlyOnTimeCounts[$month] = $onTimeResult['count'];

    // Query to count 'Late' attendance for the selected year and month
    $lateQuery = mysqli_query($conn, "SELECT COUNT(*) AS count FROM attendance WHERE YEAR(date) = '$selectedYear' AND MONTH(date) = '$month' AND status = 'Late'");
    $lateResult = mysqli_fetch_assoc($lateQuery);
    $monthlyLateCounts[$month] = $lateResult['count'];
}

// Encode data for JavaScript
$onTimeDataJSON = json_encode(array_values($monthlyOnTimeCounts));
$lateDataJSON = json_encode(array_values($monthlyLateCounts));
$monthsJSON = json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);

$username = $_SESSION["username"];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        .right-bar {
            width: calc(100% - 280px);
            height: 100vh;
            background-color: #FFFCE5;
            margin-left: 280px;
        }

        .right-bar .nav-bar {
            width: 100%;
            height: 70px;
            background-color: #AF834C;
            display: flex;
            justify-content: end;
            align-items: center;
            padding: 0px 30px;
        }

        /* .right-bar .nav-bar .nav-logo h4 {
            color: white;
            margin: 0px 0px 0px;
            font-size: 22px;
        } */

        /* .right-bar .nav-bar .nav-username .image {
            display: flex;
            align-items: center;
            column-gap: 13px;
        }


        .right-bar .nav-bar .nav-username .image img{
            width: 45px;
            height: 45px;
            background-color: #fffffe;
            border-radius: 50%;
        }

        .right-bar .nav-bar .nav-username .image span{
            color: #fffffe;
            font-weight: 600;
            font-size: 17px;
        } */

        .right-bar .nav-bar .nav-logo .fa-bars {
            color: white;
            font-size: 23px;
        }

        .right-bar .nav-bar .nav-logo .dropdown button {
            padding: 0;
            border: none;
            outline: none;
        }

        .right-bar h3 {
            padding: 20px 20px 0px 20px;
            margin: 0px 0px;
            font-weight: bold;
            font-size: 20px;
        }

        .right-bar .parent-box {
            display: flex;
            justify-content: start;
            flex-wrap: wrap;
            padding: 20px;
            gap: 20px;
        }

        .right-bar .parent-box .box-more {
            width: 255px;
            height: 150px;
            border-radius: 5px;
            position: relative;
        }

        .right-bar .parent-box .box-more:nth-child(1) {
            background-color: #D9EDF4;
        }

        .right-bar .parent-box .box-more .more-info {
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
        }


        .right-bar .parent-box .box-more .more-info a {
            color: white;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
        }

        .right-bar .parent-box .box-more .more-info a:hover {
            text-decoration: underline;
        }

        .right-bar .parent-box .box-more:nth-child(2) {
            background-color: #BBF5BB;
        }

        .right-bar .parent-box .box-more:nth-child(3) {
            background-color: #FFCE88;
        }

        .right-bar .parent-box .box-more:nth-child(4) {
            background-color: #FFA5A0;
        }

        .right-bar .parent-box .box-more .more-info1 {
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #AEC6CF;
            border: 1px solid rgba(0, 0, 0, 0.1);
            /* box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px; */
            width: 255px;
            height: 35px;
        }

        .right-bar .parent-box .box-more .more-info2 {
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #77DD77;
            border: 1px solid rgba(0, 0, 0, 0.1);
            /* box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px; */
            width: 255px;
            height: 35px;
        }

        .right-bar .parent-box .box-more .more-info3 {
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #FFB347;
            border: 1px solid rgba(0, 0, 0, 0.1);
            /* box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px; */
            width: 255px;
            height: 35px;
        }

        .right-bar .parent-box .box-more .more-info4 {
            position: absolute;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #FF6961;
            border: 1px solid rgba(0, 0, 0, 0.1);
            /* box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px; */
            width: 255px;
            height: 35px;
        }

        .right-bar .parent-monthly {
            display: flex;
            justify-content: left;
            align-items: left;
            padding: 0px 20px 0px 20px;
        }

        .right-bar .monthly-attendance {
            background-color: #F4EFC6;
            height: 370px;
            width: 1100px;
            border-radius: 5px;
            /* box-shadow: 0px 0px 1px 2px #B0B0B0; */
            border: 1px solid rgba(0, 0, 0, 0.1);
            /* box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px; */
        }

        .right-bar .monthly-attendance h3 {
            font-size: 20px;
            padding: 20px 0px 0px;
        }

        .right-bar .parent-box .box-more .box-text-icon {
            display: flex;
            align-items: center;
            justify-content: space-around;
            margin-top: 1.7rem;
        }

        .right-bar .parent-box .box-more .box-text-icon .box-text p {
            font-size: 30px;
            font-weight: bold;
            margin: 0px 0px 0px;
        }

        .right-bar .parent-box .box-more .box-text-icon .box-icon .fa {
            font-size: 60px;
            color: #232323;
        }

        #attendanceChart {
            padding: 20px;
        }

        .dropdown-menu p {
            margin: 0px 0px 5px;
            font-size: 13px;
            color: #777F87;
        }

        ul.dropdown-menu.show {
            width: 280px;
            height: 115px;
            padding: 10px;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
        }

        .right-bar .nav-bar .nav-logo .fa-bars {
            color: white;
            font-size: 23px;
        }

        .right-bar .nav-bar .nav-logo .dropdown button {
            padding: 0;
            border: none;
            outline: none;
        }

        .right-bar .nav-bar .nav-logo .dropdown .dropdown-menu button {
            padding: 4px 16px;
        }
    </style>
</head>

<body>

    <div class="main">

        <?php
        include("sidebar.php");
        ?>

        <div class="right-bar">

            <div class="nav-bar">
                <div class="nav-logo">

                    <div class="dropdown">
                        <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <p>Account Management</p>
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"><i class="fa fa-user"></i> Profile</button>
                            </li>
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal2"><i class="fa fa-unlock"></i> Reset
                                    Password</button></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Profile -->
            <?php

            $select_admin = mysqli_query($conn, "SELECT * FROM login");

            while ($row = mysqli_fetch_assoc($select_admin)) {

                ?>
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #F4EFC6">
                                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Profile</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="modal-body"
                                    style="background-color: #F4EFC6; display: flex; align-items: center; gap: 10px;">

                                    <div class="image"
                                        style="display: flex; justify-content: center; align-items: center; flex-direction: column; gap: 10px; padding: 10px; width: 270px; height: 332px; box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">

                                        <input type="hidden" name="id" value="<?php echo $row["id"] ?>">

                                        <div class="image-box"
                                            style="width: 200px; height: 200px; box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                            <img src="./uploads/<?php echo $row["image"] ?>" width="200px" height="200px"
                                                style="border-radius: 50%; z-index: 1;">
                                        </div>

                                        <p class="text-center" style="font-size: 19px; margin: 0px 0px 10px;">
                                            <?php echo $row["username"] ?>
                                        </p>

                                        <input type="file" accept="image/png, image/jpg, image/jpeg" name="image"
                                            style="width: 250px; font-size: 15.5px;" required>
                                    </div>

                                    <div class="identity"
                                        style="padding: 10px; width: 500px; box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">
                                        <h4 style="font-size: 20px;">Edit Profile</h4>

                                        <div class="row mt-2">
                                            <div class="col-lg-12">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" class="form-control"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row["username"] ?>">
                                            </div>
                                        </div>

                                        <h4 class="mt-4" style="font-size: 20px; margin: 16px 0px 0px;">Change Password</h4>

                                        <div class="row mt-2">
                                            <div class="col-lg-12">
                                                <label class="form-label">Password <span
                                                        style="font-size: 13px; color: #777F87;">(Leave
                                                        blank
                                                        to keep current password)</span></label>
                                                <input type="password" name="password" class="form-control"
                                                    style="border: 2px solid #D3891F; background: none;">
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-lg-12">
                                                <label class="form-label">Confirm Password <span
                                                        style="font-size: 13px; color: #777F87;">(Leave
                                                        blank
                                                        to keep current password)</span></label>
                                                <input type="password" name="confirm_password" class="form-control"
                                                    style="border: 2px solid #D3891F; background: none;">
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="modal-footer" style="background-color: #F4EFC6">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn" name="edit_profile"
                                        style="background-color: #D3891F; color: white;">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php

            }

            ?>

            <!-- Reset Password -->
            <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #F4EFC6;">
                            <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Reset Password</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="" method="post">
                            <div class="modal-body" style="background-color: #F4EFC6; padding: 19px;">

                                <div class="reset" style="padding: 20px; box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">

                                    <h4 style="font-size: 20px; margin: 0px 0px 8px;">Reset Your Password</h4>
                                    <p style="font-size: 16px; color: #777F87; margin: 0px 0px 16px;">Input your email
                                        to register reset new password.</p>

                                    <div class="row mt-2">
                                        <div class="col-lg-12">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control"
                                                style="border: 2px solid #D3891F; background: none;"
                                                placeholder="Enter username" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <label class="form-label">Old Password</label>
                                            <input type="password" name="old_password" class="form-control"
                                                style="border: 2px solid #D3891F; background: none;"
                                                placeholder="Enter old password" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control"
                                                style="border: 2px solid #D3891F; background: none;"
                                                placeholder="Enter new password" required>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" name="confirm_new_password" class="form-control"
                                                style="border: 2px solid #D3891F; background: none;"
                                                placeholder="Confirm new password" required>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="modal-footer" style="background-color: #F4EFC6;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn" style="background-color: #D3891F; color: white;"
                                    name="reset_password">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <h3>Dashboard</h3>

            <div class="parent-box">
                <div class="box-more">
                    <?php
                    $select_employee = "SELECT * FROM employee";
                    $result = mysqli_query($conn, $select_employee);
                    $total_employee = mysqli_num_rows($result);
                    ?>
                    <div class="box-text-icon">
                        <div class="box-text">
                            <p><?php echo $total_employee ?></p>
                            <label>Total Employee</label>
                        </div>
                        <div class="box-icon">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                    <div class="more-info more-info1">
                        <a href="employee.php">More info <div class="fa fa-arrow-right"></div></a>
                    </div>
                </div>

                <div class="box-more">
                    <?php
                    $select_ontime_today = mysqli_query($conn, "SELECT * FROM attendance WHERE status = 'On Time'");
                    $selec_employee = mysqli_query($conn, "SELECT * FROM employee");

                    $ontime = mysqli_num_rows($select_ontime_today);
                    $employee = mysqli_num_rows($selec_employee);

                    $ontime_percentage = 0;

                    if ($employee > 0) {
                        $result = $ontime / $employee;
                        $ontime_percentage = number_format($result * 100, 2);
                    } else {
                        $ontime_percentage = "0.00";
                    }
                    ?>
                    <div class="box-text-icon">
                        <div class="box-text">
                            <p><?php echo $ontime_percentage; ?></p>
                            <label>On Time Percent</label>
                        </div>
                        <div class="box-icon">
                            <i class="fa fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="more-info more-info2">
                        <a href="attendance.php">More info <div class="fa fa-arrow-right"></div></a>
                    </div>
                </div>

                <div class="box-more">
                    <?php
                    $select_ontime = mysqli_query($conn, "SELECT * FROM attendance WHERE status = 'On Time'");
                    $ontime_today = mysqli_num_rows($select_ontime);
                    ?>
                    <div class="box-text-icon">
                        <div class="box-text">
                            <p><?php echo $ontime_today; ?></p>
                            <label>On Time Today</label>
                        </div>
                        <div class="box-icon">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                    <div class="more-info more-info3">
                        <a href="attendance.php">More info <div class="fa fa-arrow-right"></div></a>
                    </div>
                </div>

                <div class="box-more">
                    <?php
                    $select_late = mysqli_query($conn, "SELECT * FROM attendance WHERE status = 'Late'");
                    $late_today = mysqli_num_rows($select_late);
                    ?>
                    <div class="box-text-icon">
                        <div class="box-text">
                            <p><?php echo $late_today; ?></p>
                            <label>Late Today</label>
                        </div>
                        <div class="box-icon">
                            <i class="fa fa-warning"></i>
                        </div>
                    </div>
                    <div class="more-info more-info4">
                        <a href="attendance.php">More info <div class="fa fa-arrow-right"></div></a>
                    </div>
                </div>
            </div>


            <div class="parent-monthly">
                <div class="monthly-attendance">
                    <div class="parent-monthly d-flex justify-content-between align-items-center">
                        <div class="title-year">
                            <h3>Monthly Attendance Report</h3>
                        </div>
                        <div class="select-year" style="padding: 20px 0px 0px;">
                            <form method="POST">
                                <label for="year" style="font-size: 15px;">Select Year:</label>
                                <select name="year" onchange="this.form.submit()" style="background-color: #AF834C; color: #fffffe; font-size: 15px; border: none; outline: none; padding: 2px 10px;">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?= $year['year'] ?>" <?= $selectedYear == $year['year'] ? 'selected' : '' ?>>
                                            <?= $year['year'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                    <canvas id="attendanceChart" width="380" height="110"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            var onTimeData = JSON.parse('<?= $onTimeDataJSON ?>');
            var lateData = JSON.parse('<?= $lateDataJSON ?>');
            var months = JSON.parse('<?= $monthsJSON ?>');

            var ctx = document.getElementById('attendanceChart').getContext('2d');
            var attendanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'On Time',
                        data: onTimeData,
                        backgroundColor: '#FFB347',
                        borderColor: '#FFB347',
                        borderWidth: 1
                    }, {
                        label: 'Late',
                        data: lateData,
                        backgroundColor: '#FF6961',
                        borderColor: '#FF6961',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>




    <?php

    if (isset($_SESSION["success"])) {
        ?>

        <script>
            swal({
                title: "<?php echo $_SESSION["success"] ?>",
                icon: "success",
                button: "cancel",
            });
        </script>

        <?php
        unset($_SESSION["success"]);

    } else if (isset($_SESSION["error"])) {
        ?>

            <script>
                swal({
                    title: "<?php echo $_SESSION["error"] ?>",
                    icon: "warning",
                    button: "cancel",
                });
            </script>

            <?php
            unset($_SESSION["error"]);
    }

    ?>
</body>

</html>