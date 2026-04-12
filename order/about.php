<?php
// Configuration for the About section
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .about-container {
            max-width: 600px;
            margin: 40px auto;
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
        }
        .about-header {
            display: flex;
            align-items: center;
            font-size: 28px;
            font-weight: bold;
            color: #475569;
            margin-bottom: 20px;
        }
        .about-text {
            font-size: 18px;
            white-space: pre-line; /* Preserves the line breaks from the PHP variable */
        }
    </style>
</head>
<body>

    <div class="about-container">
        <div class="about-header">
            <span style="margin-right: 15px;">ⓘ</span> About Us
        </div>

        <div class="about-text">
            <?php echo $about_description; ?>
        </div>
    </div>

</body>
</html>