<?php

session_start();
include("./database/connection.php");

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

if (isset($_POST["submit_payroll"])) {
    $employee_id_log = filter_input(INPUT_POST, "payroll_employee_id", FILTER_SANITIZE_SPECIAL_CHARS);
    $payment_date_log = filter_input(INPUT_POST, "payroll_payment_date", FILTER_SANITIZE_SPECIAL_CHARS);
    $starting_date_log = filter_input(INPUT_POST, "payroll_starting_date", FILTER_SANITIZE_SPECIAL_CHARS);
    $ending_date_log = filter_input(INPUT_POST, "payroll_ending_date", FILTER_SANITIZE_SPECIAL_CHARS);
    // You are getting this from a hidden input, which was set to the calculated value
    $calculated_total_hours_log = filter_input(INPUT_POST, "payroll_total_hours", FILTER_SANITIZE_SPECIAL_CHARS);
    $gross_pay_log = filter_input(INPUT_POST, "payroll_gross_pay", FILTER_SANITIZE_SPECIAL_CHARS);
    $employee_name_log = filter_input(INPUT_POST, "payroll_employee_name", FILTER_SANITIZE_SPECIAL_CHARS);

    $select_date = mysqli_query($conn, "SELECT employee_name, payment_date FROM payroll_logs WHERE employee_name = '$employee_name_log' AND payment_date = '$payment_date_log'");

    if (mysqli_num_rows($select_date) > 0) {
        $_SESSION["error"] = "the date of payment is already exists!";
    } else {
        // Use the calculated total hours for insertion
        $insert_query = mysqli_query($conn, "INSERT INTO payroll_logs (payment_date, employee_name, starting_date, end_date, total_hours, gross_pay) VALUES ('$payment_date_log', '$employee_name_log', '$starting_date_log', '$ending_date_log', '$calculated_total_hours_log', '$gross_pay_log')");

        if ($insert_query) {
            $_SESSION["message"] = "Payroll data saved successfully!";
            header("Location: payroll.php");
            exit();
        } else {
            echo '<div class="alert alert-danger mt-3">Error saving payroll data: ' . mysqli_error($conn) . '</div>';
        }
    }
}

