document.getElementById('login_form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get the username from the input
    const username = document.getElementById('login_username').value;
    
    // Simple login validation (just for demonstration, replace with actual logic)
    if (username) {
        // Store the username in local storage to persist across pages
        localStorage.setItem('username', username);
        
        // Redirect to the main content page
        window.location.href = 'index.html';
    }
});
