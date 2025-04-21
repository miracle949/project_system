<?php

session_start();
include("./database/connection.php");

if (isset($_GET["page_no"]) && $_GET["page_no"] !== "") {
    
    $page_no = $_GET["page_no"];

} else {

    $page_no = 1;
}

// total rows or records to display
$total_records_per_page = 10;

// get the page offset for the LIMIT query
$offset = ($page_no - 1) * $total_records_per_page;

// get the previous page
$previous_page = $page_no - 1;

// get the next page
$next_page = $page_no + 1;

// get the total counts of records
$result_count = mysqli_query($conn, "SELECT COUNT(*) AS total_records FROM schedules") or die(mysqli_error($conn));

// total records
$records = mysqli_fetch_assoc($result_count);

// store total_records to a variable
$total_records = $records["total_records"];

// get the total pages
$total_no_of_pages = ceil($total_records / $total_records_per_page);

$sql = "SELECT * FROM schedules LIMIT $offset, $total_records_per_page";

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

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

if (isset($_POST["save"])) {
    $employee_name = filter_input(INPUT_POST, "employee_name", FILTER_SANITIZE_SPECIAL_CHARS);

    $select_time = filter_input(INPUT_POST, "select_time", FILTER_SANITIZE_SPECIAL_CHARS);

    $insert_schedule = mysqli_query($conn, "INSERT INTO schedules (employee_name,schedule) VALUES ('$employee_name','$select_time')");

    if ($insert_schedule) {
        $_SESSION["schedule"] = "Add schedule successfully!";
        header("Location: schedule.php");
        exit();
    }
}

if (isset($_POST["save_time"])) {
    $time_in = filter_input(INPUT_POST, "time_in", FILTER_SANITIZE_SPECIAL_CHARS);

    $time_out = filter_input(INPUT_POST, "time_out", FILTER_SANITIZE_SPECIAL_CHARS);

    $select_timein_timeout = mysqli_query($conn, "SELECT * FROM schedule_time WHERE time_in = '$time_in' AND time_out = '$time_out'");

    if(mysqli_num_rows($select_timein_timeout) > 0){

        $_SESSION["error"] = "Schedule time is already exists!";

    }else{

        $insert_time = mysqli_query($conn, "INSERT INTO schedule_time (time_in,time_out) VALUES ('$time_in','$time_out')");

        if ($insert_time) {
            $_SESSION["schedule"] = "Add time successfully!";
            header("Location: schedule.php");
            exit();
    }
    }
}

if (isset($_POST["edit_employee"])) {

    $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);

    $employee_name = filter_input(INPUT_POST, "employee_name", FILTER_SANITIZE_SPECIAL_CHARS);

    $schedule = filter_input(INPUT_POST, "schedule", FILTER_SANITIZE_SPECIAL_CHARS);

    $update_position = mysqli_query($conn, "UPDATE schedules SET employee_name = '$employee_name', schedule = '$schedule' WHERE id = '$id'");

    if ($update_position) {
        $_SESSION["schedule"] = "Update successfully!";
        header("Location: schedule.php");
        exit();
    }
}

