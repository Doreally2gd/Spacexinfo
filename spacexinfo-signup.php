<?php
/*
Template Name: Spacexinfo Signup
Template Post Type: page
*/

// 1. DATABASE CONNECTION
$servername = "localhost";
$dbusername = "spacenet_spacexinfo"; 
$dbpassword = "@#passNet"; 
$dbname = "spacenet_spacexinfo_userdb";

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    $conn->set_charset("utf8mb4"); 
} catch (Exception $e) {
    die("<h3>Database Connection Error</h3><p>Please check your config variables.</p><small>" . $e->getMessage() . "</small>");
}

// 2. HANDLE FORM SUBMISSION
$signup_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use null coalescing operator to avoid "undefined index" warnings
    $username  = $_POST["username"] ?? '';
    $firstname = $_POST["firstname"] ?? '';
    $lastname  = $_POST["lastname"] ?? '';
    $phone     = $_POST["phone"] ?? '';
    $email     = $_POST["email"] ?? '';
    $password  = $_POST["password"] ?? '';
    
    // Check if empty (Server-side validation)
    if(!empty($username) && !empty($email) && !empty($password)) {
        
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Check if username or email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $signup_message = "<div style='color: #ff4757; text-align:center; margin: 10px;'>Username or Email already exists!</div>";
        } else {
            // Insert
            $sql = "INSERT INTO users (username, firstname, lastname, phone, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("ssssss", $username, $firstname, $lastname, $phone, $email, $hashed);
                
                if ($stmt->execute()) {
                    // --- SUCCESS & REDIRECT SECTION ---
                    // This closes the database and redirects the user
                    $stmt->close();
                    $conn->close();
                    
                    echo "<script>
                        alert('Signup successful! Redirecting to Login...');
                        window.location.href = 'https://spacexinfo.net/log-in/';
                    </script>";
                    exit(); // Stop the rest of the page from loading
                    // ----------------------------------
                    
                } else {
                    $signup_message = "<div style='color: #ff4757; text-align:center; margin: 10px;'>Error: " . $stmt->error . "</div>";
                }
                $stmt->close(); // Only close here if execute failed
            }
        }
        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spacexinfo - Registration</title>
    <style>
        /* YOUR ORIGINAL CSS - KEPT EXACTLY THE SAME */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0e27;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(0, 255, 135, 0.15), transparent);
            top: -200px; right: -200px; border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }
        body::after {
            content: ''; position: absolute; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.15), transparent);
            bottom: -150px; left: -150px; border-radius: 50%;
            animation: pulse 5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .container { position: relative; z-index: 1; max-width: 500px; width: 100%; padding: 2rem; }
        .registration-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        .logo {
            font-size: 2rem; font-weight: bold;
            background: linear-gradient(135deg, #00ff87, #00d4ff);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.5rem; text-align: center;
        }
        .registration-card h2 { font-size: 1.8rem; margin-bottom: 0.5rem; color: #fff; text-align: center; }
        .registration-card > p { color: #b8bcc8; margin-bottom: 2rem; text-align: center; }
        .step-indicator { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 2rem; }
        .step-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); transition: all 0.3s; }
        .step-dot.active { width: 30px; border-radius: 5px; background: linear-gradient(90deg, #00ff87, #00d4ff); }
        .form-section { display: none; }
        .form-section.active { display: block; animation: fadeIn 0.4s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #fff; font-weight: 500; }
        .form-group input {
            width: 100%; padding: 0.9rem 1.2rem;
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px; color: #fff; font-size: 1rem; transition: all 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #00ff87; background: rgba(255, 255, 255, 0.08); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: #b8bcc8; cursor: pointer; font-size: 1.2rem;
        }
        .password-toggle:hover { color: #00ff87; }
        .password-strength { margin-top: 0.5rem; height: 4px; background: rgba(255, 255, 255, 0.1); border-radius: 2px; overflow: hidden; display: none; }
        .password-strength.show { display: block; }
        .password-strength-bar { height: 100%; width: 0%; transition: all 0.3s; border-radius: 2px; }
        .strength-weak { width: 33%; background: #ff4757; }
        .strength-medium { width: 66%; background: #ffa502; }
        .strength-strong { width: 100%; background: #00ff87; }
        .password-requirements { margin-top: 0.8rem; padding: 0.8rem; background: rgba(255, 255, 255, 0.03); border-radius: 8px; font-size: 0.85rem; }
        .requirement { color: #6b7280; margin-bottom: 0.3rem; display: flex; align-items: center; gap: 0.5rem; }
        .requirement.met { color: #00ff87; }
        .requirement-icon { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #6b7280; display: flex; align-items: center; justify-content: center; font-size: 10px; }
        .requirement.met .requirement-icon { border-color: #00ff87; background: #00ff87; color: #0a0e27; }
        .error-message { background: rgba(255, 71, 87, 0.1); border: 1px solid rgba(255, 71, 87, 0.3); color: #ff4757; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; display: none; }
        .btn { width: 100%; padding: 1rem; border: none; border-radius: 10px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #00ff87, #00d4ff); color: #0a0e27; margin-top: 1rem; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0, 255, 135, 0.3); }
        .btn-secondary { background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); margin-top: 1rem; }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.1); }
        .switch-auth { text-align: center; color: #b8bcc8; margin-top: 1.5rem; }
        .switch-auth a { color: #00ff87; text-decoration: none; font-weight: 600; }
        @media (max-width: 600px) {
            .registration-card { padding: 2rem 1.5rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-card">
            <div class="logo">Spacexinfo</div>
            <h2>Create Your Account</h2>
            <p>Join millions of traders worldwide</p>

            <?php echo $signup_message; ?>

            <div class="step-indicator">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
            </div>

            <div class="error-message" id="errorMessage"></div>

            <div class="form-section active" id="step1">
                <form id="basicInfoForm">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="username" placeholder="Choose a unique username" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="firstName" placeholder="John" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="lastName" placeholder="Doe" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="phone" placeholder="+1 (555) 000-0000" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="email" placeholder="your.email@example.com" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Continue</button>
                </form>
            </div>

            <div class="form-section" id="step2">
                <form id="passwordForm">
                    <div class="form-group">
                        <label>Create Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" placeholder="Create a strong password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length"><span class="requirement-icon"></span><span>At least 8 characters</span></div>
                            <div class="requirement" id="req-uppercase"><span class="requirement-icon"></span><span>One uppercase letter</span></div>
                            <div class="requirement" id="req-lowercase"><span class="requirement-icon"></span><span>One lowercase letter</span></div>
                            <div class="requirement" id="req-number"><span class="requirement-icon"></span><span>One number</span></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirmPassword" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁️</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <button type="button" class="btn btn-secondary" onclick="goToStep1()">Back</button>
                </form>
            </div>

            <div class="switch-auth">
                Already have an account? <a href="#">Sign In</a>
            </div>
        </div>
    </div>

    <script>
        let formData = {};

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Step 1 Handler
        document.getElementById('basicInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.style.display = 'none';

            // Get values
            const username = document.getElementById('username').value.trim();
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!username || !firstName || !lastName || !phone || !email) {
                errorDiv.textContent = 'Please fill in all fields';
                errorDiv.style.display = 'block';
                return;
            }

            // Simple Email Regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorDiv.textContent = 'Please enter a valid email address';
                errorDiv.style.display = 'block';
                return;
            }

            formData = { username, firstName, lastName, phone, email };
            goToStep2();
        });

        // Step 2 Handler
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.style.display = 'none';

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                errorDiv.textContent = 'Passwords do not match';
                errorDiv.style.display = 'block';
                return;
            }

            if (!isPasswordStrong(password)) {
                errorDiv.textContent = 'Please meet all password requirements';
                errorDiv.style.display = 'block';
                return;
            }

            formData.password = password;
            
            // Trigger actual submission
            submitRegistration();
        });

        // Password Strength Logic (Kept same)
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');

            if (password.length > 0) {
                strengthDiv.classList.add('show');
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);

                updateRequirement('req-length', hasLength);
                updateRequirement('req-uppercase', hasUppercase);
                updateRequirement('req-lowercase', hasLowercase);
                updateRequirement('req-number', hasNumber);

                const metCount = [hasLength, hasUppercase, hasLowercase, hasNumber].filter(Boolean).length;
                strengthBar.className = 'password-strength-bar';
                if (metCount <= 2) strengthBar.classList.add('strength-weak');
                else if (metCount === 3) strengthBar.classList.add('strength-medium');
                else strengthBar.classList.add('strength-strong');
            } else {
                strengthDiv.classList.remove('show');
            }
        });

        function updateRequirement(id, met) {
            const element = document.getElementById(id);
            if (met) {
                element.classList.add('met');
                element.querySelector('.requirement-icon').textContent = '✓';
            } else {
                element.classList.remove('met');
                element.querySelector('.requirement-icon').textContent = '';
            }
        }

        function isPasswordStrong(password) {
            return password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password);
        }

        function goToStep2() {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            document.querySelectorAll('.step-dot')[0].classList.remove('active');
            document.querySelectorAll('.step-dot')[1].classList.add('active');
        }

        function goToStep1() {
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step1').classList.add('active');
            document.querySelectorAll('.step-dot')[1].classList.remove('active');
            document.querySelectorAll('.step-dot')[0].classList.add('active');
            document.getElementById('errorMessage').style.display = 'none';
        }

        // --- KEY FIX: THIS FUNCTION NOW SUBMITS TO PHP ---
        function submitRegistration() {
            // Create a hidden form to submit data to the PHP self
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Submits to the current page

            // Map JavaScript camelCase variables to PHP lowercase $_POST keys
            const map = {
                username: formData.username,
                firstname: formData.firstName,
                lastname: formData.lastName,
                phone: formData.phone,
                email: formData.email,
                password: formData.password
            };

            for (const key in map) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = map[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit(); // This reloads the page and triggers the PHP at the top
        }
    </script>
    <?php wp_footer(); ?> 
</body>

</html>
