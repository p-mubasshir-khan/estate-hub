<?php
session_start();
require_once 'config/database.php';
require_once 'config/mail.php'; // include the PHPMailer mail config

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error_message = "Please enter your email address";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address";
    } else {
        try {
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate new random password
                $new_password = generateRandomPassword();
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in database
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($update_stmt->execute([$hashed_password, $user['id']])) {
                    // Send new password via email
                    if (sendPasswordRecoveryEmail($email, $user['username'], $new_password)) {
                        $success_message = "A new password has been sent to your email address.";
                    } else {
                        $error_message = "Failed to send email. Please try again later.";
                    }
                } else {
                    $error_message = "Failed to update password. Please try again.";
                }
            } else {
                $error_message = "No account found with this email address";
            }
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Function to generate random password
function generateRandomPassword($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Estate Hub</title>
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
            width: 256px;
            height: 256px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container span {
            position: absolute;
            left: 0;
            width: 32px;
            height: 6px;
            background: #2c4766;
            border-radius: 8px;
            transform-origin: 128px;
            transform: scale(2.2) rotate(calc(var(--i) * (360deg / 50)));
            animation: blink 3s linear infinite;
            animation-delay: calc(var(--i) * (3s / 50));
        }

        @keyframes blink {
            0% {
                background: #0ef;
            }
            25% {
                background: #2c4766;
            }
        }

        .forgot-box {
            position: absolute;
            width: 400px;
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
        }

        .login-link {
            margin: 20px 0 10px;
            text-align: center;
            color: #fff;
        }

        .login-link a {
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
        <?php for($i = 0; $i < 50; $i++): ?>
            <span style="--i:<?php echo $i; ?>"></span>
        <?php endfor; ?>

        <div class="forgot-box">
            <h2>Forgot Password</h2>
            
            <?php if($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if($success_message): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-box">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <button type="submit" class="btn">Send New Password</button>
                <div class="login-link">
                    Remember your password? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 