if(isset($_POST["delete_schedule"])){
    $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_SPECIAL_CHARS);

    $delete_schedule = mysqli_query($conn, "DELETE FROM schedules WHERE id = '$id'");

    if ($delete_schedule) {
        $_SESSION["schedule"] = "Delete schedule successfully!";
        header("Location: schedule.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css_folder/schedule.css">

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
                                    data-bs-target="#exampleModal3"><i class="fa fa-user"></i> Profile</button>
                            </li>
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal4"><i class="fa fa-unlock"></i> Reset
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
                <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel"
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
            <div class="modal fade" id="exampleModal4" tabindex="-1" aria-labelledby="exampleModalLabel"
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

        <div class="positions-employee">
            <div class="positions-text">
                <h3>Schedules</h3>
            </div>

            <div class="button-positions">
                <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal2"><i class="fa fa-plus"></i>
                    Time Schedule</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa fa-plus"></i>
                    New Schedules</button>
            </div>
        </div>

        <!-- Modal time schedule -->
        <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #F4EFC6">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Time Schedule</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background-color: #F4EFC6">
                        <form action="" method="post">

                            <div class="row">
                                <div class="col-lg-12">
                                    <label class="form-label">Time In</label>
                                    <input type="time" name="time_in" class="form-control"
                                        style="border: 2px solid #D3891F; background: none;" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Time Out</label>
                                    <input type="time" name="time_out" class="form-control"
                                        style="border: 2px solid #D3891F; background: none;" required>
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer" style="background-color: #F4EFC6">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn text-white" style="background-color: #D3891F;"
                            name="save_time">Save</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <?php
        $select_time = mysqli_query($conn, "SELECT time_in, time_out FROM schedule_time");
        $time_options = [];
        while ($row = mysqli_fetch_assoc($select_time)) {
            $time_options[] = [
                'time_in_24' => $row["time_in"],
                'time_out_24' => $row["time_out"]
            ];
        }
        // Encode the PHP array as a JSON string
        $time_options_json = json_encode($time_options);
        ?>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #F4EFC6">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">New Schedules</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background-color: #F4EFC6">
                        <form action="" method="post">

                            <div class="row">
                                <div class="col-lg-12">
                                    <label class="form-label">Employee Name</label><br>
                                    <select name="employee_name" required>
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
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Schedule</label>
                                    <select name="select_time" id="selectTime" required>
                                        <option value="">Select time</option>
                                    </select>
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer" style="background-color: #F4EFC6">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn text-white" style="background-color: #D3891F;"
                            name="save">Save</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>


        <div class="parent-positions">

            <div class="table-positions">

                <div class="search-filter">

                    <div class="search">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="search" name="search" id="search" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                    
                </div>

                <!-- Modal -->
                <?php

                $select_employee2 = mysqli_query($conn, "SELECT * FROM schedules");

                while ($row2 = mysqli_fetch_assoc($select_employee2)) {

                    ?>
                    <div class="modal fade" id="exampleModal2<?php echo $row2["id"] ?>" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #F4EFC6">
                                    <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Update Schedule
                                    </h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="background-color: #F4EFC6">
                                    <form action="" method="post">

                                        <input type="hidden" name="id" value="<?php echo $row2["id"] ?>">

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label class="form-label">Employee Name</label>
                                                <input type="text" name="employee_name" class="form-control"
                                                    placeholder="Enter Name"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["employee_name"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Schedule</label>
                                                <input type="text" name="schedule" class="form-control"
                                                    placeholder="Enter Position"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["schedule"] ?>">
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

                <div class="parent-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Schedules</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="showdata">
                            <?php

                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $row["id"] ?></td>
                                    <td><?php echo $row["employee_name"] ?></td>
                                    <td><?php echo $row["schedule"] ?></td>
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
                                                    <form action="" method="post">
                                                        <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                                        <button type="submit" class="dropdown-item" name="delete_schedule"><i
                                                            class="fa fa-trash"></i>
                                                        Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php

                            }

                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="parent-pagination" style="display: flex; justify-content: space-between; position: relative; top: 25px;">
                    <div class="showing-entries">
                        <p style="font-size: 15px;">Page <?= $page_no; ?> of <?= $total_records_per_page; ?> </p>
                    </div>

                    <div class="pagination">
                        <nav aria-label="..." class="d-flex justify-content-center align-items-center">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="text-dark page-link <?= ($page_no <= 1) ? 'disabled' : ''; ?> "
                                        <?= ($page_no > 1) ? 'href=?page_no=' . $previous_page : ''; ?>
                                        style=" background-color: #F4EFC6; border: 1px solid rgba(0,0,0,0.1); font-size: 15px;">Previous
                                    </a>
                                </li>

                                <?php

                                for ($counter = 1; $counter <= $total_no_of_pages; $counter++) {
                                    ?>

                                    <li class="page-item">
                                        <a class="text-dark page-link" style=" background-color: #F4EFC6; border: 1px solid rgba(0,0,0,0.1); font-size: 15px;"
                                            href="?page_no=<?= $counter; ?>"><?= $counter; ?></a>
                                    </li>

                                    <?php
                                }

                                ?>

                                </li>

                                <li class="page-item">
                                    <a class="text-dark page-link <?= ($page_no >= $total_no_of_pages) ? 'disabled' : ''; ?> "
                                        <?= ($page_no < $total_no_of_pages) ? 'href=?page_no=' . $next_page : ''; ?>
                                        style=" background-color: #F4EFC6; border: 1px solid rgba(0,0,0,0.1); font-size: 15px;">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>            
        </div>
    </div>

    <script>
        function convertTo12Hour(time24) {
            const [hours, minutes, seconds] = time24.split(':');
            let hours12 = parseInt(hours);
            const ampm = hours12 >= 12 ? 'PM' : 'AM';

            hours12 = hours12 % 12;
            hours12 = hours12 ? hours12 : 12; // Handle midnight (00:00)

            const formattedMinutes = minutes.padStart(2, '0');
            return `${hours12}:${formattedMinutes} ${ampm}`;
        }

        function formatSchedule(schedule24) {
            const times24 = schedule24.split(' '); // Split the concatenated times
            if (times24.length === 2) {
                const timeIn24 = times24[0];
                const timeOut24 = times24[1];
                const timeIn12 = convertTo12Hour(timeIn24.substring(0, 5)); // Take only HH:MM
                const timeOut12 = convertTo12Hour(timeOut24.substring(0, 5)); // Take only HH:MM
                return `${timeIn12} - ${timeOut12}`;
            } else {
                // Handle cases where the format might be different
                return schedule24; // Or add more specific handling
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const table = document.querySelector('.table'); // Target the table by its class
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const scheduleCell = rows[i].getElementsByTagName('td')[2]; // The "Schedules" column is the 3rd (index 2)
                const schedule24 = scheduleCell.textContent.trim(); // Get the current text content

                if (scheduleCell && schedule24) {
                    scheduleCell.textContent = formatSchedule(schedule24);
                }
            }
        });
    </script>

    <script>
        const timeOptionsJSON = '<?php echo $time_options_json; ?>';
        const timeOptionsArray = JSON.parse(timeOptionsJSON);
        const selectTimeElement = document.getElementById('selectTime');

        function convertTo12Hour(time24) {
            const [hours, minutes] = time24.split(':');
            let hours12 = parseInt(hours);
            const ampm = hours12 >= 12 ? 'PM' : 'AM';

            hours12 = hours12 % 12;
            hours12 = hours12 ? hours12 : 12; // Handle midnight (00:00)

            const formattedMinutes = minutes.padStart(2, '0');
            return `${hours12}:${formattedMinutes} ${ampm}`;
        }

        function populateTimeSelect() {
            selectTimeElement.innerHTML = '<option value="Select time">Select time</option>'; // Clear existing options

            timeOptionsArray.forEach(timePair => {
                const timeIn24 = timePair.time_in_24;
                const timeOut24 = timePair.time_out_24;
                const timeIn12 = convertTo12Hour(timeIn24);
                const timeOut12 = convertTo12Hour(timeOut24);

                const option = document.createElement('option');
                option.value = `${timeIn24} ${timeOut24}`; // Store the 24-hour format as the value
                option.textContent = `${timeIn12} - ${timeOut12}`;
                selectTimeElement.appendChild(option);
            });
        }

        // Call populateTimeSelect when the modal is shown
        const exampleModal = document.getElementById('exampleModal');
        exampleModal.addEventListener('show.bs.modal', populateTimeSelect);
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <?php

    if (isset($_SESSION["schedule"])) {
        ?>

        <script>
            swal({
                title: "<?php echo $_SESSION["schedule"] ?>",
                icon: "success",
                button: "cancel",
            });
        </script>

        <?php
        unset($_SESSION["schedule"]);
    }else if(isset($_SESSION["error"])){
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

    <script>
        $(document).ready(function () {
            $('#search').on("keyup", function () {
                var search = $(this).val();
                $.ajax({
                    method: 'POST',
                    url: './search_function/search_schedule.php',
                    data: {
                        id: search,
                        employee_name: search
                    },

                    success: function (response) {
                        $("#showdata").html(response);
                        const table = document.querySelector('#showdata'); 
                        const rows = table.getElementsByTagName('tr');
                        for (let i = 0; i < rows.length; i++) {
                            const scheduleCell = rows[i].getElementsByTagName('td')[2];
                            const schedule24 = scheduleCell.textContent.trim();
                            if (scheduleCell && schedule24) {
                                scheduleCell.textContent = formatSchedule(schedule24);
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>