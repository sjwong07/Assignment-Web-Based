<?php
// Contact Information
$contact = [
    "address" => "67, Jalan Malinja 2, Setapak, Wilayah Persekutuan Kuala Lumpur",
    "phone"   => "+60 12-411-4008",
    "email"   => "support@elexstore.com",
    "hours"   => "Mon - Fri: 9:00 AM - 6:00 PM"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #475569;
            --accent-color: #3b82f6;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
        }

        .contact-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .contact-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .contact-header p {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .contact-info {
            list-style: none;
            padding: 0;
            text-align: left;
            display: inline-block;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #f1f5f9;
            width: 100%;
            min-width: 300px;
        }

        .contact-item i {
            font-size: 20px;
            color: var(--accent-color);
            width: 30px;
            text-align: center;
        }

        .contact-item div {
            font-size: 16px;
            font-weight: 500;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="contact-container">
        <div class="contact-header">
            <h1>Get in Touch</h1>
            <p>Have questions? We'd love to hear from you.</p>
        </div>

        <div class="contact-info">
            <div class="contact-item">
                <i class="fa-solid fa-location-dot"></i>
                <div><?php echo $contact['address']; ?></div>
            </div>

            <div class="contact-item">
                <i class="fa-solid fa-phone"></i>
                <div><?php echo $contact['phone']; ?></div>
            </div>

            <div class="contact-item">
                <i class="fa-solid fa-envelope"></i>
                <div><?php echo $contact['email']; ?></div>
            </div>

            <div class="contact-item">
                <i class="fa-solid fa-clock"></i>
                <div><?php echo $contact['hours']; ?></div>
            </div>
        </div>

        <br>
        <a href="#" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Store</a>
    </div>

</body>
</html>