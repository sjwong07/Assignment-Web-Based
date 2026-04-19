<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | ElexStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
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