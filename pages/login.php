<?php
// Any PHP logic can go here
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EMEMHS Guidance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fafafa;
            min-height: 100vh;
        }

        .form-container {
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border-radius: 24px;
            overflow: hidden;
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .input-field:focus {
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
            outline: none;
        }

        .btn-primary {
            background: #800000;
            color: #fff;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: #a52a2a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(128, 0, 0, 0.2);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .back-link {
            transition: all 0.2s ease;
        }

        .back-link:hover {
            transform: translateX(-4px);
        }

        .message {
            display: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.875rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .logo-section {
            background: linear-gradient(135deg, #f8eaea 0%, #fafafa 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .logo-section {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-6xl">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="../index.php" class="back-link inline-flex items-center text-gray-600 hover:text-[#800000] transition-colors duration-200 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Login Card -->
        <div class="form-container flex flex-col md:flex-row">
            <!-- Left Column - Login Form -->
            <div class="w-full md:w-1/2 p-6 sm:p-8 lg:p-12">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold text-[#800000] mb-3">Welcome Back</h1>
                    <p class="text-gray-600 text-base">Sign in to access your guidance account</p>
                </div>

                <!-- Message Display Area -->
                <?php if (isset($_GET['error'])): ?>
                    <div id="message" class="message error" style="display: block !important;">
                        <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                    </div>
                <?php elseif (isset($_GET['success'])): ?>
                    <div id="message" class="message success" style="display: block !important;">
                        <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
                    </div>
                <?php elseif (isset($_GET['warning'])): ?>
                    <div id="message" class="message warning" style="display: block !important;">
                        <?php echo htmlspecialchars(urldecode($_GET['warning'])); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="../logic/login_logic.php" method="POST" class="space-y-5">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input type="text" id="username" name="username" required
                            placeholder="Enter your email"
                            class="input-field w-full px-4 py-3 rounded-xl text-gray-900 placeholder-gray-400">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                            placeholder="Enter your password"
                            class="input-field w-full px-4 py-3 rounded-xl text-gray-900 placeholder-gray-400">
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="forgot-password.php" class="text-sm text-[#800000] hover:text-[#a52a2a] font-medium transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit"
                        class="btn-primary w-full px-4 py-3.5 text-white font-semibold rounded-xl text-base">
                        Sign In
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="register.php" class="text-[#800000] hover:text-[#a52a2a] font-semibold transition-colors duration-200">
                            Create Account
                        </a>
                    </p>
                </div>
            </div>

            <!-- Right Column - Logo Section -->
            <div class="w-full md:w-1/2 logo-section">
                <div class="text-center">
                    <img src="../image/ememhs-logo.png" alt="EMEMHS Logo" class="w-48 sm:w-64 md:w-80 h-auto mx-auto mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-[#800000] mb-2">EMEMHS Guidance System</h2>
                    <p class="text-gray-600 text-sm sm:text-base max-w-sm mx-auto">
                        Empowering students through guidance and support
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>