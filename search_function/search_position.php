<?php

include("../database/connection.php");

$id = mysqli_real_escape_string($conn, $_POST["id"]);

$positions = mysqli_real_escape_string($conn, $_POST["positions"]);

$rate = mysqli_real_escape_string($conn, $_POST["rate"]);

$sql_query = "SELECT * FROM positions WHERE id LIKE '$id%' OR positions LIKE '$positions%' OR rate LIKE '$rate%'";

$query = mysqli_query($conn, $sql_query);

$data = "";

while ($row = mysqli_fetch_assoc($query)) {
    $data .= "<tr>
        <td>" . $row['id'] . "</td>
        <td>" . $row['positions'] . "</td>
        <td>" . $row['rate'] . "</td>
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
                            <button type='submit' class='dropdown-item' name='delete_positions'>
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