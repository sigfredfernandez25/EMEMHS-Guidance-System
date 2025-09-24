<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EMEMHS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script>emailjs.init("GRi35_90k4gj9Es_f");</script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; min-height: 100vh; }
        .form-container { background: white; max-width: 100%; width: 100%; }
        .btn-primary { background: #800000; transition: all 0.3s; }
        .btn-primary:hover { background: #a52a2a; transform: translateY(-2px); }
        .message { display: none; padding: 1rem; margin: 1rem 0; border-radius: 0.5rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-[#800000] mb-2">Forgot Password?</h1>
                <p class="text-gray-600">Enter your email to reset your password</p>
            </div>

            <div id="successMessage" class="message success">
                ✅ Reset link sent! Check your email.
            </div>

            <div id="errorMessage" class="message error">
                ❌ Something went wrong. Please try again.
            </div>

            <form id="forgotPasswordForm" class="space-y-4">
                <div>
                    <input type="email" id="email" name="email" required
                        placeholder="Enter your email"
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000]/50">
                </div>

                <button type="submit"
                    class="btn-primary w-full p-3 text-white font-semibold rounded-lg">
                    Send Reset Link
                </button>

                <div class="text-center text-sm mt-4">
                    <a href="login.php" class="text-[#800000] hover:underline">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const successMsg = document.getElementById('successMessage');
        const errorMsg = document.getElementById('errorMessage');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Show loading state
            const btn = form.querySelector('button[type="submit"]');
            const btnText = btn.innerText;
            btn.disabled = true;
            btn.innerHTML = 'Sending...';
            
            // Hide messages
            successMsg.style.display = 'none';
            errorMsg.style.display = 'none';
            
            try {
                // Generate token and prepare reset link
                const email = document.getElementById('email').value;
                const token = generateToken();
                const resetLink = `${window.location.origin}/EMEMHS-Guidance-System/pages/reset-password.php?token=${encodeURIComponent(token)}`;
                
                // First, store the token in the database
                const storeResponse = await fetch('../logic/store_reset_token.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, token })
                });
                
                const storeResult = await storeResponse.json();
                console.log('Store token response:', storeResult);
                if (!storeResponse.ok) {
                    throw new Error(storeResult.message || 'Failed to process reset request');
                }
                
                // Prepare email template parameters
                const templateParams = {
                    to_email: email,
                    to_name: email.split('@')[0],
                    from_name: 'EMEMHS Guidance System',
                    reply_to: 'guidance@ememhs.edu.ph',
                    subject: 'EMEMHS Password Reset Request',
                    email: email,
                    link: resetLink,
                    current_year: new Date().getFullYear()
                };

                // Send email with the template
                console.log('Sending email with params:', templateParams);
                await emailjs.send(
                    'service_sm9r79h', 
                    'template_j8fff19',
                    templateParams,
                    'xhUMwQkD-kAk5o13X'
                );
                console.log('Email sent successfully');
                
                // Show success
                successMsg.style.display = 'block';
                form.reset();
            } catch (error) {
                console.error('Error:', error);
                errorMsg.style.display = 'block';
            } finally {
                // Reset button state
                btn.disabled = false;
                btn.innerHTML = btnText;
            }
        });

        function generateToken() {
            // Fallback for browsers that don't support crypto.getRandomValues
            if (typeof crypto === 'undefined' || !crypto.getRandomValues) {
                console.warn('crypto.getRandomValues not supported, using fallback');
                // Use a more secure fallback than Math.random
                const array = new Uint8Array(32);
                for (let i = 0; i < 32; i++) {
                    array[i] = Math.floor(Math.random() * 256);
                }
                return Array.from(array, byte => byte.toString(36)).join('');
            }

            const array = new Uint8Array(32);
            crypto.getRandomValues(array);
            return Array.from(array, byte => byte.toString(36)).join('');
        }
    </script>
</body>
</html>
