// Add to your dashboard or any page
function logoutUser() {
    if (confirm('Are you sure you want to logout?')) {
        fetch('logout.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = '/login.php';
        });
    }
}

// Usage: <button onclick="logoutUser()">Logout</button>