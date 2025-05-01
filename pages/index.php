<?php
// Any PHP logic can go here
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System's Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .form-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .input-focus {
            transition: all 0.3s ease;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(128, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="form-container rounded-2xl overflow-hidden flex">
                <!-- Left Column - Login Form -->
                <div class="w-1/2 p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-[#800000] mb-2">Welcome Back</h1>
                        <p class="text-gray-600 text-sm">Please login to your account</p>
                    </div>

                    <form action="../logic/login_logic.php" method="POST" class="space-y-6">
                        <div class="space-y-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                                <input type="text" id="username" name="username" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20">
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus focus:border-[#800000] focus:ring-2 focus:ring-[#800000]/20">
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" id="remember" name="remember"
                                        class="h-4 w-4 text-[#800000] focus:ring-[#800000] border-gray-300 rounded">
                                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                                </div>
                                <a href="#" class="text-sm text-[#800000] hover:text-[#a52a2a]">Forgot password?</a>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn-primary w-full px-4 py-3 text-white font-semibold rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/20 focus:ring-offset-2">
                            Sign In
                        </button>

                        <div class="text-center mt-4">
                            <p class="text-sm text-gray-600">
                                Don't have an account?
                                <a href="register.php" class="text-[#800000] hover:text-[#a52a2a] font-medium">Register here</a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Right Column - Image -->
                <div class="w-1/2 bg-[#800000]">
                    <img src="../assets/images/login-image.jpg" alt="Login" class="w-full h-full object-cover opacity-80">
                </div>
            </div>
        </div>
    </div>

    <script src="../js/index.js"></script>
</body>

</html>