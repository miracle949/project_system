<?php

session_start();
include("./database/connection.php");

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION["username"])) {
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

    } else if(!mysqli_num_rows($check_email) > 0) {

        $_SESSION["error"] = "Username is not found!";
        
    }else{

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



if (isset($_POST["submit"])) {

    $employee_name = filter_input(INPUT_POST, "employee_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, "date", FILTER_SANITIZE_SPECIAL_CHARS);
    $timein_raw = filter_input(INPUT_POST, "timein", FILTER_SANITIZE_SPECIAL_CHARS);
    $timeout_raw = filter_input(INPUT_POST, "timeout", FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_SPECIAL_CHARS);
    $total_hours = null;

    // Calculate total hours if both time in and time out are provided
    if (!empty($timein_raw) && !empty($timeout_raw)) {
        try {
            // Assuming your time input is in HH:MM:SS format
            $timein_dt = new DateTime($timein_raw);
            $timeout_dt = new DateTime($timeout_raw);

            $interval = $timein_dt->diff($timeout_dt);

            // Format total hours as HH:MM:SS
            $total_hours = $interval->format('%H:%i:%s');

        } catch (Exception $e) {
            // Handle invalid time format or other errors
            error_log("Error calculating total hours: " . $e->getMessage());
            $_SESSION["error"] = "Invalid time format";
            header("Location: attendance.php");
            exit();
        }
    }

    $insert_attendance = mysqli_query($conn, "INSERT INTO attendance (employee_name, date, time_in, time_out, total_hours,status) VALUES ('$employee_name', '$date', '$timein_raw', '$timeout_raw', '$total_hours','$status')");

    if ($insert_attendance) {
        $_SESSION["success"] = "Attendance successfully recorded";
        header("Location: attendance.php");
        exit();
    } else {
        $_SESSION["error"] = "Error recording attendance: " . mysqli_error($conn);
        header("Location: attendance.php");
        exit();
    }

} else {
    unset($_SESSION["message"]);
    unset($_SESSION["error"]);
}

if (isset($_POST["edit_attendance"])) {

    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_SPECIAL_CHARS);
    $employee_name = filter_input(INPUT_POST, "employee_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, "date", FILTER_SANITIZE_SPECIAL_CHARS);
    $timein_raw = filter_input(INPUT_POST, "timein", FILTER_SANITIZE_SPECIAL_CHARS);
    $timeout_raw = filter_input(INPUT_POST, "timeout", FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_SPECIAL_CHARS);
    $total_hours = null; // Initialize to null

    // Calculate total hours if both time in and time out are provided
    if (!empty($timein_raw) && !empty($timeout_raw)) {
        try {
            // Assuming your time input is in HH:MM:SS format
            $timein_dt = new DateTime($timein_raw);
            $timeout_dt = new DateTime($timeout_raw);

            $interval = $timein_dt->diff($timeout_dt);

            // Format total hours as HH:MM:SS
            $total_hours = $interval->format('%H:%i:%s');

        } catch (Exception $e) {
            // Handle invalid time format or other errors
            error_log("Error calculating total hours: " . $e->getMessage());
            $_SESSION["error"] = "Invalid time format";
            header("Location: attendance.php");
            exit();
        }
    }

    $edit_attendance = mysqli_query($conn, "UPDATE attendance SET employee_name = '$employee_name', date = '$date', time_in = '$timein_raw', time_out = '$timeout_raw', total_hours = '$total_hours', status = '$status' WHERE id = '$employee_id'");

    if ($edit_attendance) {
        $_SESSION["success"] = "Attendance updated successfully";
        header("Location: attendance.php");
        exit();
    } else {
        $_SESSION["error"] = "Error recording attendance: " . mysqli_error($conn);
        header("Location: attendance.php");
        exit();
    }

}

if(isset($_POST["delete_attendance"])){
    $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);

    $delete_attendance = mysqli_query($conn, "DELETE FROM attendance WHERE id = '$id'");

    if($delete_attendance){
        $_SESSION["success"] = "Delete attendance successfully!";
        header("Location: attendance.php");
        exit();
    }


}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css_folder/style.css">
    <link rel="stylesheet" href="css_folder/attendance.css">
    <!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        .right-bar .nav-bar .nav-logo .dropdown .dropdown-menu button {
            padding: 4px 16px;
            font-size: 16px;
        }
    </style>

</head>

<body>

    <!-- <div class="loader">
        <div></div>

        <p>Please Wait....</p>
    </div> -->

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
                                    data-bs-target="#exampleModal2"><i class="fa fa-user"></i> Profile</button>
                            </li>
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal3"><i class="fa fa-unlock"></i> Reset
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
                <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel"
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
                                                <img src="./uploads/<?php echo $row["image"] ?>" width="200px" height="200px" style="border-radius: 50%; z-index: 1;">
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
            <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel"
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
                                    <p style="font-size: 16px; color: #777F87; margin: 0px 0px 16px;">Input your username
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

            <div class="navbar-text-button">
                <div class="navbar-text">
                    <h3>Attendance</h3>
                </div>

                <div class="navbar-button">
                    <button type="button" class="btn btn" data-bs-toggle="modal" data-bs-target="#exampleModal"><i
                            class="fas fa-calendar-alt"></i>Attendance</button>
                </div>
            </div>

            <!-- <div class="row">
                <div class="col-lg-12">

                    <label class="form-label">Employee</label><br>
                    <select name="employee_name" required>
                        <option value="Select employee">Select employee</option>

                        <?php
                        $select_employee = mysqli_query($conn, "SELECT * FROM employee");

                        while ($row = mysqli_fetch_assoc($select_employee)) {
                            ?>
                            <option value="<?php echo $row["name"] ?>"><?php echo $row["name"] ?>
                            </option>
                            <?php
                        }

                        ?>
                    </select>
                </div>
            </div> -->


            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #F4EFC6">
                            <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Attendance</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="background-color: #F4EFC6">
                            <form action="" method="post">

                                <div class="row">
                                    <div class="col-lg-12">

                                        <label class="form-label">Employee</label><br>
                                        <select name="employee_name" required>
                                            <option value="">Select employee</option>

                                            <?php
                                            $select_employee = mysqli_query($conn, "SELECT * FROM employee");

                                            while ($row = mysqli_fetch_assoc($select_employee)) {
                                                ?>
                                                <option value="<?php echo $row["name"] ?>"><?php echo $row["name"] ?>
                                                </option>
                                                <?php
                                            }

                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-12">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control"
                                            style="border: 2px solid #D3891F; background: none;" required>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-12">
                                        <label class="form-label">Status</label>
                                        <select name="status">
                                            <option value="">Select Status</option>
                                            <option value="Late">Late</option>
                                            <option value="On Time">On Time</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-6">
                                        <label class="form-label">Time In</label>
                                        <input type="time" name="timein" class="form-control"
                                            style="border: 2px solid #D3891F; background: none;" required>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label">Time Out</label>
                                        <input type="time" name="timeout" class="form-control"
                                            style="border: 2px solid #D3891F; background: none;" required>
                                    </div>
                                </div>




                        </div>
                        <div class="modal-footer" style="background-color: #F4EFC6;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn" style="background-color: #D3891F; color: #fffffe;"
                                name="submit">Submit</button>
                        </div>

                        </form>
                    </div>
                </div>
            </div>


            <?php

            $select_attendance = mysqli_query($conn, "SELECT * FROM attendance");

            while ($row3 = mysqli_fetch_assoc($select_attendance)) {

                ?>
                <!-- Modal2 -->
                <div class="modal fade" id="exampleModal2<?php echo $row3["id"] ?>" tabindex="-1"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #F4EFC6">
                                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Update Attendance</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="background-color: #F4EFC6">
                                <form action="" method="post">

                                    <input type="hidden" name="employee_id" value="<?php echo $row3["id"] ?>">

                                    <div class="row">
                                        <div class="col-lg-12">

                                            <label class="form-label">Employee Name:</label><br>
                                            <input type="text" name="employee_name"
                                                value="<?php echo $row3["employee_name"] ?>"
                                                style="border: 2px solid #D3891F; background: none; width: 466.67px; height: 40px; padding: 0px 10px;">
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" class="form-control"
                                                value="<?php echo $row3["date"] ?>"
                                                style="border: 2px solid #D3891F; background: none;">
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-12">
                                            <label class="form-label">Status</label>
                                            <select name="status" style="border: 2px solid #D3891F; background: none;">
                                                <?php

                                                if ($row3["status"] == "On Time") {
                                                    ?>

                                                    <option value="<?php echo $row3["status"] ?>"><?php echo $row3["status"] ?>
                                                    </option>
                                                    <option value="Late">Late</option>

                                                    <?php
                                                } else if ($row3["status"] == "Late") {
                                                    ?>
                                                        <option value="<?php echo $row3["status"] ?>"><?php echo $row3["status"] ?>
                                                        </option>
                                                        <option value="On Time">On Time</option>

                                                    <?php
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-lg-6">
                                            <label class="form-label">Time In</label>
                                            <input type="time" name="timein"
                                                value="<?php echo ($row3["time_in"] == '00:00:00') ? '' : $row3["time_in"] ?>"
                                                class="form-control" style="border: 2px solid #D3891F; background: none;">
                                        </div>

                                        <div class="col-lg-6">
                                            <label class="form-label">Time Out</label>
                                            <input type="time" name="timeout"
                                                value="<?php echo ($row3["time_out"] == '00:00:00') ?: $row3["time_out"] ?>"
                                                class="form-control" style="border: 2px solid #D3891F; background: none;">
                                        </div>
                                    </div>

                            </div>
                            <div class="modal-footer" style="background-color: #F4EFC6;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn" style="background-color: #D3891F; color: #fffffe;"
                                    name="edit_attendance">Save changes</button>
                            </div>

                            </form>
                        </div>
                    </div>
                </div>
                <?php

            }

            ?>

            <div class="parent-attendance-logs">
                <div class="attendance-logs">
                    <h3>Attendance Logs</h3>

                    <div class="filter-totalhrs">

                        <div class="filter">
                            <h4>Filter</h4>

                            <form action="" method="post">
                                <select name="employee_name">
                                    <option value="Select employee">Select employee</option>
                                    <?php
                                    $select_employee = mysqli_query($conn, "SELECT * FROM employee");
                                    while ($row = mysqli_fetch_assoc($select_employee)) {
                                        ?>
                                        <option value="<?php echo $row["name"] ?>"><?php echo $row["name"] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>

                                <select name="select_date">
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>

                                <button type="submit" class="btn btn" name="run">Run</button>
                            </form>
                        </div>
                        <?php

                        if (isset($_POST["run"])) {

                            $employee_name = $_POST["employee_name"];
                            $select_date = $_POST["select_date"];

                            $select_attendance = mysqli_query($conn, "SELECT time_in, time_out FROM attendance WHERE employee_name = '$employee_name' AND MONTHNAME(date) = '$select_date'");

                            $total_seconds = 0;

                            while ($row = mysqli_fetch_assoc($select_attendance)) {
                                $time_in = $row["time_in"];
                                $time_out = $row["time_out"];

                                if (!empty($time_in) && $time_in != '00:00:00' && !empty($time_out) && $time_out != '00:00:00') {
                                    try {
                                        $timein_dt = new DateTime($time_in);
                                        $timeout_dt = new DateTime($time_out);
                                        $interval = $timein_dt->diff($timeout_dt);
                                        $total_seconds += $interval->h * 3600 + $interval->i * 60 + $interval->s;
                                    } catch (Exception $e) {
                                        error_log("Error calculating time difference: " . $e->getMessage());
                                    }
                                }
                            }

                            $total_hours = floor($total_seconds / 3600);
                            $total_minutes = floor(($total_seconds % 3600) / 60);

                            ?>

                            <div class="totalhrs">
                                <p class="fw-bold" style="margin: 0px 20px 0px 10px;">Total Hours:
                                    <?php echo sprintf('%d:%02d', $total_hours, $total_minutes); ?>
                                </p>
                            </div>

                            <?php
                        }

                        ?>
                    </div>
                    <!-- 
                    <?php
                    $select_attendance = mysqli_query($conn, "SELECT * FROM attendance");
                    while ($row = mysqli_fetch_assoc($select_attendance)) {

                    }
                    ?> -->

                    <div class="table-parent">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($_POST['run'])) {

                                    unset($_SESSION["success"]);


                                    $selected_employee = $_POST['employee_name'];
                                    $selected_month = $_POST['select_date'];


                                    $sql = "SELECT * FROM attendance WHERE employee_name = '$selected_employee' AND MONTHNAME(date) = '$selected_month'";
                                    $result = mysqli_query($conn, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row2 = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                
                                                    
                                                    <td><?php echo $row2["date"] ?></td>
                                                    <td><?php echo $row2["employee_name"] ?></td>
                                                    <td><?php echo ($row2["time_in"] == '00:00:00' || is_null($row2["time_in"])) ? '--:--' : $row2["time_in"]; ?>
                                                    </td>
                                                    <td><?php echo ($row2["time_out"] == '00:00:00' || is_null($row2["time_out"])) ? '--:--' : ($row2["time_out"] ?? '--:--'); ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if (!empty($row2["time_in"]) && $row2["time_in"] != '00:00:00' && !empty($row2["time_out"]) && $row2["time_out"] != '00:00:00') {
                                                            try {
                                                                $timein_dt = new DateTime($row2["time_in"]);
                                                                $timeout_dt = new DateTime($row2["time_out"]);
                                                                $interval = $timein_dt->diff($timeout_dt);
                                                                $total_seconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
                                                                echo sprintf('%d:%02d', floor($total_seconds / 3600), floor(($total_seconds % 3600) / 60));
                                                            } catch (Exception $e) {
                                                                echo '--:--';
                                                            }
                                                        } else {
                                                            echo '--:--';
                                                        }
                                                        ?>
                                                    </td>

                                                    <?php

                                                    if ($row2["status"] == "Late") {
                                                        ?>

                                                        <td><button class="btn text-white" style="font-size: 14px; background-color: #FF6961;"><?php echo $row2["status"] ?></button></td>

                                                        <?php
                                                    }else if($row2["status"] == "On Time"){
                                                        ?>

                                                        <td><button class="btn text-white" style="font-size: 14px; background-color: #FFB347;"><?php echo $row2["status"] ?></button></td>

                                                        <?php
                                                    }

                                                    ?>

                                                    <td>
                                                    <form method="post">
                                                        
                                                            <div class="dropdown">
                                                                <a class="btn btn-secondary dropdown-toggle" type="button"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Actions
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <button type="button" class="dropdown-item"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal2<?php echo $row2["id"] ?>"><i
                                                                                class="fa fa-edit"></i> Edit</button>
                                                                    </li>
                                                                    <li>
                                                                        <input type="hidden" name="id" value="<?php echo $row2["id"] ?>">
                                                                        
                                                                        <button type="submit" class="dropdown-item" name="delete_attendance"><i
                                                                                class="fa fa-trash"></i> Delete</button>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                            </form>
                                                        
                                                    </td>
                                                
                                            </tr>
                                            <?php
                                        }

                                    } else {
                                        echo "<tr><td colspan='8'>No records found for the selected employee and month.</td></tr>";
                                    }

                                } else {

                                    echo "<tr><td colspan='8'>No records found for the selected employee and month.</td></tr>";

                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        function convertTo12Hour(time24, isTotalHours = false) {
            if (!time24 || time24 === '--:--' || time24 === 'null') return '--:--';

            let [hours, minutes] = time24.split(':');
            hours = parseInt(hours, 10);

            if (isNaN(hours)) { // Check for NaN
                return '--:--';
            }

            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;

            if (hours === 0 && ampm === 'PM') {
                hours = 12;
            }

            if (isTotalHours) {
                return hours + ':' + minutes;
            } else {
                return hours + ':' + minutes + ' ' + ampm;
            }
        }

        const timeCells = document.querySelectorAll('td:nth-child(3), td:nth-child(4)');

        timeCells.forEach(cell => {
            if (cell.textContent !== '--:--') {
                cell.textContent = convertTo12Hour(cell.textContent);
            }
        });

        const totalHoursCell = document.querySelector('td:nth-child(5)');

        if (totalHoursCell && totalHoursCell.textContent !== '--:--') {
            totalHoursCell.textContent = convertTo12Hour(totalHoursCell.textContent, true);
        }
    </script>

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
    }

    ?>
</body>

</html>