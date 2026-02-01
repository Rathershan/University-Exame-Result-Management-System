<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "university_exam");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query admin_users table (change table name if needed)
    $sql = "SELECT * FROM admin_users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php"); // Redirect to admin dashboard
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GWUIM Exam System</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .login-header img {
            width: 80px;
            margin-bottom: 15px;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: #7f8c8d;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1a252f;
        }

        .alert-danger {
            background: #f8d7da;
            color: var(--accent-color);
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid var(--accent-color);
        }

        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #7f8c8d;
            font-size: 13px;
            text-decoration: none;
        }

        .forgot-password a:hover {
            color: var(--secondary-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 576px) {
            .login-card {
                border-radius: 0;
                box-shadow: none;
            }
            
            body {
                padding: 0;
                background: white;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <img src="gwuim.png" alt="GWUIM Logo">
            <h2>Admin Portal</h2>
        </div>
        
        <div class="login-body">
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="username" name="username" 
                          
                           placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password" 
                           required placeholder="Enter your password">
                </div>
                
                <div class="forgot-password">
                    <a href="">Forgot Password?</a>
                </div>
                
                <button type="submit" name="login" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-3">
        <a href="home.php" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Back to Home
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple animation on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.login-container').style.opacity = '1';
    });
    
    // Focus on username field when page loads
    document.getElementById('username').focus();
</script>
</body>
</html>