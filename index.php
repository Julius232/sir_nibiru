<?php
// Include the header and data
include 'header.php';
?>

<!-- Include only the bundled JavaScript file -->
<script src="dist/bundle.js"></script> <!-- Adjust path as needed -->
<script src="https://code.createjs.com/1.0.0/createjs.min.js"></script>
<script src="animations/sir-nibiru7.js"></script>
<script>
    var canvas, stage, exportRoot, anim_container, dom_overlay_container, fnStartAnimation;

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
        exportRoot = new lib.sirnibiru7();
        stage = new lib.Stage(canvas);

        // Animation Function
        fnStartAnimation = function () {
            stage.addChild(exportRoot);
            createjs.Ticker.framerate = 20;

            // Function to handle frame updates based on progress bar widths
            const updateAnimationFrame = () => {
                const yellowBarWidth = parseFloat(document.querySelector('.progress-bar.yellow').style.width) || 0;
                const blueBarWidth = parseFloat(document.querySelector('.progress-bar.blue').style.width) || 0;

                if (yellowBarWidth <= 33 && blueBarWidth <= 33) {
                    if (exportRoot.currentFrame < 231 || exportRoot.currentFrame >= 260) {
                        exportRoot.gotoAndStop(231);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                } else if (yellowBarWidth > 33 && blueBarWidth <= 33) {
                    if (exportRoot.currentFrame < 51 || exportRoot.currentFrame >= 98) {
                        exportRoot.gotoAndStop(51);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                } else if (yellowBarWidth <= 33 && blueBarWidth > 33 && blueBarWidth <= 66) {
                    if (exportRoot.currentFrame < 200 || exportRoot.currentFrame >= 229) {
                        exportRoot.gotoAndStop(200);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                } else if (yellowBarWidth > 33 && blueBarWidth > 33 && blueBarWidth <= 66) {
                    if (exportRoot.currentFrame < 1 || exportRoot.currentFrame >= 49) {
                        exportRoot.gotoAndStop(1);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                } else if (yellowBarWidth <= 33 && blueBarWidth > 66) {
                    if (exportRoot.currentFrame < 169 || exportRoot.currentFrame >= 198) {
                        exportRoot.gotoAndStop(169);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                } else if (yellowBarWidth > 33 && blueBarWidth > 66) {
                    if (exportRoot.currentFrame < 100 || exportRoot.currentFrame >= 151) {
                        exportRoot.gotoAndStop(100);
                    } else {
                        exportRoot.gotoAndPlay(exportRoot.currentFrame + 1);
                    }
                }
            };

            // Function to update the bowl image based on green bar width
            const updateBowlImage = () => {
                const greenBarWidth = parseFloat(document.querySelector('.progress-bar.green').style.width) || 0;
                const bowlImage = document.getElementById('bowlImage');
                let newSrc;

                if (greenBarWidth <= 25) {
                    newSrc = "animations/images/food/empty_bowl.png";
                } else if (greenBarWidth <= 50) {
                    newSrc = "animations/images/food/slightly_filled_bowl.png";
                } else if (greenBarWidth <= 75) {
                    newSrc = "animations/images/food/filled_bowl.png";
                } else {
                    newSrc = "animations/images/food/over_filled_bowl.png";
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
        if (window.innerWidth <= 768) {
            // Exit the function early for mobile devices
            return;
        }
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
    }

    // Adjust height on page load and resize
    window.addEventListener('load', adjustHeightToBackground);
    window.addEventListener('resize', adjustHeightToBackground);

    // Refresh page on resize with debounce to avoid excessive reloads
    let resizeTimeout;
    let lastWindowWidth = window.innerWidth;

    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (Math.abs(window.innerWidth - lastWindowWidth) > 10) { // Check for a significant width change
                location.reload();
            }
        }, 200); // Adjust debounce delay as needed
    });

    window.onload = init;
</script>

<main>
    <link rel="stylesheet" href="styles/styles.css"> <!-- Link to your CSS file -->
    <link rel="stylesheet" href="styles/play.css">
    <link rel="stylesheet" href="styles/highscore.css">
    <link rel="stylesheet" href="styles/roadmap.css">
    <section class="how-to">
        <!-- Modal Overlay -->
        <div id="walletOptionsOverlay" class="overlay" style="display: none;">
            <div class="overlay-content">
                <h3>Wallet Options</h3>
                <label for="newUsernameInput">Change Username:</label>
                <input type="text" id="newUsernameInput" placeholder="New Username">
                <button onclick="changeUsername()">Submit</button>
                <button onclick="disconnectWallet()">Disconnect</button>
                <button onclick="closeWalletOptions()">Cancel</button>
            </div>
        </div>
        <div id="howToModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>

                <!-- Disclaimer First -->
                <h3>Disclaimer</h3>
                <p>
                    - We are private individuals who created this platform as a fun, interactive tool to visualize
                    tokens burned for Sir Nibiru on the Solana blockchain. This is a community project without any
                    profit intentions; every token sent to the burn address is permanently removed from circulation.<br>
                    - We are not affiliated with Solana, nor are we responsible for any token loss or transaction
                    errors.<br>
                    - All burns are irreversible; once sent, tokens cannot be returned or recovered. Please ensure all
                    details are correct before burning tokens.
                </p>

                <!-- How to Play Instructions -->
                <h3>How to Play</h3>
                <p>1. <strong>Select an Action in the Menu:</strong><br>
                    "Clean Me" impacts the blue bar, "Feed Me" impacts the green bar, and "Play With Me" impacts the
                    yellow bar.</p>
                <p>2. <strong>Enter Your Details:</strong> Input your username and wallet address in the respective
                    fields.</p>
                <p>3. <strong>Open Your Wallet:</strong> Use a compatible Solana wallet, such as Phantom.</p>
                <p>4. <strong>Send Tokens:</strong> In your wallet, select the "Send" option, enter the burn address
                    <span onclick="copyToClipboard(this)" style="cursor: pointer; color: red; font-weight: bold;">
                        1nc1nerator11111111111111111111111111111111
                    </span>
                    confirm the amount to burn, and complete the transaction. Remember, this action is
                    permanent.
                </p>
                <p>5. <strong>Submit Proof:</strong> Enter the transaction’s signature as proof.</p>
                <p>6. <strong>Press Enter and Have Fun!</strong> Based on the amount burned, your selected bar will
                    increase by a percentage. These bars gradually decrease over time, so revisit to keep Sir Nibiru
                    happy!</p>
            </div>
        </div>
    </section>

    <section class="form-play">
        <!-- Modal Form -->
        <div id="actionFormModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeForm()">&times;</span>
                <h3 id="formTitle">Action</h3>
                <form id="actionForm" onsubmit="validateUser(event)">
                    <!-- Hidden input to store the action type -->
                    <input type="hidden" id="actionType" name="actionType">

                    <!-- Username and Wallet ID inputs -->
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>

                    <label for="walletId">Wallet ID:</label>
                    <input type="text" id="walletId" name="walletId" required>

                    <!-- Signature input, hidden initially -->
                    <div id="signatureField" style="display: none;">
                        <label for="signature">Signature:</label>
                        <input type="text" id="signature" name="signature">
                    </div>

                    <!-- Submit button -->
                    <button type="submit" id="nextButton">Next</button>
                </form>
            </div>
        </div>
    </section>

    <section class="play-with-me" id="playWithMe">
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
                </div>
            </div>

            <!-- Overlay Form -->
            <div id="actionOverlay" class="overlay" style="display: none;">
                <div class="overlay-content">
                    <h3 id="actionTitle">Perform Action</h3>
                    <label for="tokenAmountInput">Enter Token Amount:</label>
                    <input type="number" id="tokenAmountInput" placeholder="Token Amount" min="0" step="any">
                    <button onclick="submitAction()">Submit</button>
                    <button onclick="closeActionForm()">Cancel</button>
                </div>
            </div>
        </div>
    </section>

    <section class="highscore">
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

    <section class="road-map">
        <img class="roadMap">
    </section>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="overlay" style="display: none;">
        <div class="overlay-content">
            <p>Processing your transaction...</p>
        </div>
    </div>

</main>

<?php
// Include the footer
include 'footer.php';
?>