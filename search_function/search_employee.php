<?php

include("../database/connection.php");

$name = mysqli_real_escape_string($conn, $_POST["name"]);

$position = mysqli_real_escape_string($conn, $_POST["position"]);

$rate = mysqli_real_escape_string($conn, $_POST["rate"]);

$address = mysqli_real_escape_string($conn, $_POST["address"]);

$contact = mysqli_real_escape_string($conn, $_POST["contact"]);

$status = mysqli_real_escape_string($conn, $_POST["status"]);

$sql_query = "SELECT * FROM employee WHERE name LIKE '$name%' OR position LIKE '$position%' OR rate LIKE '$rate%' OR address LIKE '$address%' OR contact LIKE '$contact%' OR status LIKE '$status%'";
$query = mysqli_query($conn, $sql_query);

$data = "";

while ($row = mysqli_fetch_assoc($query)) {
    $data .= "<tr>
        <td>" . $row['name'] . "</td>
        <td>" . $row['position'] . "</td>
        <td>" . $row['rate'] . "</td>
        <td>" . $row['address'] . "</td>
        <td>" . $row['contact'] . "</td>
        <td>" . $row['status'] . "</td>
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
                            <button type='submit' class='dropdown-item' name='delete_employee'>
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