<?php 
    // Logic: Set page-specific variables
    // These variables are used by the combined header we created earlier
    $_title = "ELEX Store - Home";
    $_subtitle = "Innovate Your Every Day.";
    $currentYear = date("Y");

    // Include the combined header file
    // Note: Make sure the filename matches what you saved (e.g., header.php or _head.php)
    include('_head.php'); 
?>

<main class="container">
    <section class="page-header text-center">
        <h1>Welcome to ELEX Store</h1>
        <p class="lead"><strong>"Innovate Your Every Day."</strong></p>
    </section>

    <div class="store-description">
        <section class="mb-5">
            <p>At ELEX Store, we believe technology should be seamless. From the latest flagship smartphones to immersive home theater systems and smart appliances, we curate only the best in modern tech. Experience the future today with expert guidance and cutting-edge devices designed to elevate your lifestyle. <strong>"Tech Made Simple."</strong></p>
        </section>

        <section class="mb-5">
            <p>Tired of the tech jargon? At ELEX Store, we focus on finding the right fit for you. Whether you’re looking for a reliable laptop for school or need help setting up your first smart home, our friendly experts are here to make technology easy, affordable, and accessible to everyone. <strong>"Your Partner in Productivity."</strong></p>
        </section>

        <section class="mb-5">
            <p>ELEX Store provides the infrastructure that keeps businesses moving. We specialize in reliable enterprise hardware, professional-grade networking solutions, and workstations built for efficiency. With a focus on durability and support, we help you bridge the gap between complex technology and streamlined results. <strong>"Built for Performance. Driven by Power."</strong></p>
        </section>

        <section class="mb-5">
            <p>Whether you’re a hardcore gamer, a creative professional, or a DIY hobbyist, ELEX Store is your ultimate hardware hub. We stock high-performance CPUs, GPUs, and networking gear that push the limits of what’s possible. Don’t just buy a machine—build a powerhouse with the help of our specialist team.</p>
        </section>
    </div>

    <section class="mt-5">
        <h3>Why Shop With Us?</h3>
        <?php
            $features = ["Fast Performance", "Secure Hardware", "Expert Guidance", "Enterprise Support"];
            echo "<ul class='mt-3'>";
            foreach ($features as $feature) {
                echo "<li><i class='fas fa-check-circle text-success'></i> $feature</li>";
            }
            echo "</ul>";
        ?>
    </section>
</main>

<?php 
    // Include the combined footer file
    include('_foot.php'); 
?>