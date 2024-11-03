<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sir Nibiru - Token Page</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->

    <!-- Google Fonts: Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="header-content">
            <img src="img/sir_nibiru.png" alt="Sir Nibiru Logo" class="logo">
            <h2>This Dog is Awesome</h2>
            <h1>SIR NIBIRU</h1>
            <p>This is a token you can show him your love in so many ways!</p>
        </div>
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
    </header>

</body>

</html>