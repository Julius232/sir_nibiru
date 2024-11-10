<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sir Nibiru - Token Page</title>

    <link rel="stylesheet" href="styles/header.css">
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
    <header id="header">
        <div class="header-banner"></div>
        <section class="best-friend-section">
            <div class="best-friend-badge">
                <p class="best-friend-name"><?php echo $bestFriend; ?></p>
                <p class="best-friend-donation"><?php echo $topDonation; ?></p>
            </div>
        </section>

        <!-- Navigation Container Moved Inside Header -->
        <nav class="nav-container">
            <!-- Navigation Links -->
            <ul class="nav-links" id="nav-links">
                <li><a href="#" id="copyButton" onclick="copyAddress()" class="icon token-address"></a></li>
                <li><a href="https://jup.ag/swap/SOL-<?php echo $tokenMint; ?>" target="_blank"
                        class="icon jupiter"></a></li>
                <li><a href="https://t.me/sirnibiru" target="_blank" class="icon telegram"></a></li>
                <li><a href="https://www.x.com/sirnibiru" target="_blank" class="icon x"></a></li>
                <li><button id="connect-wallet" class="icon phantom"></button></li>
            </ul>
        </nav>

        <!-- Hamburger Menu Icon -->
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-controls="nav-links" aria-expanded="false"></button>


        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    </header>
</body>

</html>