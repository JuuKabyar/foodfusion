<div class="custom-modal-overlay" id="loginModal">
    <div class="custom-modal-box">
        <button class="modal-close-btn" type="button" onclick="closeLoginModal()">&times;</button>

        <div class="modal-header-center">
            <img src="/FoodFusion-LYH/assets/images/logo4.png" class="modal-logo" alt="FoodFusion Logo">
            <h4>Login</h4>
        </div>

        <form id="loginForm" novalidate>

            <!-- EMAIL -->
            <input
                name="email"
                class="custom-input"
                placeholder="Email"
            >
            <div class="error-text" id="login-error-email"></div>

            <!-- PASSWORD -->
            <div class="password-input-wrap">
                <input
                    type="password"
                    name="password"
                    id="login_password"
                    class="custom-input password-input"
                    placeholder="Password"
                >

                <button
                    type="button"
                    class="password-toggle-btn"
                    onclick="togglePassword('login_password', this)"
                >
                    <img
                        src="/FoodFusion-LYH/assets/images/show.png"
                        alt="Show Password"
                        class="password-toggle-icon"
                    >
                </button>
            </div>

            <div class="error-text" id="login-error-password"></div>

            <div class="error-text" id="login-error-general"></div>
            <div class="success-text" id="login-success-message"></div>

            <button type="submit" class="custom-submit-btn">
                Login
            </button>

            <div class="login-divider">OR</div>

            <button type="button" class="google-login-btn" onclick="loginWithGoogle()">
                <img src="/FoodFusion-LYH/assets/images/google.png" alt="Google">
                Continue with Google
            </button>

            <button type="button" class="tiktok-login-btn" onclick="loginWithTikTok()">
                <img src="/FoodFusion-LYH/assets/images/tik-tok.png" alt="TikTok">
                Continue with TikTok
            </button>

        </form>

        <p class="modal-switch-text">
            Don’t have an account?
            <a href="#" onclick="switchToRegister(event)">Register</a>
        </p>
    </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
function loginWithGoogle() {
    google.accounts.id.initialize({
        client_id: '755904441289-eiltn0d6u1b0sov183chb8ccm072q4c4.apps.googleusercontent.com',
        callback: handleGoogleLogin
    });

    google.accounts.id.prompt();
}

function handleGoogleLogin(response) {
    fetch('/FoodFusion-LYH/auth/google-login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'credential=' + encodeURIComponent(response.credential)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        } else {
            const general = document.getElementById('login-error-general');
            if (general) {
                general.innerText = data.message;
            }
        }
    })
    .catch(() => {
        const general = document.getElementById('login-error-general');
        if (general) {
            general.innerText = 'Google login failed. Please try again.';
        }
    });
}

function loginWithTikTok() {
    window.location.href = '/FoodFusion-LYH/auth/tiktok-login.php';
}

</script>