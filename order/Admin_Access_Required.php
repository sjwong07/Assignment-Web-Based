<?php
require '../lib/_base.php';

// Check if user is admin
function auth($required_role = null) {
    if ($required_role === null) {
        return isset($_SESSION['user_id']);
    }
    
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    return $_SESSION['role'] === $required_role;
}

// If not admin, show access denied page
if (!auth('admin')) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin Access • Restricted</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
      <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: 'Inter', sans-serif;
          background: linear-gradient(135deg, #f5f7fa 0%, #e9eef4 100%);
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 1.5rem;
        }

        /* Glass-morphism card - matching login page style */
        .access-card {
          max-width: 460px;
          width: 100%;
          background: rgba(255, 255, 255, 0.75);
          backdrop-filter: blur(20px);
          -webkit-backdrop-filter: blur(20px);
          border-radius: 32px;
          box-shadow: 
            0 20px 40px -12px rgba(0, 10, 30, 0.12),
            0 0 0 1px rgba(255, 255, 255, 0.6) inset;
          padding: 2.5rem 2rem 2.25rem;
          transition: all 0.3s ease;
          animation: fadeSlide 0.4s ease;
        }

        @keyframes fadeSlide {
          from { opacity: 0; transform: translateY(8px); }
          to { opacity: 1; transform: translateY(0); }
        }

        /* Header - matching Welcome Back style */
        h1 {
          font-size: 2.2rem;
          font-weight: 700;
          letter-spacing: -0.02em;
          color: #0a1a2b;
          margin-bottom: 0.25rem;
        }

        .subtitle {
          font-size: 0.95rem;
          color: #4a5c6e;
          margin-bottom: 2rem;
          font-weight: 400;
        }

        /* Lock icon - clean and simple */
        .lock-icon {
          display: flex;
          justify-content: center;
          margin-bottom: 20px;
        }

        .lock-icon i {
          font-size: 48px;
          color: #c5221f;
          background: rgba(234, 67, 53, 0.08);
          width: 80px;
          height: 80px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 24px;
          border: 1px solid rgba(234, 67, 53, 0.15);
        }

        /* Role section - matching login form field style */
        .role-section {
          margin-bottom: 28px;
        }

        .role-label {
          display: block;
          font-size: 0.9rem;
          font-weight: 500;
          color: #2a3c4e;
          margin-bottom: 8px;
        }

        .role-display {
          display: flex;
          align-items: center;
          justify-content: space-between;
          background: rgba(255, 255, 255, 0.7);
          padding: 14px 18px;
          border-radius: 18px;
          border: 1.5px solid rgba(200, 212, 226, 0.5);
          backdrop-filter: blur(5px);
        }

        .role-text {
          font-size: 1rem;
          font-weight: 500;
          color: #0a1a2b;
        }

        .guest-badge {
          background: #eef2f7;
          padding: 6px 16px;
          border-radius: 40px;
          font-weight: 600;
          font-size: 0.9rem;
          color: #4a5c6e;
          border: 1px solid #d0dae6;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .guest-badge i {
          color: #7a8b9b;
          font-size: 0.85rem;
        }

        /* Warning box - EXACT same style as login page */
        .warning-box {
          display: flex;
          align-items: center;
          gap: 10px;
          background: rgba(245, 158, 11, 0.08);
          padding: 12px 16px;
          border-radius: 16px;
          margin-bottom: 20px;
          border: 1px solid rgba(245, 158, 11, 0.15);
        }

        .warning-box i {
          color: #d97706;
          font-size: 1.1rem;
        }

        .warning-box span {
          color: #92400e;
          font-size: 0.9rem;
          font-weight: 500;
        }

        /* Go Back button - matching Sign In button style */
        .go-back-btn {
          width: 100%;
          padding: 16px;
          background: #0a1a2b;
          color: white;
          border: none;
          border-radius: 40px;
          font-size: 1.05rem;
          font-weight: 600;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 10px;
          margin: 8px 0 18px;
          transition: all 0.2s;
          box-shadow: 0 8px 18px rgba(10, 26, 43, 0.15);
          border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .go-back-btn i {
          font-size: 1rem;
        }

        .go-back-btn:hover {
          background: #12314a;
          transform: translateY(-1px);
          box-shadow: 0 12px 22px rgba(10, 26, 43, 0.2);
        }

        /* Help link - matching Register here style */
        .help-link {
          text-align: center;
          font-size: 0.9rem;
          color: #4a5c6e;
        }

        .help-link a {
          color: #1a5cff;
          text-decoration: none;
          font-weight: 600;
          margin-left: 6px;
          border-bottom: 1px solid transparent;
          transition: border 0.2s;
        }

        .help-link a:hover {
          border-bottom: 1px solid #1a5cff;
        }

        /* Divider */
        .help-link span {
          margin: 0 8px;
          color: #cbd5e1;
        }

        /* Responsive */
        @media (max-width: 480px) {
          .access-card {
            padding: 2rem 1.5rem;
          }
          h1 {
            font-size: 1.9rem;
          }
        }
      </style>
    </head>
    <body>
      <div class="access-card">
        
        <!-- Lock Icon -->
        <div class="lock-icon">
          <i class="fas fa-lock"></i>
        </div>
        
        <!-- Header -->
        <h1>Admin Access Required</h1>
        <div class="subtitle">This page is restricted to administrators only</div>
        
        <!-- Current role -->
        <div class="role-section">
          <label class="role-label">Your current role</label>
          <div class="role-display">
            <span class="role-text"><?= htmlspecialchars($_SESSION['role'] ?? 'Guest') ?></span>
            <span class="guest-badge">
              <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['role'] ?? 'Guest') ?>
            </span>
          </div>
        </div>
        
        <!-- Warning box -->
        <div class="warning-box">
          <i class="fas fa-exclamation-triangle"></i>
          <span>⚠️ Maximum 3 attempts - 5 minute lockout</span>
        </div>
        
        <!-- Go Back button -->
        <button class="go-back-btn" onclick="window.history.back();">
          <i class="fas fa-arrow-left"></i> Go Back
        </button>
        
        <!-- Help link -->
        <div class="help-link">
          Don't have access? 
          <a href="#" onclick="alert('Please contact system administrator'); return false;">Contact admin</a>
          <span>•</span>
          <a href="../login.php">Back to login</a>
        </div>
        
      </div>

      <script>
        // Fallback for back button if no history
        const backBtn = document.querySelector('.go-back-btn');
        backBtn.addEventListener('click', function(e) {
          if (document.referrer === '' && window.history.length <= 1) {
            e.preventDefault();
            window.location.href = '../login.php';
          }
        });
      </script>
    </body>
    </html>
    <?php
    exit(); // Stop execution
}

// If we reach here, user is admin - continue with product management
?>