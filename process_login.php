<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row["password"])) {

            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];

            header("Location: index.php");
            exit();

        } else {
            header("Location: login.php?error=Invalid Password");
            exit();
        }

    } else {
        header("Location: login.php?error=User Not Found");
        exit();
    }
}
?>
