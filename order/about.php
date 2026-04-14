<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Our Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-deep: #0f172a;
            --primary-soft: #1e293b;
            --accent-primary: #3b82f6;
            --accent-secondary: #8b5cf6;
            --accent-glow: rgba(59, 130, 246, 0.2);
            --text-light: #f1f5f9;
            --text-muted: #94a3b8;
            --card-bg: rgba(255, 255, 255, 0.96);
            --bg-gradient-start: #0f172a;
            --bg-gradient-end: #1e1b4b;
            --border-radius-card: 2rem;
            --transition-smooth: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0c0a2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* animated background particles effect */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(59,130,246,0.08) 0%, transparent 3%),
                radial-gradient(circle at 80% 70%, rgba(139,92,246,0.06) 0%, transparent 4%),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.01) 0px, rgba(255,255,255,0.01) 2px, transparent 2px, transparent 8px);
            pointer-events: none;
            z-index: 0;
        }

        /* floating orbs decoration */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            z-index: 0;
            pointer-events: none;
        }
        .orb-1 {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: -100px;
            left: -100px;
        }
        .orb-2 {
            width: 400px;
            height: 400px;
            background: #8b5cf6;
            bottom: -150px;
            right: -100px;
        }

        .about-container {
            max-width: 780px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            animation: fadeSlideUp 0.5s ease-out forwards;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* main card */
        .about-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border-radius: var(--border-radius-card);
            border: 1px solid rgba(59, 130, 246, 0.25);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(59, 130, 246, 0.1);
            overflow: hidden;
            transition: var(--transition-smooth);
        }

        .about-card:hover {
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 30px 50px -15px rgba(0, 0, 0, 0.5);
        }

        /* header section with gradient accent */
        .about-header {
            background: linear-gradient(135deg, rgba(59,130,246,0.15) 0%, rgba(139,92,246,0.1) 100%);
            padding: 2rem 2.5rem;
            border-bottom: 1px solid rgba(59,130,246,0.2);
            position: relative;
        }

        .badge-year {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59,130,246,0.2);
            backdrop-filter: blur(8px);
            padding: 0.35rem 1rem;
            border-radius: 60px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: #60a5fa;
            border: 1px solid rgba(59,130,246,0.4);
            margin-bottom: 1.2rem;
        }

        .badge-year i {
            font-size: 0.7rem;
        }

        .about-header h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #c4b5fd 80%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .about-header h1 i {
            background: none;
            background-clip: unset;
            -webkit-background-clip: unset;
            color: #60a5fa;
            font-size: 2rem;
        }

        .about-sub {
            color: var(--text-muted);
            margin-top: 0.75rem;
            font-size: 0.95rem;
            border-left: 3px solid var(--accent-primary);
            padding-left: 1rem;
        }

        /* content body */
        .about-body {
            padding: 2.5rem;
        }

        .about-text {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #e2e8f0;
            white-space: pre-line;
        }

        /* custom highlight for company name and year */
        .highlight {
            background: linear-gradient(120deg, rgba(59,130,246,0.2) 0%, rgba(139,92,246,0.2) 100%);
            color: #a5f3ff;
            font-weight: 600;
            padding: 0 4px;
            border-radius: 6px;
            display: inline-block;
        }

        .stat-grid {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
            justify-content: space-between;
            border-top: 1px solid rgba(71, 85, 105, 0.4);
            padding-top: 2rem;
        }

        .stat-item {
            flex: 1;
            min-width: 120px;
            background: rgba(255,255,255,0.03);
            border-radius: 1.2rem;
            padding: 1rem;
            text-align: center;
            transition: var(--transition-smooth);
            border: 1px solid rgba(59,130,246,0.15);
        }

        .stat-item:hover {
            background: rgba(59,130,246,0.08);
            border-color: rgba(59,130,246,0.4);
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f1f5f9, #94a3f8);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-top: 6px;
        }

        .mission-quote {
            margin-top: 2rem;
            background: linear-gradient(90deg, rgba(59,130,246,0.08) 0%, rgba(139,92,246,0.05) 100%);
            padding: 1.25rem 1.5rem;
            border-radius: 1.2rem;
            border-left: 4px solid var(--accent-primary);
            font-style: italic;
            color: #cbd5e1;
            font-size: 0.9rem;
        }

        .mission-quote i {
            color: var(--accent-primary);
            margin-right: 8px;
        }

        /* footer / cta area */
        .about-footer {
            padding: 1.5rem 2.5rem 2rem;
            border-top: 1px solid rgba(71, 85, 105, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition-smooth);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            background: rgba(255,255,255,0.02);
        }

        .footer-link i {
            font-size: 0.8rem;
            transition: transform 0.2s;
        }

        .footer-link:hover {
            color: #60a5fa;
            background: rgba(59,130,246,0.12);
        }

        .footer-link:hover i {
            transform: translateX(3px);
        }

        .social-icons {
            display: flex;
            gap: 0.8rem;
        }
        .social-icons a {
            color: #64748b;
            background: rgba(255,255,255,0.04);
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            text-decoration: none;
        }
        .social-icons a:hover {
            background: var(--accent-primary);
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .about-header {
                padding: 1.5rem;
            }
            .about-header h1 {
                font-size: 2rem;
            }
            .about-body {
                padding: 1.8rem;
            }
            .stat-grid {
                flex-direction: column;
                gap: 0.8rem;
            }
            .about-footer {
                flex-direction: column-reverse;
                align-items: flex-start;
                padding: 1.5rem;
            }
        }

        /* custom scroll */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 10px;
        }
    </style>
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