<?php
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Redirect if already logged in
if (isset($_SESSION['logged_account'])) {
    if ($_SESSION['logged_account']['type'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: main_menu.php');
    }
    exit();
}

// Handle form submission
$loginErrors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $formData['email'] = $email;
    
    if (empty($email)) {
        $loginErrors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginErrors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $loginErrors['password'] = 'Password is required';
    }
    
    if (empty($loginErrors)) {
        // Check credentials
        $stmt = $conn->prepare("SELECT email, password, type, status FROM account_table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $account = $result->fetch_assoc();
            
            if (password_verify($password, $account['password'])) {
                if ($account['status'] === 'Approved' || $account['type'] === 'admin') {
                    // Login successful
                    $_SESSION['logged_account'] = [
                        'email' => $account['email'],
                        'type' => $account['type'],
                        'status' => $account['status']
                    ];
                    
                    if ($account['type'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: main_menu.php');
                    }
                    exit();
                } else {
                    $loginErrors['general'] = 'Your account is pending approval or has been rejected.';
                }
            } else {
                $loginErrors['general'] = 'Invalid email or password.';
            }
        } else {
            $loginErrors['general'] = 'Invalid email or password.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Admin Theme -->
    <link rel="stylesheet" href="css/admin-theme.css">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 50%, var(--secondary-800) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: var(--space-6);
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-3xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: var(--space-12) var(--space-8);
            text-align: center;
        }
        
        .login-logo {
            font-size: 4rem;
            color: var(--primary-600);
            margin-bottom: var(--space-6);
        }
        
        .login-title {
            font-family: var(--font-secondary);
            font-size: 2rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: var(--space-2);
        }
        
        .login-subtitle {
            color: var(--neutral-600);
            margin-bottom: var(--space-8);
        }
        
        .form-floating {
            margin-bottom: var(--space-4);
        }
        
        .form-floating .form-control {
            border: 2px solid var(--neutral-300);
            border-radius: var(--radius-lg);
            padding: 1.5rem var(--space-4) 0.75rem;
            height: auto;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-floating label {
            color: var(--neutral-500);
            padding: 1rem var(--space-4);
        }
        
        .login-btn {
            width: 100%;
            padding: var(--space-4);
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            margin-top: var(--space-4);
        }
        
        .features-list {
            text-align: left;
            margin-top: var(--space-8);
        }
        
        .features-list li {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-3);
            color: var(--neutral-700);
        }
        
        .features-list i {
            color: var(--primary-600);
            font-size: 1.2rem;
        }
        
        .error-message {
            background: var(--error-50);
            border: 1px solid var(--error-200);
            color: var(--error-800);
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-4);
            font-size: 0.875rem;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="login-container">
        <div class="login-card fade-in">
            <!-- Logo and Title -->
            <div class="login-logo">
                <i class="bi bi-mortarboard"></i>
            </div>
            <h1 class="login-title">Welcome Back!</h1>
            <p class="login-subtitle">Connect with your alumni community and stay updated with the latest events and opportunities.</p>
            
            <!-- Error Messages -->
            <?php if (!empty($loginErrors['general'])): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($loginErrors['general']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" novalidate>
                <div class="form-floating">
                    <input type="email" 
                           class="form-control <?php echo isset($loginErrors['email']) ? 'is-invalid' : ''; ?>" 
                           id="email" 
                           name="email" 
                           placeholder="name@example.com"
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                           required>
                    <label for="email">
                        <i class="bi bi-envelope me-2"></i>Email Address
                    </label>
                    <?php if (isset($loginErrors['email'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($loginErrors['email']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-floating">
                    <input type="password" 
                           class="form-control <?php echo isset($loginErrors['password']) ? 'is-invalid' : ''; ?>" 
                           id="password" 
                           name="password" 
                           placeholder="Password"
                           required>
                    <label for="password">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <?php if (isset($loginErrors['password'])): ?>
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($loginErrors['password']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary login-btn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button>
            </form>
            
            <!-- Registration Link -->
            <div class="text-center mt-3">
                <p class="text-muted mb-2">Don't have an account?</p>
                <a href="registration.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person-plus"></i> Register as Alumni
                </a>
            </div>
            
            <!-- Features List -->
            <ul class="features-list">
                <li>
                    <i class="bi bi-people"></i>
                    <span>Connect with Alumni</span>
                </li>
                <li>
                    <i class="bi bi-calendar-event"></i>
                    <span>Discover Events</span>
                </li>
                <li>
                    <i class="bi bi-chat-dots"></i>
                    <span>Stay Connected</span>
                </li>
            </ul>
        </div>
        

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Add form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid = true;
            
            // Reset previous validation states
            email.classList.remove('is-invalid');
            password.classList.remove('is-invalid');
            
            // Validate email
            if (!email.value.trim()) {
                email.classList.add('is-invalid');
                isValid = false;
            } else if (!email.value.includes('@')) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate password
            if (!password.value.trim()) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Add loading state to button
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.querySelector('.login-btn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';
            btn.disabled = true;
        });
    </script>
</body>
</html>