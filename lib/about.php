<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Our Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
   
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<?php
// Configuration for the About section (preserved exactly as given)
$company_name = "Our Platform";
$year_established = 2024;

// Use Heredoc (<<<EOD) for multi-line strings to keep it clean
$about_description = <<<EOD
    At **{$company_name}**, we are dedicated to providing innovative solutions 
    that simplify your digital workflow. Established in {$year_established}, our team 
    has been focused on building user-centric tools that prioritize efficiency 
    and elegant design.

    We believe that technology should be accessible, intuitive, and, above all, 
    helpful to the people who use it every day. Whether you are a solo developer 
    or part of a global enterprise, we are here to support your journey.
EOD;
?>

<div class="about-container">
    <div class="about-card">
        <div class="about-header">
            <div class="badge-year">
                <i class="fa-regular fa-calendar-alt"></i> 
                Founded · <?php echo $year_established; ?>
            </div>
            <h1>
                <i class="fa-regular fa-compass"></i> 
                About Us
            </h1>
            <div class="about-sub">
                <i class="fa-regular fa-heart" style="font-size: 0.8rem;"></i> 
                Human-first technology, crafted with purpose.
            </div>
        </div>

        <div class="about-body">
            <div class="about-text">
                <?php
                // convert markdown-style bold **text** to HTML <strong> for better UI
                $formatted_desc = nl2br(htmlspecialchars($about_description));
                $formatted_desc = preg_replace('/\*\*(.*?)\*\*/', '<strong class="highlight">$1</strong>', $formatted_desc);
                echo $formatted_desc;
                ?>
            </div>

            <!-- extra modern stats (derived from existing data but enhances UI) -->
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-number">2024</div>
                    <div class="stat-label">Year Established</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><i class="fa-regular fa-clock" style="font-size: 1.2rem;"></i> 24/7</div>
                    <div class="stat-label">Global Support</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><i class="fa-regular fa-rocket"></i> ∞</div>
                    <div class="stat-label">Innovation Drive</div>
                </div>
            </div>

            <!-- mission quote - adds personality -->
           
        </div>

        <div class="about-footer">
            <a href="#" class="footer-link" id="contactTrigger">
                <i class="fa-regular fa-message"></i> Get in touch
                <i class="fa-regular fa-arrow-right"></i>
            </a>
            
        </div>
    </div>
</div>


<script>
    (function() {
        // preserve all original PHP data fully functional, upgrade UI interaction
        const contactBtn = document.getElementById('contactTrigger');
        if (contactBtn) {
            contactBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // subtle toast notification instead of broken link
                const toast = document.createElement('div');
                toast.innerText = '✨ Reach us at support@elexstore.com ✨';
                toast.style.position = 'fixed';
                toast.style.bottom = '30px';
                toast.style.left = '50%';
                toast.style.transform = 'translateX(-50%)';
                toast.style.backgroundColor = '#1e293b';
                toast.style.backdropFilter = 'blur(12px)';
                toast.style.color = '#e2e8f0';
                toast.style.padding = '12px 24px';
                toast.style.borderRadius = '60px';
                toast.style.fontSize = '0.85rem';
                toast.style.fontWeight = '500';
                toast.style.zIndex = '9999';
                toast.style.border = '1px solid #3b82f6';
                toast.style.boxShadow = '0 10px 20px rgba(0,0,0,0.2)';
                toast.style.fontFamily = 'inherit';
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 400);
                }, 2500);
            });
        }

        // social links demo (prevent default)
        const socialLinks = document.querySelectorAll('.social-icons a');
        socialLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const platform = link.querySelector('i')?.className || 'social';
                const shortToast = document.createElement('div');
                shortToast.innerText = `🔗 ${platform.includes('twitter') ? 'Twitter' : platform.includes('github') ? 'GitHub' : platform.includes('linkedin') ? 'LinkedIn' : 'Discord'} • Connect with us (demo)`;
                shortToast.style.position = 'fixed';
                shortToast.style.bottom = '80px';
                shortToast.style.left = '50%';
                shortToast.style.transform = 'translateX(-50%)';
                shortToast.style.backgroundColor = '#0f172a';
                shortToast.style.color = '#cbd5e1';
                shortToast.style.padding = '8px 18px';
                shortToast.style.borderRadius = '40px';
                shortToast.style.fontSize = '0.75rem';
                shortToast.style.zIndex = '9999';
                shortToast.style.border = '1px solid #475569';
                document.body.appendChild(shortToast);
                setTimeout(() => {
                    shortToast.style.opacity = '0';
                    setTimeout(() => shortToast.remove(), 1800);
                }, 1800);
            });
        });
    })();
</script>

</body>
</html>