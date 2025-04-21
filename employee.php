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
$result_count = mysqli_query($conn, "SELECT COUNT(*) AS total_records FROM employee") or die(mysqli_error($conn));

// total records
$records = mysqli_fetch_assoc($result_count);

// store total_records to a variable
$total_records = $records["total_records"];

// get the total pages
$total_no_of_pages = ceil($total_records / $total_records_per_page);

$sql = "SELECT * FROM employee LIMIT $offset, $total_records_per_page";

$result2 = mysqli_query($conn, $sql) or die(mysqli_error($conn));

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

// Handle insert
if (isset($_POST["submit_employee"])) {
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $position = filter_input(INPUT_POST, "position", FILTER_SANITIZE_SPECIAL_CHARS);
    $rate = filter_input(INPUT_POST, "rate", FILTER_SANITIZE_SPECIAL_CHARS);
    $contact = filter_input(INPUT_POST, "contact", FILTER_SANITIZE_SPECIAL_CHARS);
    $address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_SPECIAL_CHARS);

    $insert_employee = mysqli_query($conn, "INSERT INTO employee (name, position, rate, address, contact, status) VALUES ('$name','$position','$rate','$contact','$address','$status')");

    if ($insert_employee) {
        $_SESSION["new_employee"] = "Added successfully!";
        header("Location: employee.php");
        exit();
    }
}

// Handle update
if (isset($_POST["edit_employee"])) {
    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_SPECIAL_CHARS);
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $position = filter_input(INPUT_POST, "position", FILTER_SANITIZE_SPECIAL_CHARS);
    $rate = filter_input(INPUT_POST, "rate", FILTER_SANITIZE_SPECIAL_CHARS);
    $contact = filter_input(INPUT_POST, "contact", FILTER_SANITIZE_SPECIAL_CHARS);
    $address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, "status", FILTER_SANITIZE_SPECIAL_CHARS);

    $edit_employee = mysqli_query($conn, "UPDATE employee SET name = '$name', position = '$position', rate = '$rate', address = '$address', contact = '$contact', status = '$status' WHERE id = '$employee_id'");

    if ($edit_employee) {
        $_SESSION["new_employee"] = "Update successfully!";
        header("Location: employee.php");
        exit();
    }
}

// Handle delete
if (isset($_POST["delete_employee"])) {
    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_SPECIAL_CHARS);
    $delete_employee = mysqli_query($conn, "DELETE FROM employee WHERE id = '$employee_id'");

    if ($delete_employee) {
        $_SESSION["new_employee"] = "Delete successfully!";
        header("Location: employee.php");
        exit();
    }
}

// 🎯 Insert chart data preparation logic here
$avg_rates = [];
$total_rates = [];

$query = "SELECT positions AS position, rate FROM positions";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $position = $row['position'];
    $rate = (float) $row['rate'];

    // Save average rate data
    $avg_rates[] = [
        "position" => $position,
        "rate" => $rate
    ];

    // Count employees per position
    $employee_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM employee WHERE position = '$position'");
    $employee_data = mysqli_fetch_assoc($employee_count);
    $total_employees = $employee_data['total'];

    // Calculate total wages
    $total_rates[] = [
        "position" => $position,
        "total_wage" => $rate * $total_employees
    ];
}

