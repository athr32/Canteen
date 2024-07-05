document.addEventListener('DOMContentLoaded', function() {
    const username = localStorage.getItem('username');
    if (username) {
        document.getElementById('user_info').textContent = username;
    } else {
        // Redirect to login if not logged in
        window.location.href = 'login.html';
    }
});
