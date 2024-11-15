<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="Sir.Nibiru">
    <title>Sir.Nibiru</title>

    <link rel="stylesheet" href="styles/header.css">
    <!-- Google Fonts: Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="57x57" href="/sir_nibiru/img/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/sir_nibiru/img/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/sir_nibiru/img/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/sir_nibiru/img/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/sir_nibiru/img/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/sir_nibiru/img/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/sir_nibiru/img/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/sir_nibiru/img/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/sir_nibiru/img/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/sir_nibiru/img/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/sir_nibiru/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/sir_nibiru/img/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/sir_nibiru/img/favicon/favicon-16x16.png">
    <link rel="icon" href="/sir_nibiru/img/favicon/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/sir_nibiru/manifest.json">

    <meta name="msapplication-TileImage" content="/sir_nibiru/img/favicon/ms-icon-144x144.png">


</head>

<body>
    <header id="header">
        <div class="header-banner"></div>

        <section class="menu">
            <a href="#playWithMe" class="menu-link">Play |</a>
            <a href="#highscore" class="menu-link">Highscore |</a>
            <a href="#roadmap" class="menu-link">Roadmap |</a>
            <a href="#story" class="menu-link">The Story |</a>
            <a href="whitepaper/Sir_Nibiru_Project_Whitepaper_v1.pdf" class="menu-link" download>Whitepaper</a>
            <img src="img/icon_burn.webp" alt="Burn Icon" class="burn-icon">
            <span class="menu-text">Tokens Burned: <span id="total-burn-amount">0</span></span>
        </section>
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
                <li><a href="#" id="copyButton" onclick="copyAddress()" class="icon token-address"
                        data-address="InitialAddress">CopyAddress</a></li>
                <li><a target="_blank" class="icon jupiter"></a></li>
                <li><a href="https://t.me/sirnibiru" target="_blank" class="icon telegram"></a></li>
                <li><a href="https://www.x.com/sirnibiru" target="_blank" class="icon x"></a></li>
                <li>
                    <button id="connect-wallet" class="icon phantom">
                        <span id="connect-text"></span>
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Hamburger Menu Icon -->
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-controls="nav-links"
            aria-expanded="false"></button>
        <button class="pfeil" id="scrollTop" aria-label="Scroll to top"></button>

        <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    </header>
</body>

</html>

<script>
    function copyAddress() {
        // Get the updated address from the `data-address` attribute
        const address = document.getElementById("copyButton").getAttribute("data-address");

        // Create a temporary input element to hold the address for copying
        const tempInput = document.createElement("input");
        tempInput.value = address;
        document.body.appendChild(tempInput);

        // Select and copy the text, then remove the temporary element
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);

        // Optional: Give feedback to the user that the address was copied
        alert("Address copied: " + address);
    }
</script>