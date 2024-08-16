document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-container');
    const loginButton = document.getElementById('login-button');
    const registerButton = document.getElementById('register-button');

    const loginForm = `
        <form id="login-form" action="login.php" method="POST">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    `;

    const registerForm = `
        <form id="register-form" action="register.php" method="POST">
            <h2>Register</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    `;

    loginButton.addEventListener('click', function() {
        formContainer.innerHTML = loginForm;
    });

    registerButton.addEventListener('click', function() {
        formContainer.innerHTML = registerForm;
    });

    // Load login form by default
    formContainer.innerHTML = loginForm;
});
