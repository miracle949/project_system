<?php

include("./database/connection.php");

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: index.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css_folder/style.css">
</head>

<body>

    <div class="side-bar">
        <div class="nav-logo">
            <?php

                $select_image = mysqli_query($conn, "SELECT * FROM login");

                while($row = mysqli_fetch_assoc($select_image)){

            ?>
            <img src="./uploads/<?php echo $row["image"] ?>" width="170px" height="170px" style="border-radius: 50%;">
            <?php
                }
            ?>
        </div>

        <div class="nav-list">
            <h3>Reports</h3>

            <ul>
                <li>
                    <a href="dashboard.php" id="dashboard">
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <h3>Manage</h3>

            <ul>
                <li>
                    <a href="attendance.php" id="attendance">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Attendance</span>
                    </a>
                </li>

                <li>
                    <a href="employee.php" id="employee">
                        <i class="fa fa-users"></i>
                        <span>Employee</span>
                    </a>
                </li>

                <li>
                    <a href="BonusDeduction.php" id="bonus">
                        <i class="fa fa-file"></i>
                        <span>Bonus & Deduction</span>
                    </a>
                </li>

                <li>
                    <a href="positions.php" id="position">
                        <i class="fas fa-address-card"></i>
                        <span>Positions</span>
                    </a>
                </li>
            </ul>

            <h3>Printables</h3>

            <ul>
                <li>
                    <a href="payroll.php" id="payroll">
                        <i class="fa fa-file-invoice-dollar"></i>
                        <span>Payroll</span>
                    </a>
                </li>

                <li>
                    <a href="schedule.php" id="schedule">
                        <i class="fa fa-business-time"></i>
                        <span>Schedule</span>
                    </a>
                </li>
            </ul>

            <form action="" method="post">
                <button type="submit" name="logout"><i class="fa fa-sign-out"></i> Logout</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

        
    <script>
        const dashboard = document.getElementById("dashboard");

        dashboard.addEventListener("click", e => {

            console.log("Hello");

        });
    </script>

</body>

</html>