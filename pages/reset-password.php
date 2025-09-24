<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EMEMHS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; min-height: 100vh; }
        .form-container { background: white; max-width: 100%; width: 100%; }
        .btn-primary { background: #800000; transition: all 0.3s; }
        .btn-primary:hover { background: #a52a2a; transform: translateY(-2px); }
        .message { display: none; padding: 1rem; margin: 1rem 0; border-radius: 0.5rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .strength-0 { width: 20%; background-color: #dc3545; } /* Weak */
        .strength-1 { width: 40%; background-color: #fd7e14; } /* Fair */
        .strength-2 { width: 60%; background-color: #ffc107; } /* Good */
        .strength-3 { width: 80%; background-color: #28a745; } /* Strong */
        .strength-4 { width: 100%; background-color: #20c997; } /* Very Strong */
    </style>
</head>
<body class="flex items-center p-4">
    <div class="w-full max-w-md mx-auto">
        <div class="text-center mb-8">
            <a href="../index.php" class="text-gray-600 hover:text-[#800000] text-sm">
                ← Back to Home
            </a>
        </div>

        <div class="bg-white p-8 rounded-xl shadow">
            <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-xl shadow-lg">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Reset Your Password</h2>
                </div>
                <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative hidden" role="alert"></div>
                <div id="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative hidden" role="alert"></div>
                <form class="mt-8 space-y-6" id="resetPasswordForm" method="POST">
                    <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8"
                            placeholder="Enter your new password"
                            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/50"
                            oninput="checkPasswordStrength(this.value)">
                        <div class="h-1 bg-gray-200 rounded mt-2">
                            <div id="passwordStrength" class="h-full rounded"></div>
                        </div>
                        <p id="passwordStrengthText" class="text-xs text-gray-500 mt-1"></p>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8"
                            placeholder="Confirm your new password"
                            class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/50">
                    </div>

                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <span id="submitText">Reset Password</span>
                        <span id="submitSpinner" class="hidden ml-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </form>
                <div class="text-center text-sm mt-4">
                    <a href="login.php" class="text-[#800000] hover:underline">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            if (!strengthBar) return;
            
            let strength = 0;
            let tips = [];
            
            // Check password length
            if (password.length >= 8) strength++;
            else tips.push('At least 8 characters');
            
            // Check for lowercase letters
            if (/[a-z]/.test(password)) strength++;
            else tips.push('Lowercase letters');
            
            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength++;
            else tips.push('Uppercase letters');
            
            // Check for numbers
            if (/[0-9]/.test(password)) strength++;
            else tips.push('Numbers');
            
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else tips.push('Special characters');
            
            // Update strength bar
            strengthBar.className = 'h-1 mt-1 rounded ' + (
                strength < 2 ? 'bg-red-500' : 
                strength < 4 ? 'bg-yellow-500' : 'bg-green-500'
            );
            
            // Update strength text
            const strengthText = document.getElementById('passwordStrengthText');
            if (strengthText) {
                if (password.length === 0) {
                    strengthText.textContent = '';
                } else if (tips.length > 0) {
                    strengthText.textContent = 'Add: ' + tips.join(', ');
                    strengthText.className = 'text-xs text-red-500 mt-1';
                } else {
                    strengthText.textContent = 'Strong password!';
                    strengthText.className = 'text-xs text-green-500 mt-1';
                }
            }
        }

        const form = document.getElementById('resetPasswordForm');
        const successMsg = document.getElementById('successMessage');
        const errorMsg = document.getElementById('errorMessage');
        form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const token = new URLSearchParams(window.location.search).get('token');
    
    console.log('Token received:', token);
    console.log('Token length:', token ? token.length : 'null');
    
    // Validate passwords match
    if (password !== confirmPassword) {
        errorMsg.textContent = "Passwords do not match!";
        errorMsg.style.display = 'block';
    }
    
    // Validate password strength
    if (password.length < 8) {
        errorMsg.textContent = "Password must be at least 8 characters long";
        errorMsg.style.display = 'block';
        return;
    }
    
    // Show loading state
    const btn = form.querySelector('button[type="submit"]');
    const btnText = btn.innerText;
    document.getElementById('submitText').textContent = 'Resetting...';
    document.getElementById('submitSpinner').classList.remove('hidden');
    btn.disabled = true;
    
    // Hide messages
    successMsg.style.display = 'none';
    errorMsg.style.display = 'none';
    
    try {
        const response = await fetch('../logic/reset_password_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ token, password })
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'Failed to reset password');
        }
        
        // Show success message
        successMsg.textContent = 'Your password has been reset successfully!';
        successMsg.style.display = 'block';
        form.reset();
        
        // Redirect to login after 3 seconds
        setTimeout(() => {
            window.location.href = 'login.php?reset=success';
        }, 3000);
        
    } catch (error) {
        console.error('Error:', error);
        errorMsg.textContent = error.message || 'An error occurred. Please try again.';
        errorMsg.style.display = 'block';
    } finally {
        btn.disabled = false;
        document.getElementById('submitText').textContent = 'Reset Password';
        document.getElementById('submitSpinner').classList.add('hidden');
    }
});
    </script>
</body>
</html>
