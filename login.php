
<?php
session_start();
require "db.php";

$error = "";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Motor Shop Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-card">
    <h1>🏍️ Motor Shop</h1>
    <h2>Admin Login</h2>

    <p><?php echo $error; ?></p>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username"
               placeholder="Enter username" required>

        <label>Password</label>
        <input type="password" name="password"
               placeholder="Enter password" required>

        <button type="submit" name="login" class="btn">
            LOGIN
        </button>
    </form>
</div>

</body>
</html>
