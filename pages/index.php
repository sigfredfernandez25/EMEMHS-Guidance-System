<?php
// Any PHP logic can go here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System's Login</title>
</head>
<body>
    <div>
    <h3>Log-in</h3>    
    <div>
    <form action="../logic/login_logic.php" method="POST">
        <div>
            <label for="first_name">Username or Email:</label><br>
            <input type="text" id="username" name="username" required><br><br>
        </div>

        <div>
            <label for="middle_name">Password:</label><br>
            <input type="password" id="password" name="password"><br><br>
        </div>
        <div>
            <input type="submit" value="Login">
        </div>
    </form>
    <script src="../js/index.js"></script>
</body>
</html>