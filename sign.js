document.getElementById('signup_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get the username from the input
    const username = document.getElementById('signup_username').value;
    
    // Simple sign-up logic (just for demonstration, replace with actual logic)
    if (username) {
        // Store the username in local storage to persist across pages
        localStorage.setItem('username', username);
        
        // Redirect to the main content page
        window.location.href = 'index.html';
    }
});
