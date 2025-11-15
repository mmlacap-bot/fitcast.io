document.addEventListener("DOMContentLoaded", function() {
    const registrationForm = document.getElementById('registrationForm');
    const loginForm = document.getElementById('loginForm');

    if (registrationForm) {
        registrationForm.addEventListener('submit', function(event) {
            const firstName = document.getElementById('first-name').value;
            const lastName = document.getElementById('last-name').value;
            const userName = document.getElementById('user-name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            alert(`Registration successful!`);
            registrationForm.reset();
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            alert(`Login successful for email: ${email}`);
            window.location.href = "profile.php";
            loginForm.reset();
        });
    }
});