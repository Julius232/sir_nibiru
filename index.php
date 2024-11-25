<?php
if (substr($_SERVER['REQUEST_URI'], -1) !== '/') {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $_SERVER['REQUEST_URI'] . '/');
    exit;
}
// Include the header and data
include 'header.php';
?>

<!-- Include only the bundled JavaScript file -->
<script src="dist/bundle.js"></script> <!-- Adjust path as needed -->
<script src="https://code.createjs.com/1.0.0/createjs.min.js"></script>
<script src="animations/sir-nibiru-9.js"></script>
<script>
    var canvas, stage, exportRoot, anim_container, dom_overlay_container, fnStartAnimation, isEggParty, totalBurnAmount;
    isEggParty = true;
    //EGG-PARTY
    const EGG_PARTY_TARGET = 25920000; // Threshold to stop egg party

    function init() {
        canvas = document.getElementById("canvas");
        anim_container = document.getElementById("animation_container");
        dom_overlay_container = document.getElementById("dom_overlay_container");

        const comp = AdobeAn.getComposition("B309F7E37067A34FB780BA4C6E089657");
        const lib = comp.getLibrary();
        const loader = new createjs.LoadQueue(false);
        loader.addEventListener("fileload", function (evt) { handleFileLoad(evt, comp) });
        loader.addEventListener("complete", function (evt) { handleComplete(evt, comp) });
        loader.loadManifest(lib.properties.manifest);
    }

    function handleFileLoad(evt, comp) {
        const images = comp.getImages();
        if (evt && (evt.item.type == "image")) { images[evt.item.id] = evt.result; }
    }

    function handleComplete(evt, comp) {
        const lib = comp.getLibrary();
        exportRoot = new lib.sirnibiru9();
        stage = new lib.Stage(canvas);

        // Animation Function
        fnStartAnimation = function () {
            stage.addChild(exportRoot);
            createjs.Ticker.framerate = 25;
            // Function to handle frame updates based on progress bar widths
            const updateAnimationFrame = () => {
                // Otherwise, update each bar individually as needed
                const yellowBarWidth = parseFloat(document.querySelector('.progress-bar.yellow').style.width) || 0;
                const blueBarWidth = parseFloat(document.querySelector('.progress-bar.blue').style.width) || 0;
                const greenBarWidth = parseFloat(document.querySelector('.progress-bar.green').style.width) || 0;

                var startFrame = 0;
                var endFrame = 0;

                const eggPartyPercentage = Math.min(totalBurnAmount / EGG_PARTY_TARGET, 1) * 100;

                if (isEggParty) {
                    // Calculate the egg party animation frame based on total burned amount
                    if (eggPartyPercentage < 9) {
                        startFrame = 300;
                        endFrame = 324;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 18) {
                        startFrame = 325;
                        endFrame = 349;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 27) {
                        startFrame = 350;
                        endFrame = 374;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 36) {
                        startFrame = 375;
                        endFrame = 399;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 45) {
                        startFrame = 400;
                        endFrame = 425;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 54) {
                        startFrame = 426;
                        endFrame = 451;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 63) {
                        startFrame = 452;
                        endFrame = 477;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 72) {
                        startFrame = 478;
                        endFrame = 503;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 81) {
                        startFrame = 504;
                        endFrame = 529;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage < 90) {
                        startFrame = 530;
                        endFrame = 555;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (eggPartyPercentage <= 100) {
                        startFrame = 556;
                        endFrame = 581;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame - 1) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    }

                } else {
                    // Logic after egg party ends (when totalBurnAmount >= EGG_PARTY_TARGET)
                    if (yellowBarWidth <= 33 && blueBarWidth <= 33) {
                        startFrame = 232;
                        endFrame = 260;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (yellowBarWidth > 33 && blueBarWidth <= 33) {
                        startFrame = 51;
                        endFrame = 98;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (yellowBarWidth <= 33 && blueBarWidth > 33 && blueBarWidth <= 66) {
                        startFrame = 200;
                        endFrame = 229;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (yellowBarWidth > 33 && blueBarWidth > 33 && blueBarWidth <= 66) {
                        startFrame = 1;
                        endFrame = 49;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (yellowBarWidth <= 33 && blueBarWidth > 66) {
                        startFrame = 169;
                        endFrame = 198;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    } else if (yellowBarWidth > 33 && blueBarWidth > 66) {
                        startFrame = 100;
                        endFrame = 151;
                        if (exportRoot.currentFrame < startFrame || exportRoot.currentFrame >= endFrame) {
                            exportRoot.gotoAndStop(startFrame);
                        } else {
                            exportRoot.gotoAndPlay(exportRoot.currentFrame);
                        }
                    }
                }
            };
            // Function to update the bowl image based on green bar width
            const updateBowlImage = () => {
                const greenBarWidth = parseFloat(document.querySelector('.progress-bar.green').style.width) || 0;
                const bowlImage = document.getElementById('bowlImage');
                let newSrc;

                if (isEggParty) {
                    bowlImage.style.display = "none";
                    newSrc = "";
                }
                else {
                    if (greenBarWidth <= 25) {
                        newSrc = "animations/images/food/empty_bowl.png";
                    } else if (greenBarWidth <= 50) {
                        newSrc = "animations/images/food/slightly_filled_bowl.png";
                    } else if (greenBarWidth <= 75) {
                        newSrc = "animations/images/food/filled_bowl.png";
                    } else {
                        newSrc = "animations/images/food/over_filled_bowl.png";
                    }
                }

                // Update src only if it's different from the current one
                if (bowlImage.getAttribute('src') !== newSrc) {
                    bowlImage.setAttribute('src', newSrc);
                }
            };

            // Register "tick" event to update animation and bowl image
            createjs.Ticker.addEventListener("tick", () => {
                updateAnimationFrame();
                updateBowlImage();
            });

            createjs.Ticker.addEventListener("tick", stage);
        };

        AdobeAn.compositionLoaded(lib.properties.id);
        fnStartAnimation();

        // ResizeObserver to make the canvas responsive to `.play-area`
        const parentContainer = document.querySelector('.play-area');
        const resizeObserver = new ResizeObserver(entries => {
            for (let entry of entries) {
                const { width, height } = entry.contentRect;
                scaleCanvas(width, height);
            }
        });
        resizeObserver.observe(parentContainer);
    }

    function scaleCanvas(parentWidth, parentHeight) {
        const lib = AdobeAn.getComposition("B309F7E37067A34FB780BA4C6E089657").getLibrary();

        // Calculate the scale to fit the parent container
        const scaleX = parentWidth / lib.properties.width;
        const scaleY = parentHeight / lib.properties.height;
        const scale = Math.min(scaleX, scaleY);

        // Set canvas dimensions and scale
        canvas.width = lib.properties.width * scale;
        canvas.height = lib.properties.height * scale;

        // Apply scaling to the stage
        stage.scaleX = stage.scaleY = scale;

        // Update stage to reflect the changes
        stage.update();
    }

    function adjustHeightToBackground() {
        const viewportWidth = window.innerWidth || document.documentElement.clientWidth;

        if (viewportWidth > 1024) {
            const header = document.getElementById('header');
            const playWithMe = document.getElementById('playWithMe');

            // Header Image
            const headerImage = new Image();
            headerImage.src = 'img/header.webp';
            headerImage.onload = function () {
                const aspectRatioHeader = headerImage.width / headerImage.height;
                header.style.height = `${header.offsetWidth / aspectRatioHeader}px`;
            };

            // PlayWithMe Image
            const playWithMeImage = new Image();
            playWithMeImage.src = 'img/bg_interact-with-me.webp';
            playWithMeImage.onload = function () {
                const aspectRatioPlayWithMe = playWithMeImage.width / playWithMeImage.height;
                playWithMe.style.height = `${playWithMe.offsetWidth / aspectRatioPlayWithMe}px`;
            };
        } else {
            // Optionally, reset the heights for smaller screens
            const header = document.getElementById('header');
            const playWithMe = document.getElementById('playWithMe');

            if (header) {
                header.style.height = 'auto';
            }
            if (playWithMe) {
                playWithMe.style.height = 'auto';
            }
        }
    }


    // Adjust position on page load and resize
    window.addEventListener('load', () => {
        adjustHeightToBackground(); // Keep existing adjustments for header and playWithMe
    });
    window.addEventListener('resize', () => {
        adjustHeightToBackground();
    });

    /*     // Refresh page on resize with debounce to avoid excessive reloads
        let resizeTimeout;
        let lastWindowWidth = window.innerWidth;
    
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (Math.abs(window.innerWidth - lastWindowWidth) > 100) { // Increase threshold
                    lastWindowWidth = window.innerWidth; // Update width only on significant change
                    location.reload();
                }
            }, 200);
        }); */

    window.onload = init;
</script>

<main>
    <link rel="stylesheet" href="styles/styles.css"> <!-- Link to your CSS file -->
    <link rel="stylesheet" href="styles/play.css">
    <link rel="stylesheet" href="styles/highscore.css">
    <link rel="stylesheet" href="styles/roadmap.css">
    <link rel="stylesheet" href="styles/story.css">
    <section class="how-to">
        <div id="walletOptionsOverlay" class="overlay" style="display: none;">
            <div class="overlay-content">
                <h3>Wallet Options</h3>

                <!-- Current Username Display -->
                <div id="currentUsernameDisplay" class="current-username">
                    <span><strong id="currentUsername">Loading...</strong></span>
                </div>

                <img src="img/icon_burn.webp" alt="Burn Icon" class="burn-icon">
                <span class="menu-text">Tokens Burned: <span id="user-burn-amount">0</span></span>
                <input type="text" id="newUsernameInput" placeholder="New Username">

                <!-- Button container to align buttons in a row -->
                <div class="button-container">
                    <button class="submit btn-submit" onclick="changeUsername()"></button>
                    <button class="cancel btn-cancel" onclick="closeWalletOptions()"></button>
                    <button class="disconnect btn-disconnect" onclick="disconnectWallet()"></button>
                </div>
                <!-- Container for transaction details -->
                <div id="transactionDetails" class="transaction-details">
                    <!-- Transaction details will be injected here -->
                </div>
            </div>
        </div>

        <div id="howToModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>

                <h3>Disclaimer</h3>
                <p>
                    Welcome to our community-driven platform, designed as a fun, interactive experience to visualize
                    tokens burned for Sir Nibiru on the Solana blockchain. This project is non-profit and intended
                    purely for entertainment. Any tokens sent to the burn address are permanently removed from
                    circulation.
                    <br><br>
                    <strong>We are not affiliated with Solana</strong> and hold no responsibility for any token loss or
                    transaction errors. <strong>All burns are irreversible</strong>; once tokens are sent to the burn
                    address, they cannot be returned or recovered. Please verify all transaction details carefully
                    before proceeding.
                </p>

                <h3>How It Works:</h3>
                <ol>
                    <li><strong>Connect Your Phantom Wallet</strong>: Start by connecting your Phantom Wallet. If you
                        choose not to set a username, the first 8 characters of your wallet's public address will be
                        displayed on the leaderboard.</li>
                    <li><strong>Choose an Action</strong>: Select an action you’d like to perform with Sir Nibiru (e.g.,
                        clean, feed, or play).</li>
                    <li><strong>Select Token Amount</strong>: Enter the number of tokens you wish to burn. This amount
                        will directly affect the interaction.</li>
                    <li><strong>Sign the Transaction</strong>: After confirming your action and token amount, sign the
                        transaction using your Phantom Wallet. This will initiate a burn instruction on Solana,
                        permanently burning the specified tokens.</li>
                </ol>

                <h3>Leaderboards:</h3>
                <ul>
                    <li><strong>Nibiru’s Best Friend</strong>: This title goes to the user who has completed the highest
                        single burn transaction of all time.</li>
                    <li><strong>Monthly Top 10</strong>: This leaderboard showcases the top 10 users by total burned
                        tokens for the current month. Your position here is determined by the sum of all tokens you've
                        burned within the month.</li>
                    <li><strong>All-Time Top 10</strong>: This leaderboard shows the top 10 users by total tokens burned
                        across all time. Your ranking here is based on the cumulative total of all your burn
                        transactions.</li>
                </ul>

                <p>
                    Enjoy interacting with Sir Nibiru and see if you can climb the leaderboards! Thank you for being
                    part of our community, and remember: every token burned here is a permanent donation to the Sir
                    Nibiru experience.
                </p>
            </div>
        </div>
    </section>

    <section class="play-with-me" id="playWithMe">
        <div id="musicControls">
            <audio id="eggPartyMusic" loop muted>
                <source src="audio/egg_party.mp3" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            <audio id="postEggPartyMusic" loop muted>
                <source src="audio/post_egg_party.mp3" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            <button id="muteButton" onclick="toggleMute()">Play Audio</button>
        </div>

        <div class="play-area">
            <h1>INTERACT WITH ME</h1>
            <!-- Game Frame on the Left -->
            <div class="game-container">
                <div id="bowl_container" class="bowl_container">
                    <img id="bowlImage" class="bowl_image" src="" alt="Food Bowl">
                </div>

                <div id="animation_container" class="animation_container">
                    <canvas id="canvas" class="canvas"></canvas>
                    <div id="dom_overlay_container" class="dom_overlay_container"></div>
                </div>

                <div class="progress-bars">
                    <div class="progress-bar-container">
                        <div class="progress-bar blue" style="width: 80%;"></div> <!-- Mock 80% -->
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar green" style="width: 60%;"></div> <!-- Mock 60% -->
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar yellow" style="width: 40%;"></div> <!-- Mock 40% -->
                    </div>
                </div>

            </div>

            <!-- Button Grid and Best Friend Badge on the Right -->
            <div class="right-panel">
                <div class="button-grid">
                    <h4>PLAY MENU</h4>
                    <button class="btn-howto" onclick="showHowTo()"></button>
                    <button class="btn-clean" onclick="openActionForm('clean')"></button>
                    <button class="btn-play" onclick="openActionForm('play')"></button>
                    <button class="btn-feed" onclick="openActionForm('feed')"></button>
                    <button class="btn-hatch" onclick="openActionForm('hatch')"></button>
                </div>
            </div>

            <!-- Overlay Form -->
            <div id="actionOverlay" class="overlay" style="display: none;">
                <div class="overlay-content">
                    <h3 id="actionTitle">Perform Action</h3>

                    <p id="actionDescription" style="display: none;"></p>

                    <div class="burn-amount-container">
                        <img src="img/icon_burn.webp" alt="Burn Icon" class="burn-amount-img">
                        <input type="number" id="tokenAmountInput" placeholder="Token Amount" min="1000" step="any"
                            oninput="updateTokenAmountColor()">
                    </div>
                    <div class="button-container">
                        <button class="submit btn-submit" onclick="submitAction()"></button>
                        <button class="cancel btn-cancel" onclick="closeActionForm()"></button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section id="highscore" class="highscore">
        <h3>SIR NIBIRU LOVES</h3>

        <!-- Tabs for Switching Between Monthly and All-Time High Scores -->
        <div class="highscore-tabs">
            <button id="monthlyTab" class="active">Monthly Top 10</button>
            <button id="allTimeTab">All-Time Top 10</button>
        </div>

        <!-- Monthly Highscore List -->
        <div id="monthlyHighscore" class="highscore-list">

        </div>

        <!-- All-Time Highscore List (Initially Hidden) -->
        <div id="allTimeHighscore" class="highscore-list" style="display: none;">
            <!-- Add more all-time highscore items as needed -->
        </div>

        <!-- Last Sitter Section -->
        <div class="last-sitter">
            <div class="last-sitter-item last-sitter-title">
                <h4>LAST DOG SITTER:</h4>
            </div>
            <div class="last-sitter-item last-sitter-names">
                <p>TakeMyMoney24<br>pumpitup<br>Mr. Bitcoin<br>ButterTherea<br>DieterFohlen</p>
            </div>
        </div>
    </section>

    <section id="roadmap" class="road-map"></section>
    <section id="story" class="story"></section>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="overlay" style="display: none;">
        <div class="overlay-content">
            <p>Processing your transaction...</p>
        </div>
    </div>

    <div id="overlay" style="display: none;">
        <div id="popup">
            <h3>Please Open in Phantom Browser</h3>
            <p>It seems like you're not using the Phantom browser. Please open this website in the Phantom wallet app's
                browser or download Phantom.</p>
            <button id="closePopupButton">Okay</button>
            <button id="redirectToDownloadButton">Download Phantom</button>
        </div>
    </div>


</main>

<?php
// Include the footer
include 'footer.php';
?>