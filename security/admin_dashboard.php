<?php
require_once 'auth.php';
// Only allow users with 'admin' role to see this page
authorize('admin'); 
?>
<h1>Welcome to Admin Dashboard</h1>