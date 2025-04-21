<?php

include("../database/connection.php");

$id = mysqli_real_escape_string($conn, $_POST["id"]);

$employee_name = mysqli_real_escape_string($conn, $_POST["employee_name"]);

$payment_date = mysqli_real_escape_string($conn, $_POST["payment_date"]);

$details_reason = mysqli_real_escape_string($conn, $_POST["details_reason"]);

$amount = mysqli_real_escape_string($conn, $_POST["amount"]);

$sql_query = "SELECT * FROM deduction WHERE id LIKE '$id%' OR employee_name LIKE '$employee_name%' OR payment_date LIKE '$payment_date%' OR details_reason LIKE '$details_reason%' OR amount LIKE '$amount%'";

$query = mysqli_query($conn, $sql_query);

$data = "";

while ($row = mysqli_fetch_assoc($query)) {
    $data .= "<tr>
        <td>" . $row['id'] . "</td>
        <td>" . $row['employee_name'] . "</td>
        <td>" . $row['payment_date'] . "</td>
        <td>" . $row['details_reason'] . "</td>
        <td>" . $row['amount'] . "</td>
        <td>
            <div class='dropdown'>
                <a class='btn btn-secondary dropdown-toggle' type='button'
                    data-bs-toggle='dropdown' aria-expanded='false'>
                    Actions
                </a>
                <ul class='dropdown-menu'>
                    <li>
                        <button type='button' class='dropdown-item' data-bs-toggle='modal'
                            data-bs-target='#exampleModal2" . $row["id"] . "'>
                            <i class='fa fa-edit'></i> Edit
                        </button>
                    </li>
                    <li>
                        <form method='POST'>
                            <input type='hidden' name='id' value='" . $row["id"] . "'>
                            <button type='submit' class='dropdown-item' name='delete_deduction'>
                                <i class='fa fa-trash'></i> Delete
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </td>
    </tr>";
}

echo $data;

?>