<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sir Nibiru - Token Page</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->

    <!-- Google Fonts: Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- Default favicon (16x16 or 32x32 pixels) -->
    <link rel="icon" href="img/favicon/favicon.ico" type="image/x-icon">

    <!-- PNG favicon for higher resolution devices -->
    <link rel="icon" href="img/favicon/favicon-32x32.png" sizes="32x32" type="image/png">

    <!-- Apple Touch Icon (for iOS devices) -->
    <link rel="apple-touch-icon" href="img/favicon/apple-touch-icon.png" sizes="180x180">

    <!-- Android and Windows Icons (optional) -->
    <link rel="icon" href="img/favicon/android-chrome-192x192.png" sizes="192x192" type="image/png">
    <link rel="icon" href="img/favicon/android-chrome-512x512.png" sizes="512x512" type="image/png">
    <link rel="icon" href="img/favicon/favicon-48x48.png" sizes="48x48" type="image/png">

</head>

<body>
    <header>
        <div class="header-banner"></div>
        <section class="best-friend-section">
            <div class="best-friend-badge">
                <p class="best-friend-name"><?php echo $bestFriend; ?></p>
                <p class="best-friend-donation"><?php echo $topDonation; ?></p>
            </div>
        </section>
        
    </header>

    <!-- Navigation Container -->
    <nav class="nav-container">
            <!-- Hamburger Menu Icon -->
            <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-controls="nav-links"
                aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <!-- Navigation Links -->
            <ul class="nav-links" id="nav-links">
                <li><a href="#" id="copyButton" onclick="copyAddress()">Token Address</a></li>
                <li><a href="https://jup.ag/swap/SOL-<?php echo $tokenMint; ?>" target="_blank">Swap on Jupiter</a></li>
                <li><a href="#">Buy Us a Coffee</a></li>
                <li><a href="https://t.me/sirnibiru" target="_blank">Join us on Telegram</a></li>
                <li><a href="https://www.x.com/sirnibiru" target="_blank">Follow us on X</a></li>
                <li><button id="connect-wallet">Connect Wallet</button></li>
            </ul>
        </nav>
        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

</body>

</html>