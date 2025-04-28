<?php
// Any PHP logic can go here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
</head>
<body>
    <form action="../logic/register_logic.php" method="POST">
        <div>
            <label for="first_name">First Name:</label><br>
            <input type="text" id="first_name" name="first_name" required><br><br>
        </div>

        <div>
            <label for="middle_name">Middle Name:</label><br>
            <input type="text" id="middle_name" name="middle_name"><br><br>
        </div>

        <div>
            <label for="last_name">Last Name:</label><br>
            <input type="text" id="last_name" name="last_name" required><br><br>
        </div>

        <div>
            <label for="grade_level">Grade Level:</label><br>
            <select id="grade_level" name="grade_level" required>
                <option value="">Select Grade Level</option>
                <option value="7">Grade 7</option>
                <option value="8">Grade 8</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
            </select><br><br>
        </div>

        <div>
            <label for="section">Section:</label><br>
            <input type="text" id="section" name="section" required><br><br>
        </div>

        <div>
            <label for="email">Email Address:</label><br>
            <input type="email" id="email" name="email" oninput="validateEmail()" required><br><br>
            <input type="button" id="getCode" value="Send Code" onclick="executeSendCode()">
            <span id="email_status" style="color: red;"></span>
        </div>
        <div>
            <label for="phone">Email Verification Code:</label><br>
            <input type="text" id="code" name="code" oninput="validateCode()" required><br><br>
            <span id="code_status" style="color: red;"></span>
        </div>
        <div>
            <label for="phone">Phone Number:</label><br>
            <input type="tel" id="phone" name="phone" pattern="[0-9]*" required><br><br>
        </div>

        <div>
            <label for="parent_name">Parent/Guardian Name:</label><br>
            <input type="text" id="parent_name" name="parent_name" required><br><br>
        </div>

        <div>
            <label for="parent_contact">Parent/Guardian Contact Number:</label><br>
            <input type="tel" id="parent_contact" name="parent_contact" pattern="[0-9]*" required><br><br>
        </div>

        <div>
            <label for="password">Password:</label><br>
            <input type="text" id="password" name="password" oninput="confirmPassword()" required><br><br>
        </div>

        <div>
            <label for="confirm_password">Confirm Password:</label><br>
            <input type="text" id="confirm_password" name="confirm_password" oninput="confirmPassword()" required><br><br>
            <span id="password_match_status" style="color: red;"></span>
        </div>

        <div>
            <input type="submit" id="register" value="Register">
        </div>
    </form>
    <script type="text/javascript" src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <script src="../js/index.js"></script>
</body>
</html>