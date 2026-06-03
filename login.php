<?php
session_start();
include("db.php");

if(isset($_SESSION["user_id"])){
    header("Location: index.php");
    exit();
}

$message = "";

/* REGISTER LOGIC */
if(isset($_POST["register"])){

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $message = "Username or Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username,$email,$password);
        
        if($stmt->execute()){
            $message = "Account Created Successfully! Please Login.";
        } else {
            $message = "Registration Failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login & Registration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
* { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

body {
    background: #f6f5f7;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 14px 28px rgba(0,0,0,0.25);
    position: relative;
    overflow: hidden;
    width: 768px;
    min-height: 480px;
}

.form-container {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    transition: 0.6s ease-in-out;
}

.sign-in-container { left: 0; z-index: 2; }
.sign-up-container { left: 0; opacity: 0; z-index: 1; }

.container.right-panel-active .sign-in-container {
    transform: translateX(100%);
    opacity: 0;
}
.container.right-panel-active .sign-up-container {
    transform: translateX(100%);
    opacity: 1;
    z-index: 5;
}

form {
    background-color: #fff;
    display: flex;
    flex-direction: column;
    padding: 0 50px;
    justify-content: center;
    height: 100%;
    text-align: center;
}

h2 { margin-bottom: 15px; }

.input-field {
    background: #eee;
    margin: 8px 0;
    border-radius: 4px;
}

.input-field input {
    border: none;
    padding: 12px;
    width: 100%;
    outline: none;
    background: transparent;
}

.main-btn {
    border: none;
    padding: 12px;
    background: #6d98f7;
    color: white;
    margin-top: 10px;
    cursor: pointer;
    border-radius: 5px;
}

.overlay-container {
    position: absolute;
    top: 0;
    left: 50%;
    width: 50%;
    height: 100%;
    overflow: hidden;
    transition: 0.6s ease-in-out;
}

.container.right-panel-active .overlay-container {
    transform: translateX(-100%);
}

.overlay {
    background: linear-gradient(to right, #5c85e6, #6d98f7);
    position: relative;
    left: -100%;
    width: 200%;
    height: 100%;
    transition: 0.6s ease-in-out;
}

.container.right-panel-active .overlay {
    transform: translateX(50%);
}

.overlay-panel {
    position: absolute;
    width: 50%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
}

.overlay-right { right: 0; }
.overlay-left { left: 0; }

.ghost {
    background: transparent;
    border: 1px solid white;
    padding: 10px 25px;
    color: white;
    cursor: pointer;
}

.message {
    color:red;
    font-size:14px;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="container" id="container">

    <!-- REGISTER -->
    <div class="form-container sign-up-container">
        <form method="POST">
            <h2>Create Account</h2>
            <div class="message"><?php echo $message; ?></div>

            <div class="input-field">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-field">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-field">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" name="register" class="main-btn">Register</button>
        </form>
    </div>

    <!-- LOGIN -->
    <div class="form-container sign-in-container">
        <form action="process_login.php" method="POST">
            <h2>Login</h2>

            <?php if(isset($_GET["error"])): ?>
                <div class="message"><?php echo htmlspecialchars($_GET["error"]); ?></div>
            <?php endif; ?>

            <div class="input-field">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input-field">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="main-btn">Login</button>
        </form>
    </div>

    <!-- OVERLAY -->
    <div class="overlay-container">
        <div class="overlay">

            <div class="overlay-panel overlay-left">
                <h2>Welcome Back!</h2>
                <button class="ghost" id="signIn">Login</button>
            </div>

            <div class="overlay-panel overlay-right">
                <h2>Hello, Welcome!</h2>
                <button class="ghost" id="signUp">Register</button>
            </div>

        </div>
    </div>

</div>

<script>
const signUp = document.getElementById('signUp');
const signIn = document.getElementById('signIn');
const container = document.getElementById('container');

signUp.onclick = () => container.classList.add("right-panel-active");
signIn.onclick = () => container.classList.remove("right-panel-active");
</script>

</body>
</html>
