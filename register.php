<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error_message = "Email already registered.";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, password, role)
                     VALUES (?, ?, ?, 'user')"
                );

                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $_SESSION['success_message'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
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
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #1f293a;
            font-family: Arial, sans-serif;
        }

        .box {
            width: 400px;
            background: #2c4766;
            padding: 40px;
            border-radius: 10px;
        }

        h2 {
            color: #0ef;
            text-align: center;
            margin-bottom: 30px;
        }

        .input-box {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            border: none;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            border: none;
            background: #0ef;
            color: #1f293a;
            font-weight: bold;
            cursor: pointer;
        }

        .error {
            color: #ff4d4d;
            margin-bottom: 15px;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            color: #fff;
        }

        .login-link a {
            color: #0ef;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Register</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-box">
            <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="input-box">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <button type="submit" class="btn">Register</button>

        <div class="login-link">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </form>
</div>

</body>
</html>
