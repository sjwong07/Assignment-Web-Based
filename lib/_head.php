<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="shortcut icon" href="/images/favicon.png">
    <link rel="stylesheet" href="/CSS/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/app.js"></script>
</head>
<body>
    <header>
        <h1><a href="/">Reusable Layout</a></h1>

    </header>
<nav>
    <a href="/lib/index.php">Index</a>
    <a href="/order/checkout.php">CheckOut</a>
    <a href="/order/cart.php">Cart</a>
    <a href="/order/Product.php">Product</a>
    <a href="/order/history.php">History</a>
    
</nav>
<main>
    <h1><?= $_title ?? 'Untitle' ?></h1>

    