if (isset($_POST["delete_payroll"])) {
    $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);

    $delete_payroll = mysqli_query($conn, "DELETE FROM payroll_logs WHERE id = '$id'");

    if ($delete_payroll) {
        $_SESSION["message"] = "Delete data successfully!";
        header("Location: payroll.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css_folder/payroll.css">

    <style>
        .right-bar .nav-bar .nav-logo .dropdown .dropdown-menu button {
            padding: 4px 16px;
            font-size: 16px;
        }
    </style>
</head>

<body>

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
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
        <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

        <h3>Payroll</h3>

        <!-- Modal -->
        <?php

        $select_employee2 = mysqli_query($conn, "SELECT * FROM payroll_logs");

        while ($row2 = mysqli_fetch_assoc($select_employee2)) {

            ?>
            <div class="modal fade" id="exampleModal2<?php echo $row2["id"] ?>" tabindex="-1"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #F4EFC6">
                            <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Edit Payroll Logs</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="background-color: #F4EFC6">
                            <form action="" method="post">

                                <input type="hidden" name="employee_id" value="<?php echo $row2["id"] ?>">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label class="form-label">Payment Date</label>
                                        <input type="text" name="name" class="form-control" placeholder="Enter Name"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["payment_date"] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                        <label class="form-label">Employee Name</label>
                                        <input type="text" name="position" class="form-control" placeholder="Enter Position"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["employee_name"] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                        <label class="form-label">Starting Date</label>
                                        <input type="text" name="rate" class="form-control" placeholder="Enter Rate"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["starting_date"] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                        <label class="form-label">End Date</label>
                                        <input type="text" name="address" class="form-control" placeholder="Enter Address"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["end_date"] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                        <label class="form-label">Total Hours</label>
                                        <input type="text" name="contact" class="form-control" placeholder="Enter Contact"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["total_hours"] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 mt-3">
                                        <label class="form-label">Gross Pay</label>
                                        <input type="text" name="status" class="form-control" placeholder="Enter Status"
                                            style="border: 2px solid #D3891F; background: none;"
                                            value="<?php echo $row2["gross_pay"] ?>">
                                    </div>
                                </div>

                        </div>
                        <div class="modal-footer" style="background-color: #F4EFC6">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn text-white" style="background-color: #D3891F;"
                                name="edit_employee">Save changes</button>
                        </div>

                        </form>
                    </div>
                </div>
            </div>
            <?php

        }

        ?>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button class="nav-link active" id="payroll-logs" data-bs-toggle="tab"
                    data-bs-target="#payroll-logs-pane" type="button" role="tab" aria-controls="home-tab-pane"
                    aria-selected="true">Payroll Logs</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="new-pay" data-bs-toggle="tab" data-bs-target="#new-pay-pane" type="button"
                    role="tab" aria-controls="profile-tab-pane" aria-selected="false">New Pay</button>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="payroll-logs-pane" role="tabpanel" aria-labelledby="home-tab"
                tabindex="0">


                <div class="payroll-box">
                    <h3>Payroll Logs</h3>

                    <div class="filter">
                        <form action="" method="post">
                            <h4>Filter</h4>

                            <select name="employee_name">
                                <option value="">Select employee</option>

                                <?php
                                $select_employee = mysqli_query($conn, "SELECT * FROM employee");

                                while ($row = mysqli_fetch_assoc($select_employee)) {
                                    ?>
                                    <option value="<?php echo $row["name"] ?>"><?php echo $row["name"] ?></option>
                                    <?php
                                }

                                ?>
                            </select>


                            <button type="submit" name="run" class="btn btn">Run</button>
                        </form>
                    </div>

                    <div class="table-parent">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Employee Name</th>
                                    <th>Starting Date</th>
                                    <th>End Date</th>
                                    <th>Total Hours</th>
                                    <th>Gross Pay</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                if (isset($_POST["run"])) {
                                    $employee_name = $_POST["employee_name"];
                                    $select_payroll = "SELECT * FROM payroll_logs WHERE employee_name = '$employee_name'";

                                    $sql = mysqli_query($conn, $select_payroll);

                                    if (mysqli_num_rows($sql) > 0) {
                                        while ($row = mysqli_fetch_assoc($sql)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $row["payment_date"] ?></td>
                                                <td><?php echo $row["employee_name"] ?></td>
                                                <td><?php echo $row["starting_date"] ?></td>
                                                <td><?php echo $row["end_date"] ?></td>
                                                <!-- <td><?php echo $row["total_hours"] ?></td> -->
                                                <td><?php
                                                $total_hours_db = $row["total_hours"];
                                                $time_parts = explode(":", $total_hours_db);
                                                echo $time_parts[0] . ":" . $time_parts[1];
                                                ?>
                                                </td>
                                                <td><?php echo $row["gross_pay"] ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a class="btn btn-secondary dropdown-toggle" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                                    data-bs-target="#exampleModal2<?php echo $row["id"] ?>"><i
                                                                        class="fa fa-edit"></i>
                                                                    Edit</button>
                                                            </li>
                                                            <li>
                                                                <form method="post" action="">
                                                                    <input type="hidden" name="id"
                                                                        value="<?php echo $row["id"]; ?>">
                                                                    <button type="submit" class="dropdown-item"
                                                                        name="delete_payroll"><i class="fa fa-trash"></i>
                                                                        Delete</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="7">No payroll logs found for this employee.</td></tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="7">Please select an employee to view payroll logs.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="new-pay-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">

                <div class="parent-new-earning">
                    <div class="payroll-new-pay">
                        <h3>New Pay</h3>

                        <form method="post" action="">
                            <label class="form-label mt-3">Employee:</label><br>
                            <select name="employee_id">
                                <option value="">Select employee</option>
                                <?php
                                $select_employee = mysqli_query($conn, "SELECT id, name FROM employee");
                                while ($row = mysqli_fetch_assoc($select_employee)) {
                                    echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
                                }
                                ?>
                            </select>
                            <br>

                            <label class="form-label mt-3">Pay Date:</label>
                            <input type="date" name="pay_date" class="form-control" placeholder="YYYY/MM/DD" required>

                            <label class="form-label mt-3">Starting Date:</label>
                            <input type="date" name="starting_date" class="form-control" placeholder="YYYY/MM/DD"
                                required>

                            <label class="form-label mt-3">Ending Date:</label>
                            <input type="date" name="end_date" class="form-control" placeholder="YYYY/MM/DD" required>

                            <button type="submit" class="btn btn mt-3" name="calculate">Enter</button>
                        </form>
                    </div>

                    <?php
                    if (isset($_POST["calculate"])) {
                        $employee_id = $_POST["employee_id"];
                        $pay_date = $_POST["pay_date"];
                        $starting_date = $_POST["starting_date"];
                        $ending_date = $_POST["end_date"];

                        $total_seconds = 0;
                        $attendance_query = mysqli_query($conn, "SELECT time_in, time_out FROM attendance WHERE employee_name = (SELECT name FROM employee WHERE id = '$employee_id') AND date >= '$starting_date' AND date <= '$ending_date'");

                        while ($row_attendance = mysqli_fetch_assoc($attendance_query)) {
                            $time_in = $row_attendance['time_in'];
                            $time_out = $row_attendance['time_out'];

                            if (!empty($time_in) && $time_in != '00:00:00' && !empty($time_out) && $time_out != '00:00:00') {
                                try {
                                    $timein_dt = new DateTime($time_in);
                                    $timeout_dt = new DateTime($time_out);
                                    $interval = $timein_dt->diff($timeout_dt);
                                    $total_seconds += ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
                                } catch (Exception $e) {
                                    error_log("Error calculating time difference: " . $e->getMessage());
                                }
                            }
                        }

                        $total_hours_decimal = $total_seconds / 3600;
                        $total_hours_display = floor($total_seconds / 3600) . ":" . sprintf("%02d", floor(($total_seconds % 3600) / 60));
                        $total_hours_for_db = gmdate("H:i:s", $total_seconds); // Format for TIME in DB
                    
                        // 2. Fetch Employee Rate
                        $rate_query = mysqli_query($conn, "SELECT rate FROM employee WHERE id = '$employee_id'");
                        $employee_data = mysqli_fetch_assoc($rate_query);
                        $hourly_rate = $employee_data['rate'] ?? '0.00';

                        $gross_pay = $total_hours_decimal * floatval($hourly_rate);

                        $gross_pay_display = number_format($gross_pay, 2);

                        $employee_name_query = mysqli_query($conn, "SELECT name FROM employee WHERE id = '$employee_id'");
                        $employee_name_data = mysqli_fetch_assoc($employee_name_query);
                        $employee_name = $employee_name_data['name'] ?? '';

                        ?>
                        <form method="post" action="">
                            <div class="earnings">
                                <h3>Earnings</h3>
                                <div class="table-parent">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th colspan="11"></th>
                                                <th>Unit</th>
                                                <th>Rate</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="background-color: #F2B359;">
                                                <td colspan="11">Ordinary Hours Worked</td>
                                                <td><?php echo $total_hours_display ?? "0.00"; ?></td>
                                                <td><?php echo $hourly_rate; ?></td>
                                                <td><?php echo $gross_pay; ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="13" style="text-align: right;">Gross Pay</td>
                                                <td><?php echo $gross_pay; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="button">
                                <button type="submit"
                                    class="btn btn mt-3 <?php echo ($total_hours_display == '0:00') ? 'disabled' : ''; ?>"
                                    name="submit_payroll" <?php echo ($total_hours_display == '0:00') ? 'disabled' : ''; ?>>Submit</button>
                            </div>
                            <input type="hidden" name="payroll_employee_id" value="<?php echo $employee_id; ?>">
                            <input type="hidden" name="payroll_payment_date" value="<?php echo $pay_date; ?>">
                            <input type="hidden" name="payroll_starting_date" value="<?php echo $starting_date; ?>">
                            <input type="hidden" name="payroll_ending_date" value="<?php echo $ending_date; ?>">
                            <input type="hidden" name="payroll_total_hours" value="<?php echo $total_hours_display; ?>">
                            <input type="hidden" name="payroll_gross_pay" value="<?php echo $gross_pay; ?>">
                            <input type="hidden" name="payroll_employee_name" value="<?php echo $employee_name; ?>">
                        </form>
                        <?php
                    } else {
                        ?>
                        <form method="post" action="">
                            <div class="earnings">
                                <h3>Earnings</h3>
                                <div class="table-parent">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th colspan="11"></th>
                                                <th>Unit</th>
                                                <th>Rate</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="background-color: #F2B359;">
                                                <td colspan="11">Ordinary Hours Worked</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="13" style="text-align: right;">Gross Pay</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="button">
                                <button type="submit" class="btn btn mt-3" name="submit_payroll" disabled>Submit</button>
                            </div>
                        </form>
                        <?php
                    }
                    ?>
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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const newPayForm = document.querySelector('#new-pay-pane form[name="calculate"]');
                const submitPayrollForm = document.querySelector('#new-pay-pane form[name="submit_payroll"]');
                const newPayTabButton = document.getElementById('new-pay');
                const newPayTab = new bootstrap.Tab(newPayTabButton);
                const payrollLogsTabButton = document.getElementById('payroll-logs');
                const payrollLogsTab = new bootstrap.Tab(payrollLogsTabButton);
                const newPayPane = document.getElementById('new-pay-pane');
                const payrollLogsPane = document.getElementById('payroll-logs-pane');
                const activeTabStorageKey = 'activeTab';

                // Save active tab to localStorage
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tabButton) {
                    tabButton.addEventListener('shown.bs.tab', function (e) {
                        localStorage.setItem(activeTabStorageKey, e.target.getAttribute('data-bs-target'));
                    });
                });

                const activateTabContent = (tabTarget) => {
                    if (tabTarget === '#new-pay-pane') {
                        newPayPane.classList.add('show', 'active');
                        payrollLogsPane.classList.remove('show', 'active');
                        if (newPayTabButton) new bootstrap.Tab(newPayTabButton).show();
                    } else if (tabTarget === '#payroll-logs-pane') {
                        payrollLogsPane.classList.add('show', 'active');
                        newPayPane.classList.remove('show', 'active');
                        if (payrollLogsTabButton) new bootstrap.Tab(payrollLogsTabButton).show();
                    }
                };

                // Read and activate tab from localStorage on page load
                window.addEventListener('DOMContentLoaded', function () {
                    var activeTab = localStorage.getItem(activeTabStorageKey);
                    if (activeTab) {
                        var tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
                        if (tabTrigger) {
                            new bootstrap.Tab(tabTrigger).show();
                            activateTabContent(activeTab); // Also activate the content
                        }
                    } else {
                        // Default to 'Payroll Logs' on initial load if no active tab is stored
                        activateTabContent('#payroll-logs-pane');
                    }
                });

                // Handle 'Enter' button click in 'New Pay'
                if (newPayForm) {
                    const enterButton = newPayForm.querySelector('button[name="calculate"]');
                    if (enterButton) {
                        enterButton.addEventListener('click', function (event) {
                            event.preventDefault();
                            const formData = new FormData(newPayForm);

                            fetch('', { // Replace '' with your actual PHP script URL for calculation
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.text())
                                .then(data => {
                                    const earningsContainer = document.querySelector('#new-pay-pane .parent-new-earning');
                                    if (earningsContainer) {
                                        earningsContainer.innerHTML = data;
                                    }
                                    activateTabContent('#new-pay-pane'); // Ensure New Pay is active
                                })
                                .catch(error => {
                                    console.error('Error calculating payroll:', error);
                                    activateTabContent('#new-pay-pane'); // Ensure New Pay is active even on error
                                });
                        });
                    }
                }

                // Handle 'Submit' button click in 'New Pay'
                if (submitPayrollForm) {
                    submitPayrollForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        const formData = new FormData(submitPayrollForm);

                        fetch('', { // Replace '' with your actual PHP script URL for submission
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.text())
                            .then(data => {
                                console.log('Payroll submitted:', data);
                                activateTabContent('#new-pay-pane'); // Ensure New Pay is active
                                // Optionally provide user feedback or update UI
                            })
                            .catch(error => {
                                console.error('Error submitting payroll:', error);
                                activateTabContent('#new-pay-pane'); // Ensure New Pay is active even on error
                            });
                    });

                    // Prevent default submit button behavior
                    const submitButton = submitPayrollForm.querySelector('button[name="submit_payroll"]');
                    if (submitButton && submitButton.type === 'submit') {
                        submitButton.type = 'button';
                        submitButton.addEventListener('click', function () {
                            submitPayrollForm.dispatchEvent(new Event('submit'));
                        });
                    }
                }
            });
        </script>


        <!-- <script>
            document.addEventListener('DOMContentLoaded', function () {
                const newPayEnterButton = document.querySelector('#new-pay-pane button[name="calculate"]');
                const payrollLogsTabButton = document.getElementById('payroll-logs');
                const newPayTabButton = document.getElementById('new-pay');
                const payrollLogsPaneId = '#payroll-logs-pane';
                const newPayPaneId = '#new-pay-pane';
                const activeTabStorageKey = 'activePayrollTab';

                // Function to save the active tab to localStorage
                const saveActiveTab = (event) => {
                    localStorage.setItem(activeTabStorageKey, event.target.getAttribute('data-bs-target'));
                };

                // Attach event listeners to tab buttons to save active tab
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tabButton) {
                    tabButton.addEventListener('shown.bs.tab', saveActiveTab);
                });

                // Function to restore the active tab from localStorage
                const restoreActiveTab = () => {
                    const activeTab = localStorage.getItem(activeTabStorageKey);
                    if (activeTab) {
                        const tabTrigger = document.querySelector('button[data-bs-target="' + activeTab + '"]');
                        if (tabTrigger) {
                            new bootstrap.Tab(tabTrigger).show();
                        }
                    } else {
                        // If no active tab is stored, default to 'Payroll Logs'
                        if (payrollLogsTabButton) {
                            new bootstrap.Tab(payrollLogsTabButton).show();
                        }
                    }
                };

                // Restore active tab on page load
                restoreActiveTab();

                // Prevent saving active tab when interacting with the 'Enter' button in 'New Pay'
                if (newPayEnterButton) {
                    newPayEnterButton.addEventListener('click', function () {
                        // Optionally, you could remove the stored active tab here
                        // localStorage.removeItem(activeTabStorageKey);
                        // Or, you could set a flag to ignore the next 'shown.bs.tab' event
                        sessionStorage.setItem('ignoreNextTabSave', 'true');
                    });
                }

                // Modify the saveActiveTab function to check the ignore flag
                const modifiedSaveActiveTab = (event) => {
                    if (sessionStorage.getItem('ignoreNextTabSave') === 'true') {
                        sessionStorage.removeItem('ignoreNextTabSave');
                        return; // Don't save the tab
                    }
                    localStorage.setItem(activeTabStorageKey, event.target.getAttribute('data-bs-target'));
                };

                // Remove the original listeners and add the modified one
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tabButton) {
                    tabButton.removeEventListener('shown.bs.tab', saveActiveTab);
                    tabButton.addEventListener('shown.bs.tab', modifiedSaveActiveTab);
                });
            });
        </script> -->

        <?php

        if (isset($_SESSION["message"])) {
            ?>

            <script>
                swal({
                    title: "<?php echo $_SESSION["message"] ?>",
                    icon: "success",
                    button: "cancel",
                });
            </script>

            <?php
            unset($_SESSION["message"]);
        }

        if (isset($_SESSION["error"])) {
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