if (isset($_POST["delete_employee"])) {
    $employee_id = filter_input(INPUT_POST, "employee_id", FILTER_SANITIZE_SPECIAL_CHARS);

    $delete_employee = mysqli_query($conn, "DELETE FROM employee WHERE id = '$employee_id'");

    if ($delete_employee) {
        $_SESSION["success"] = "Delete employee successfully!";
        header("Location: employee.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css_folder/employee.css">

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
            <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
        <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

        <div class="new-employee">
            <div class="text-employee">
                <h3>Employee</h3>
            </div>
            <div class="button-employee">
                <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">New Employee</button>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #F4EFC6">
                        <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">New Employee</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="background-color: #F4EFC6">
                        <form action="" method="post">

                            <div class="row">
                                <div class="col-lg-12">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Name"
                                        style="border: 2px solid #D3891F; background: none;" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Position</label><br>
                                    <select name="position" required>
                                        <option value="">Select position</option>

                                        <?php
                                        $select_position = mysqli_query($conn, "SELECT * FROM positions");

                                        while ($row = mysqli_fetch_assoc($select_position)) {
                                            ?>
                                            <option value="<?php echo $row["positions"] ?>"><?php echo $row["positions"] ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Rate</label><br>
                                    <select name="rate" required>
                                        <option value="">Select rate</option>

                                        <?php
                                        $select_position = mysqli_query($conn, "SELECT * FROM positions");

                                        while ($row = mysqli_fetch_assoc($select_position)) {
                                            ?>
                                            <option value="<?php echo $row["rate"] ?>"><?php echo $row["rate"] ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Enter Address"
                                        style="border: 2px solid #D3891F; background: none;" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Contact</label>
                                    <input type="text" name="contact" class="form-control" placeholder="Enter Contact"
                                        style="border: 2px solid #D3891F; background: none;" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mt-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" name="status" class="form-control" placeholder="Enter Status"
                                        style="border: 2px solid #D3891F; background: none;">
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer" style="background-color: #F4EFC6">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn text-white" style="background-color: #D3891F;"
                            name="submit_employee">Submit</button>
                    </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="parent-employee">

            <div class="table-employee">

                <div class="table-navbar">
                    <div class="table-text">
                        <h4>Employee</h4>
                    </div>

                    <div class="search-bar">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="search" name="search" id="search" placeholder="Search....">
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <!-- <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ...
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Modal -->
                <?php

                $result = mysqli_query($conn, "SELECT * FROM employee");

                while ($row2 = mysqli_fetch_assoc($result)) {

                    ?>
                    <div class="modal fade" id="exampleModal2<?php echo $row2["id"] ?>" tabindex="-1"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #F4EFC6">
                                    <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Edit Employee</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="background-color: #F4EFC6">
                                    <form action="" method="post">

                                        <input type="hidden" name="employee_id" value="<?php echo $row2["id"] ?>">

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" placeholder="Enter Name"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["name"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Position</label>
                                                <input type="text" name="position" class="form-control"
                                                    placeholder="Enter Position"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["position"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Rate</label>
                                                <input type="text" name="rate" class="form-control" placeholder="Enter Rate"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["rate"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Address</label>
                                                <input type="text" name="address" class="form-control"
                                                    placeholder="Enter Address"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["address"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Contact</label>
                                                <input type="text" name="contact" class="form-control"
                                                    placeholder="Enter Contact"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["contact"] ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mt-3">
                                                <label class="form-label">Status</label>
                                                <input type="text" name="status" class="form-control"
                                                    placeholder="Enter Status"
                                                    style="border: 2px solid #D3891F; background: none;"
                                                    value="<?php echo $row2["status"] ?>">
                                            </div>
                                        </div>

                                </div>
                                <div class="modal-footer" style="background-color: #F4EFC6">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn text-white"
                                        style="background-color: #D3891F;" name="edit_employee">Save changes</button>
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
                                <th>Name</th>
                                <th>Position</th>
                                <th>Rate</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="showdata">

                            <?php

                            while ($row = mysqli_fetch_assoc($result2)) {

                                ?>
                                <tr>

                                    <form action="" method="post">
                                        <input type="hidden" name="employee_id" value="<?php echo $row["id"] ?>">
                                        <td><?php echo $row["name"] ?></td>
                                        <td><?php echo $row["position"] ?></td>
                                        <td><?php echo $row["rate"] ?></td>
                                        <td><?php echo $row["address"] ?></td>
                                        <td><?php echo $row["contact"] ?></td>
                                        <td><?php echo $row["status"] ?></td>
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
                                                        <button type="submit" class="dropdown-item"
                                                            name="delete_employee"><i class="fa fa-trash"></i>
                                                            Delete</button>
                                                    </li>
                                                </ul>
                                            </div>


                                        </td>
                                    </form>
                                </tr>
                                <?php


                            }

                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="parent-pagination"
                    style="display: flex; justify-content: space-between; position: relative; top: 25px;">
                    <div class="showing-entries">
                        <p style="font-size: 15px;">Page <?= $page_no; ?> of <?= $total_records_per_page; ?> </p>
                    </div>

                    <div class="pagination">
                        <nav aria-label="..." style="border-radius: 5px;">
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
                                        <a class="text-dark page-link"
                                            style=" background-color: #F4EFC6; border: 1px solid rgba(0,0,0,0.1); font-size: 15px;"
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

            <div class="average-wages">
                <div class="average-hours">
                    <h4>Average Hourly Rates</h4>
                    <canvas id="avgRatesChart"></canvas>
                </div>
                <div class="total-wages">
                    <h4>Total Wage By Positions</h4>
                    <canvas id="totalWageChart"></canvas>
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

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const avgRatesData = <?php echo json_encode($avg_rates); ?>;
        const totalRatesData = <?php echo json_encode($total_rates); ?>;

        // Average Hourly Rates Chart
        const ctxAvg = document.getElementById('avgRatesChart').getContext('2d');
        new Chart(ctxAvg, {
            type: 'bar',
            data: {
                labels: avgRatesData.map(d => d.position),
                datasets: [{
                    label: 'Average Hourly Rate',
                    data: avgRatesData.map(d => d.rate),
                    backgroundColor: '#D3891F'
                }]
            },
            options: {
                indexAxis: 'y', // THIS MAKES IT HORIZONTAL
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Total Wages by Position Chart
        const ctxTotal = document.getElementById('totalWageChart').getContext('2d');
        new Chart(ctxTotal, {
            type: 'bar',
            data: {
                labels: totalRatesData.map(d => d.position),
                datasets: [{
                    label: 'Total Wage',
                    data: totalRatesData.map(d => d.total_wage),
                    backgroundColor: '#D3891F'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <?php

    if (isset($_SESSION["new_employee"])) {
        ?>

        <script>
            swal({
                title: "<?php echo $_SESSION["new_employee"] ?>",
                icon: "success",
                button: "cancel",
            });
        </script>

        <?php
        unset($_SESSION["new_employee"]);
    }

    ?>

    <script>
        $(document).ready(function () {
            $('#search').on("keyup", function () {
                var search = $(this).val();
                $.ajax({
                    method: 'POST',
                    url: './search_function/search_employee.php',
                    data: {
                        name: search,
                        position: search,
                        rate: search,
                        address: search,
                        contact: search,
                        status: search
                    },

                    success: function (response) {
                        $("#showdata").html(response);
                    }
                });
            });
        });
    </script>
</body>

</html>