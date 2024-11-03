<?php
// Include the header and data
include 'header.php';
?>

<!-- Include only the bundled JavaScript file -->
<script src="dist/bundle.js"></script> <!-- Adjust path as needed -->

<main>
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
    <section class="best-friend-section">
        <div class="best-friend-badge">
            <h4>Nibiru's Best Friend</h4>
            <p><?php echo $bestFriend; ?></p>
            <p><?php echo $topDonation; ?></p>
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



    <section class="play-with-me">
        <h3>Play with Me</h3>
        <div class="play-area">
            <!-- Game Frame on the Left -->
            <div class="game-container">
                <img src="tamagotchi/nibiru_happy.png" alt="Sir Nibiru" class="tamagotchi-image">

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
        </div>
    </section>

    <!-- Highscore Section -->
    <section class="highscore">
        <h3>SIR NIBIRU LOVES</h3>
        <!-- Tabs for Switching -->
        <div class="highscore-tabs">
            <button id="monthlyTab" class="active">Monthly Top 10</button>
            <button id="allTimeTab">All-Time Top 10</button>
        </div>
        <!-- Highscore Lists -->
        <div id="monthlyHighscore" class="highscore-list"></div>
        <div id="allTimeHighscore" class="highscore-list" style="display: none;"></div>

        <!-- Last Sitter Section -->
        <div class="last-sitter">
            <h4>LAST SITTER:</h4>
            <p>TakeMyMoney24<br>pumpitup<br>Mr. Bitcoin<br>ButterTherea<br>DieterFohlen</p>
        </div>
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