<?php

session_start();
include("./database/connection.php");

if (isset($_POST["login"])) {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $result = mysqli_query($conn, "SELECT * FROM login WHERE username = '$username'");

    if (empty($username) || empty($password)) {

        $_SESSION["error"] = "Please fill out the form completely!";

    } else {
        if (mysqli_num_rows($result) > 0) {

            $user_data = $result->fetch_assoc();

            if (password_verify($password, $user_data["password"])) {

                $_SESSION["username"] = $user_data["username"];

                $_SESSION["password"] = $user_data["password"];

                $_SESSION["success_login"] = "Login successfully";

                // header("Location: index.php");

                // exit();

            } else {
                $_SESSION["error"] = "password is incorrect!";
            }
        } else {
            $_SESSION["error"] = "user not found!";
        }
    }

} else {

    unset($_SESSION["error"]);

}

if (isset($_SESSION["username"]) && !isset($_SESSION["success_login"])) {
    header("Location: dashboard.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lobster&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playwrite+AU+SA:wght@100..400&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&family=Skranji:wght@700&display=swap');


        * {
            font-family: "Poppins", sans-serif;
        }

        main {
            background-image: url('images/backgroundimg.png');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        main .main-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #FFF7B5;
            width: 1000px;
            height: 550px;
            border-radius: 20px;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px;
        }

        main .main-container .container-img {
            width: 525px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        main .main-container .container-img .image {
            width: 400px;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -2px, rgba(0, 0, 0, 0.3) 0px 3px 7px -3px;
            border-radius: 20px;
        }

        main .main-container .container-img .image img {
            width: 300px;
            height: 300px;
        }

        main .main-container .main-form {
            width: 525px;
            padding: 20px 40px 40px 40px;
        }

        main .main-container .main-form .image-header {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        main .main-container .main-form .image-header img {
            width: 170px;
            height: 170px;
        }

        main .main-container .main-form h3 {
            color: #824B06;
            text-align: center;
        }

        main .main-container .input-group .fa {
            color: #D3891F;
        }

        main .main-container .main-form form .input-group .input-group-text {
            /* border: 2px solid #af834c; */
            border-right: 2px solid #af834c;
        }

        main .main-container .input-group span {
            background-color: #E3E1D1;

        }

        span.input-group-text {
            border-right: 1px solid #fffffe;
        }

        main .main-container .main-form .input-group input {
            background-color: #E3E1D1;
        }

        main .main-container .main-form .button input {
            background-color: #D3891F;
            color: #fffffe;
            outline: none;
            font-weight: 600;
        }

        div.alert.alert-warning.alert-dismissible.show {
            margin: 0px 0px 0px;
        }
    </style>
</head>

<body>

    <main>
        <div class="main-container">

            <div class="container-img">
                <div class="image">
                    <img src="images/logo.png" alt="">
                </div>
            </div>

            <!-- terms of service -->

            <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
                tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-second">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #F4EFC6; border: 1px solid #F4EFC6;"">
                                <h4>Terms of Service</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="background-color: #F4EFC6; border: 1px solid #F4EFC6;">
                                <p>
                                    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Assumenda sunt accusantium et blanditiis tenetur eius commodi, vitae harum magni recusandae distinctio error quis soluta perspiciatis laboriosam eum praesentium nobis asperiores. Aliquam, odit. Aliquid tenetur vero, perspiciatis impedit id eveniet hic possimus enim ducimus excepturi rem, ullam non reprehenderit, quasi aspernatur! Non ducimus necessitatibus laboriosam rerum ea modi nisi iusto accusamus eius vitae dignissimos, nobis, culpa suscipit corrupti odit voluptatem, dicta debitis omnis quod odio. Inventore voluptatem vitae nulla unde necessitatibus beatae adipisci doloribus odit sed obcaecati molestiae repellat rem, itaque ipsam hic delectus fuga iusto harum minima maiores deserunt eveniet!
                                </p>
                            </div>
                            <div class="modal-footer d-flex align-items-start justify-content-start"
                                style="background-color: #F4EFC6; border: 1px solid #F4EFC6;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- terms and conditions -->

            <div class="modal fade" id="exampleModalToggle2" aria-hidden="true"
                aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-first">
                        <div class="modal-content" style="height: 650px; overflow-y: auto;">
                            <div class="modal-header" style="background-color: #F4EFC6; border: 1px solid #F4EFC6;">
                                <h4>Terms and Conditions</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" style="background-color: #F4EFC6; border: 1px solid #F4EFC6;">
                                <p>
                                    Lorem ipsum dolor sit amet consectetur, adipisicing elit. Pariatur soluta harum dolor est repellendus deserunt expedita sapiente placeat eos vitae! Possimus eius fugit accusamus ipsam sapiente, labore a tempora obcaecati rerum ipsa quae laudantium atque. Vero minima quos exercitationem maiores voluptatem nesciunt quibusdam velit maxime perferendis. Hic, aperiam? Qui ducimus autem voluptatibus, perspiciatis perferendis blanditiis? Hic aperiam rem impedit temporibus deleniti commodi aut tempora autem! Nihil, autem, aliquid tempora cum fuga incidunt, pariatur repudiandae voluptatem cupiditate molestiae libero error nulla sapiente! Similique cupiditate placeat blanditiis aspernatur tempora, labore natus voluptatibus officia ut eum fugit iure amet architecto molestias aperiam quaerat officiis magni doloribus sequi deserunt magnam ullam accusamus laudantium explicabo? Beatae quidem alias voluptatibus, iste sint dignissimos quod id pariatur inventore delectus. Sit totam cum quasi ratione delectus nesciunt voluptatibus at deleniti eaque perferendis obcaecati sequi laborum veniam ad, similique ea eius aliquid illum id? Natus odio eligendi dolorum aliquam vel laudantium, ullam quas obcaecati sunt cum non earum similique omnis saepe excepturi corporis, fugiat ipsa modi at ducimus voluptatem quaerat? Fugiat, sint non quae vitae at maxime pariatur sequi, incidunt eveniet necessitatibus facilis dolor omnis velit maiores. Asperiores amet est sequi, facere nam velit laudantium. Deserunt totam placeat rem.
                                </p>
                            </div>
                            <div class="modal-footer d-flex align-items-start justify-content-start"
                                style="background-color: #F4EFC6; border: 1px solid #F4EFC6;">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-form">

                <form action="" method="post">
                    <div class="image-header">
                        <img src="images/loginlogo.png" alt="">
                    </div>

                    <h3>Admin Login</h3>

                    <?php

                    if (isset($_SESSION["error"])) {
                        ?>

                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION["error"] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <?php
                        unset($_SESSION["error"]);
                    }

                    ?>

                    <label class="form-label mt-2">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username">
                    </div>

                    <label class="form-label mt-3">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter password">
                    </div>

                    <div class="remember-forget mt-3">

                        <div class="checkbox" style="display: flex; align-items: start;">
                            <input type="checkbox" name="checkbox" required style="margin-top: 0.3rem;">
                            <label style="margin-left: 0.4rem;">I agree to
                                <span data-bs-target="#exampleModalToggle" data-bs-toggle="modal"
                                    style="text-decoration: underline; cursor: pointer;">Terms of
                                    Service</span> and
                                <span data-bs-target="#exampleModalToggle2" data-bs-toggle="modal"
                                    style="text-decoration: underline; cursor: pointer;">Terms and
                                    Conditions</span>
                            </label>
                        </div>
                    </div>

                    <div class="button mt-3">
                        <input type="submit" value="Log In" class="form-control" name="login">
                    </div>
                </form>

            </div>
        </div>
    </main>


    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>


    <?php

    if (isset($_SESSION["success_login"])) {
        ?>
        <script>
            swal({
                title: "<?php echo $_SESSION["success_login"] ?>",
                icon: "success",
                button: "Okay",
            }).then((value) => {
                window.location.href = "dashboard.php";
            });
        </script>
        <?php
        unset($_SESSION["success_login"]);
    }

    ?>
</body>

</html>