<?php
session_start();
require_once 'config/database.php';
require_once 'config/mail.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error_message = "All fields are required except phone number";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $error_message = "Username or email already exists";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                    // Send welcome email
                    if (sendRegistrationEmail($email, $username)) {
                        $_SESSION['success_message'] = "Registration successful! Welcome email sent.";
                    } else {
                        $_SESSION['success_message'] = "Registration successful! Welcome email could not be sent.";
                    }
                    header('Location: login.php');
                    exit();
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Estate Hub</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #1f293a;
        }

        .container {
            position: relative;
            width: 400px;
            height: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container span {
            position: absolute;
            background: #2c4766;
            border-radius: 8px;
        }

        /* Top border */
        .container span:nth-child(1) {
            top: 0;
            left: 0;
            width: 400px;
            height: 6px;
            background: linear-gradient(90deg, #2c4766, #0ef, #2c4766);
            background-size: 200% 100%;
            animation: flow 2s linear infinite;
        }

        /* Right border */
        .container span:nth-child(2) {
            top: 0;
            right: 0;
            width: 6px;
            height: 600px;
            background: linear-gradient(180deg, #2c4766, #0ef, #2c4766);
            background-size: 100% 200%;
            animation: flow 2s linear infinite;
            animation-delay: 0.5s;
        }

        /* Bottom border */
        .container span:nth-child(3) {
            bottom: 0;
            right: 0;
            width: 400px;
            height: 6px;
            background: linear-gradient(90deg, #2c4766, #0ef, #2c4766);
            background-size: 200% 100%;
            animation: flow 2s linear infinite;
            animation-delay: 1s;
        }

        /* Left border */
        .container span:nth-child(4) {
            bottom: 0;
            left: 0;
            width: 6px;
            height: 600px;
            background: linear-gradient(180deg, #2c4766, #0ef, #2c4766);
            background-size: 100% 200%;
            animation: flow 2s linear infinite;
            animation-delay: 1.5s;
        }

        @keyframes flow {
            0% {
                background-position: 0% 0%;
                box-shadow: 0 0 10px #0ef,
                           0 0 20px #0ef,
                           0 0 40px #0ef;
            }
            50% {
                background-position: 100% 0%;
                box-shadow: 0 0 20px #0ef,
                           0 0 40px #0ef,
                           0 0 60px #0ef;
            }
            100% {
                background-position: 0% 0%;
                box-shadow: 0 0 10px #0ef,
                           0 0 20px #0ef,
                           0 0 40px #0ef;
            }
        }

        .register-box {
            position: absolute;
            width: 400px;
            z-index: 1;
        }

        form {
            width: 100%;
            padding: 0 50px;
        }

        h2 {
            font-size: 2em;
            color: #0ef;
            text-align: center;
            margin-bottom: 30px;
        }

        .input-box {
            position: relative;
            margin: 25px 0;
        }

        input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: 2px solid #2c4766;
            outline: none;
            border-radius: 40px;
            font-size: 1em;
            color: #fff;
            padding: 0 20px;
            transition: .5s ease;
        }

        input:focus,
        input:valid {
            border-color: #0ef;
        }

        label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            font-size: 1em;
            color: #fff;
            pointer-events: none;
            transition: .5s ease;
        }

        input:focus~label,
        input:valid~label {
            top: 1px;
            font-size: .8em;
            background: #1f293a;
            padding: 0 6px;
            color: #0ef;
        }

        .btn {
            width: 100%;
            height: 45px;
            background: #0ef;
            border: none;
            outline: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1em;
            color: #1f293a;
            font-weight: 600;
            margin-top: 20px;
        }

        .login-link {
            margin: 20px 0 10px;
            text-align: center;
            color: #fff;
        }

        .login-link a {
            font-size: 1em;
            color: #0ef;
            text-decoration: none;
            font-weight: 600;
        }

        .error-message {
            color: #ff3333;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9em;
            background: rgba(255, 51, 51, 0.1);
            padding: 10px;
            border-radius: 20px;
        }

        .success-message {
            color: #00ff00;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.9em;
            background: rgba(0, 255, 0, 0.1);
            padding: 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <span></span>
        <span></span>
        <span></span>
        <span></span>

        <div class="register-box">
            <h2>Register</h2>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="input-box">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <input type="text" name="full_name" required>
                    <label>Full Name</label>
                </div>
                <div class="input-box">
                    <input type="tel" name="phone">
                    <label>Phone (Optional)</label>
                </div>
                <div class="input-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" required>
                    <label>Confirm Password</label>
                </div>
                <button type="submit" class="btn">Register</button>
                <div class="login-link">
                    <br>
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 
