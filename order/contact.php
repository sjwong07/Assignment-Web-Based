<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | ElexStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #1e293b;
            --accent-color: #3b82f6;
            --accent-glow: rgba(59,130,246,0.25);
            --text-main: #0f172a;
            --text-muted: #334155;
            --bg-light: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.96);
            --shadow-sm: 0 20px 35px -12px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
            --border-radius: 2rem;
            --transition: all 0.25s ease;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
            background: linear-gradient(145deg, #eef2ff 0%, #f8fafc 100%);
            color: var(--text-main);
            margin: 0;
            padding: 2rem 1.5rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* animated background subtle pattern */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 25% 40%, rgba(59,130,246,0.03) 2%, transparent 2.5%),
                              radial-gradient(circle at 75% 85%, rgba(100,116,139,0.02) 1.8%, transparent 2%);
            background-size: 48px 48px, 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        .contact-container {
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(0px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2.5rem 2rem;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,0.5);
            position: relative;
            z-index: 2;
        }

        /* glassmorphism enhancement */
        @supports (backdrop-filter: blur(8px)) {
            .contact-container {
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255,255,255,0.7);
            }
        }

        .contact-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .contact-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0f2b3d 0%, #1e4b6e 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .contact-header p {
            font-size: 1rem;
            color: var(--text-muted);
            max-width: 380px;
            margin: 0.5rem auto 0;
            line-height: 1.5;
        }

        /* badge / indicator */
        .support-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59,130,246,0.12);
            backdrop-filter: blur(4px);
            padding: 0.4rem 1.2rem;
            border-radius: 60px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--accent-color);
            margin-bottom: 1rem;
            border: 1px solid rgba(59,130,246,0.2);
        }

        .support-badge i {
            font-size: 0.85rem;
        }

        /* contact grid layout */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 2rem 0 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.1rem 1.5rem;
            background: #ffffff;
            border-radius: 1.5rem;
            transition: var(--transition);
            border: 1px solid #e9eef3;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .contact-item:hover {
            transform: translateY(-3px);
            border-color: rgba(59,130,246,0.35);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(59,130,246,0.1);
            background: #fefefe;
        }

        .contact-item i {
            font-size: 1.6rem;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            border-radius: 1.2rem;
            color: var(--accent-color);
            transition: var(--transition);
        }

        .contact-item:hover i {
            background: var(--accent-color);
            color: white;
            transform: scale(0.98);
        }

        .contact-item div {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-main);
            word-break: break-word;
            line-height: 1.4;
        }

        .contact-item div small {
            display: block;
            font-weight: 400;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* email special style */
        .contact-item .email-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px dashed transparent;
            transition: border 0.2s;
        }

        .contact-item .email-link:hover {
            border-bottom-color: var(--accent-color);
        }

        /* back link modernized */
        .back-wrapper {
            text-align: center;
            margin-top: 2.2rem;
            padding-top: 0.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: transparent;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.7rem 1.4rem;
            border-radius: 2rem;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
            background: rgba(255,255,255,0.6);
        }

        .back-link i {
            font-size: 0.85rem;
            transition: transform 0.2s;
        }

        .back-link:hover {
            background: white;
            color: var(--accent-color);
            border-color: var(--accent-color);
            box-shadow: 0 4px 10px rgba(59,130,246,0.15);
        }

        .back-link:hover i {
            transform: translateX(-4px);
        }

        /* map placeholder hint (extra micro interaction) */
        .map-hint {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            border-top: 1px solid #eef2ff;
            padding-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .map-hint span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* responsiveness */
        @media (max-width: 550px) {
            body {
                padding: 1rem;
            }
            .contact-container {
                padding: 1.8rem;
            }
            .contact-item {
                padding: 0.9rem 1rem;
                gap: 0.9rem;
            }
            .contact-item i {
                width: 40px;
                height: 40px;
                font-size: 1.3rem;
            }
            .contact-header h1 {
                font-size: 1.9rem;
            }
        }

        /* subtle animation on load */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .contact-container {
            animation: fadeSlideUp 0.45s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
        }

        .contact-item {
            animation: fadeSlideUp 0.35s ease backwards;
            animation-delay: calc(0.05s * var(--order, 0));
        }

        .contact-item:nth-child(1) { --order: 1; }
        .contact-item:nth-child(2) { --order: 2; }
        .contact-item:nth-child(3) { --order: 3; }
        .contact-item:nth-child(4) { --order: 4; }
    </style>
</head>
<body>

<?php
// Contact Information (exactly as provided, preserved)
$contact = [
    "address" => "67, Jalan Malinja 2, Setapak, Wilayah Persekutuan Kuala Lumpur",
    "phone"   => "+60 12-411-4008",
    "email"   => "support@elexstore.com",
    "hours"   => "Mon - Fri: 9:00 AM - 6:00 PM"
];
?>

<div class="contact-container">
    <div class="contact-header">
        <div class="support-badge">
            <i class="fa-regular fa-comment-dots"></i> 
            <span>24/7 Online Support</span>
        </div>
        <h1>Let's talk</h1>
        <p>Reach out anytime — our team is ready to assist you with your inquiries.</p>
    </div>

    <div class="contact-info">
        <!-- address -->
        <div class="contact-item">
            <i class="fa-solid fa-location-dot"></i>
            <div>
                <?php echo $contact['address']; ?>
                <small>📍 Main office, walk-ins welcome</small>
            </div>
        </div>

        <!-- phone with click to call -->
        <div class="contact-item">
            <i class="fa-solid fa-phone"></i>
            <div>
                <a href="tel:<?php echo str_replace(' ', '', $contact['phone']); ?>" style="text-decoration: none; color: inherit;">
                    <?php echo $contact['phone']; ?>
                </a>
                <small>📞 Available during business hours</small>
            </div>
        </div>

        <!-- email with mailto -->
        <div class="contact-item">
            <i class="fa-solid fa-envelope"></i>
            <div>
                <a href="mailto:<?php echo $contact['email']; ?>" class="email-link"><?php echo $contact['email']; ?></a>
                <small>✉️ We reply within 24 hours</small>
            </div>
        </div>

        <!-- hours -->
        <div class="contact-item">
            <i class="fa-solid fa-clock"></i>
            <div>
                <?php echo $contact['hours']; ?>
                <small>⏱️ GMT+8, Kuala Lumpur time</small>
            </div>
        </div>
    </div>

    
    <div class="map-hint">
        <span><i class="fa-regular fa-building"></i> Setapak, KL</span>
        <span><i class="fa-regular fa-clock"></i> Fast response</span>
        <span><i class="fa-regular fa-message"></i> Live chat ready</span>
    </div>
</div>

<!-- micro-interaction: improve back link with fallback for demo -->
<script>
    (function() {
        const backBtn = document.getElementById('backToStoreBtn');
        if (backBtn) {
            backBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Simulate back navigation or replace with real store URL
                // Since original has href="#", we improve UX: show a brief message
                // but you can replace window.location if needed.
                // For demo, we'll provide a nice toast-like feedback
                const toastMsg = document.createElement('div');
                toastMsg.innerText = '✨ Returning to store... (demo) ✨';
                toastMsg.style.position = 'fixed';
                toastMsg.style.bottom = '20px';
                toastMsg.style.left = '50%';
                toastMsg.style.transform = 'translateX(-50%)';
                toastMsg.style.backgroundColor = '#1e293b';
                toastMsg.style.color = 'white';
                toastMsg.style.padding = '10px 20px';
                toastMsg.style.borderRadius = '40px';
                toastMsg.style.fontSize = '0.85rem';
                toastMsg.style.fontWeight = '500';
                toastMsg.style.zIndex = '999';
                toastMsg.style.backdropFilter = 'blur(8px)';
                toastMsg.style.boxShadow = '0 8px 18px rgba(0,0,0,0.1)';
                toastMsg.style.fontFamily = 'inherit';
                document.body.appendChild(toastMsg);
                setTimeout(() => {
                    toastMsg.style.opacity = '0';
                    setTimeout(() => toastMsg.remove(), 400);
                }, 1800);
                // If you have actual store URL, uncomment next line:
                // window.location.href = '/';
                console.log('Navigate back to store homepage (demo placeholder)');
            });
        }
    })();
</script>

</body>
</html>