import { Connection, PublicKey, Transaction, TransactionInstruction } from '@solana/web3.js';
import { PhantomWalletAdapter } from '@solana/wallet-adapter-phantom';
import { Buffer } from 'buffer';
import {
    getAssociatedTokenAddress,
    createBurnCheckedInstruction,
} from '@solana/spl-token';

if (!window.Buffer) window.Buffer = Buffer;


document.addEventListener("DOMContentLoaded", async () => {
    const connectButton = document.getElementById('connect-wallet');
    const overlay = document.getElementById('walletOptionsOverlay');
    const newUsernameInput = document.getElementById('newUsernameInput');
    let wallet = null;

    const phantomWallet = new PhantomWalletAdapter();
    let TOKEN_MINT_ADDRESS, TOKEN_DECIMALS;
    let MAX_PROGRESS = 100;
    let tokensForFullProgress = 10000; // Default values
    let decayRatePerHour = 10; // Default values
    let bars = { clean: 0, play: 0, feed: 0 }; // Initial bar values

    // Call fetchConfig on page load to initialize the values
    fetchConfig();

    // Restore session from local storage on page load
    function restoreSession() {
        const storedWalletAddress = localStorage.getItem('walletAddress');
        const storedUsername = localStorage.getItem('username');

        if (storedWalletAddress && storedUsername) {
            wallet = new PublicKey(storedWalletAddress);
            connectButton.textContent = storedUsername || "Options";
            connectButton.style.color = 'blue';
            console.log("Session restored with wallet:", storedWalletAddress);
        }
    }

    // Call restoreSession on page load
    restoreSession();

    // Set up wallet event listeners
    setupWalletEvents();

    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    // Show the overlay with wallet options
    function showWalletOptions() {
        overlay.style.display = 'flex';
    }

    // Hide the wallet options overlay
    window.closeWalletOptions = function () {
        overlay.style.display = 'none';
    };

    async function connectWallet() {
        if (wallet) {
            showWalletOptions();
            return; // Open wallet options if already connected
        }

        try {
            await phantomWallet.connect();
            if (!phantomWallet.connected) {
                throw 'Failed to connect to Phantom wallet.';
            }

            wallet = phantomWallet.publicKey;
            console.log("Wallet connected:", wallet.toString());
            connectButton.textContent = "Options";
            handleNonceVerification(wallet);
        } catch (error) {
            console.error("Failed to connect wallet:", error);
            alert("Failed to connect wallet: " + error);
        }
    }

    // Disconnect wallet and remove session data
    window.disconnectWallet = async function () {
        const walletAddress = wallet ? wallet.toString() : null;

        if (!walletAddress) {
            console.error("No wallet connected to disconnect.");
            return;
        }

        try {
            const response = await fetch('https://www.sir-nibiru.com/disconnect_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_address: walletAddress })
            });
            const result = await response.json();

            if (result.status === "success") {
                console.log("Nonce deleted on the backend for wallet:", walletAddress);

                // Clear localStorage and reset wallet variable
                localStorage.removeItem('walletAddress');
                localStorage.removeItem('username');
                localStorage.removeItem('nonce');
                wallet = null;

                // Update connect button
                connectButton.textContent = "Connect Wallet";
                connectButton.style.color = ''; // Reset to default color
                closeWalletOptions();
            } else {
                console.error("Failed to delete nonce:", result.error);
            }
        } catch (error) {
            console.error("Error during disconnecting user:", error);
        }

        // Disconnect from Phantom wallet
        if (phantomWallet.connected) {
            await phantomWallet.disconnect();
        }
    };

    const maxUsernameLength = 12;
    // Change username function
    window.changeUsername = async function () {
        const newUsername = newUsernameInput.value.trim();
        const walletAddress = wallet.toString();
        const nonce = localStorage.getItem('nonce');

        // Frontend Validation: Check if username is valid
        if (!newUsername) {
            alert("Username cannot be empty.");
            return;
        } else if (newUsername.length > maxUsernameLength) {
            alert(`Username cannot exceed ${maxUsernameLength} characters.`);
            return;
        } else if (!/^[a-zA-Z0-9]+$/.test(newUsername)) {
            alert("Username can only contain letters and numbers.");
            return;
        }

        try {
            const response = await fetch('https://www.sir-nibiru.com/update_username.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    new_username: newUsername,
                    nonce: nonce
                })
            });

            const data = await response.json();
            if (data.status === "success") {
                alert("Username updated successfully!");
                localStorage.setItem('username', newUsername);
                localStorage.setItem('nonce', data.new_nonce);
                connectButton.textContent = newUsername;
                closeWalletOptions();
            } else {
                alert("Failed to update username: " + data.message);
            }
        } catch (error) {
            alert("An error occurred while updating the username.");
        }
    };

    // Event listener for the connect button
    connectButton.addEventListener("click", () => {
        if (wallet) {
            showWalletOptions();
        } else {
            connectWallet();
        }
    });

    function setupWalletEvents() {
        phantomWallet.on('connect', () => {
            wallet = phantomWallet.publicKey;
            console.log("Wallet connected:", wallet.toString());
            connectButton.textContent = "Options";
            handleNonceVerification(wallet);
        });

        phantomWallet.on('disconnect', () => {
            wallet = null;
            console.log("Wallet disconnected");
            connectButton.textContent = "Connect Wallet";
            connectButton.style.color = ''; // Reset to default color
            localStorage.clear(); // Clear all session data on disconnect
        });
    }

    let isSigning = false;
    async function handleNonceVerification(wallet) {
        if (isSigning) return;

        isSigning = true; // Set the signing flag to prevent duplicate calls

        try {
            const walletAddress = wallet.toString();

            // Request a nonce from the backend
            const nonceResponse = await fetch('https://www.sir-nibiru.com/get_nonce.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_address: walletAddress })
            });
            const nonceData = await nonceResponse.json();

            if (!nonceData.nonce) {
                console.error("Nonce not provided by the backend.");
                return;
            }

            const nonce = nonceData.nonce;
            localStorage.setItem('nonce', nonce);

            // Sign the nonce with the wallet adapter
            const encodedNonce = new TextEncoder().encode(nonce);
            const signature = await phantomWallet.signMessage(encodedNonce);

            // Send signature to backend for verification
            const verificationResponse = await fetch('https://www.sir-nibiru.com/verify_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    signature: Buffer.from(signature).toString('base64')
                })
            });
            const data = await verificationResponse.json();

            if (data.status === "success") {
                connectButton.textContent = data.username;
                connectButton.style.color = 'blue';
                localStorage.setItem('walletAddress', walletAddress);
                localStorage.setItem('username', data.username);
            } else {
                console.error("Nonce verification failed:", data.error);
            }
        } catch (err) {
            console.error("Failed to verify nonce with backend:", err);
        } finally {
            isSigning = false; // Reset the flag after execution
        }
    }


    let selectedAction = null;
    // Function to open the overlay form and set the action
    window.openActionForm = function (action) {
        selectedAction = action;
        document.getElementById("actionTitle").textContent = `Perform ${action.charAt(0).toUpperCase() + action.slice(1)} Action`;
        document.getElementById("actionOverlay").style.display = "flex";
    };

    window.closeActionForm = function () {
        document.getElementById("actionOverlay").style.display = "none"; // Hides the overlay
    };

    window.submitAction = async function () {
        const tokenAmount = parseFloat(document.getElementById("tokenAmountInput").value.trim());
        if (isNaN(tokenAmount) || tokenAmount <= 0 || !selectedAction || !wallet) {
            alert("Please enter a valid token amount and ensure your wallet is connected.");
            return;
        }

        try {
            // Create and send the burn transaction
            const transactionSignature = await performBurnAction(tokenAmount, selectedAction);
            if (!transactionSignature) {
                alert("Transaction failed or was rejected.");
                return;
            }

            // Send the transaction signature to the backend
            console.log("Submitting action with data:", {
                wallet_address: wallet.toString(),
                action: selectedAction,
                signature: transactionSignature
            });

            const response = await fetch('https://www.sir-nibiru.com/submit_burn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: wallet.toString(),
                    action: selectedAction,
                    signature: transactionSignature
                })
            });

            if (!response.ok) {
                console.error(`Network response was not ok: ${response.status} ${response.statusText}`);
                alert("Network error: Unable to submit action. Please try again later.");
                return;
            }

            const result = await response.json();

            if (result.status === "success") {
                alert("Action performed successfully!");
                location.reload();
            } else {
                alert(`Action failed: ${result.message}`);
            }
        } catch (error) {
            console.error("Error submitting action:", error);
            alert("Failed to submit action. Please try again.");
        }
    };

    async function performBurnAction(amount, action) {
        // Show loading overlay
        document.getElementById('loadingOverlay').style.display = 'flex';
        try {
            // Ensure the wallet is connected
            if (!phantomWallet.connected) {
                await phantomWallet.connect();
            }

            // Use your proxy as the RPC endpoint
            const connection = new Connection('https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7', 'confirmed');

            // Find the associated token account of the user
            const associatedTokenAddress = await getAssociatedTokenAddress(
                TOKEN_MINT_ADDRESS,
                wallet
            );

            // Validate user input: check if the user has enough tokens
            const userTokenAccountInfo = await connection.getTokenAccountBalance(associatedTokenAddress);
            const userBalance = userTokenAccountInfo.value.uiAmount;

            if (amount > userBalance) {
                alert("Insufficient token balance.");
                return null;
            }

            // Calculate the amount in smallest units as an integer
            const amountInSmallestUnits = Math.round(amount * Math.pow(10, TOKEN_DECIMALS));

            // Create the burn instruction with the decimals parameter
            const burnInstruction = createBurnCheckedInstruction(
                associatedTokenAddress,
                TOKEN_MINT_ADDRESS,
                wallet,
                amountInSmallestUnits,
                TOKEN_DECIMALS // Include the decimals parameter
            );

            // Create a memo instruction
            const memoText = `Burning ${amount} Sir.Nibiru tokens for action[${action}]`;
            const memoInstruction = new TransactionInstruction({
                keys: [],
                programId: new PublicKey('MemoSq4gqABAXKb96qnH8TysNcWxMyWCqXgDLGmfcHr'),
                data: Buffer.from(memoText, 'utf-8'),
            });

            // Create a new transaction and add the memo and burn instructions
            let transaction = new Transaction().add(memoInstruction, burnInstruction);

            // Get the latest blockhash and last valid block height
            const latestBlockhash = await connection.getLatestBlockhash("finalized");

            // Set recent blockhash and fee payer
            transaction.recentBlockhash = latestBlockhash.blockhash;
            transaction.feePayer = wallet;

            // Sign and send the transaction using the wallet adapter
            const signedTransaction = await phantomWallet.signTransaction(transaction);
            const signature = await connection.sendRawTransaction(signedTransaction.serialize());

            // Confirm the transaction using the updated method
            await connection.confirmTransaction(
                {
                    signature: signature,
                    blockhash: latestBlockhash.blockhash,
                    lastValidBlockHeight: latestBlockhash.lastValidBlockHeight,
                },
                'finalized'
            );

            return signature;
        } catch (error) {
            console.error("Error performing burn action:", error);
            alert(`Transaction failed: ${error.message}`);
            return null;
        } finally {
            // Hide the loading overlay
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    }

    // Function to show the modal and disable body scrolling
    window.showHowTo = function () {
        document.getElementById("howToModal").style.display = "block";
        document.body.style.overflow = "hidden"; // Disable main page scrolling
    };

    // Function to close the modal and re-enable body scrolling
    window.closeModal = function () {
        document.getElementById("howToModal").style.display = "none";
        document.body.style.overflow = "auto"; // Re-enable main page scrolling
    };

    // Close modal when clicking outside of it
    window.onclick = function (event) {
        const modal = document.getElementById("howToModal");
        if (event.target === modal) {
            closeModal();
        }
    };
    async function fetchBestFriend() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/get_best_friend.php'); // Update with your actual URL
            const data = await response.json();

            if (data.status === "success") {
                document.querySelector(".best-friend-badge p:nth-of-type(1)").textContent = data.username;
                const donationAmount = data.topDonation.split('.')[0];
                document.querySelector(".best-friend-badge p:nth-of-type(2)").textContent = donationAmount;
            } else {
                console.error("Error fetching best friend data:", data.message);
                document.querySelector(".best-friend-badge h4").textContent = "Best Friend Not Found";
            }
        } catch (error) {
            console.error("Failed to fetch best friend data:", error);
        }
    }
    // Call the fetch function
    fetchBestFriend();
    // Fetch and display the monthly highscore by default
    fetchHighscore('monthly');

    // Variables to track if data has been loaded
    let monthlyDataLoaded = true; // Since we load it by default
    let allTimeDataLoaded = false;

    // Tab elements
    const monthlyTab = document.getElementById('monthlyTab');
    const allTimeTab = document.getElementById('allTimeTab');

    // Highscore list elements
    const monthlyHighscoreList = document.getElementById('monthlyHighscore');
    const allTimeHighscoreList = document.getElementById('allTimeHighscore');

    // Event listeners for tab clicks
    monthlyTab.addEventListener('click', () => {
        // Switch active tab styling
        monthlyTab.classList.add('active');
        allTimeTab.classList.remove('active');

        // Show monthly highscore list
        monthlyHighscoreList.style.display = 'block';
        allTimeHighscoreList.style.display = 'none';

        // No need to fetch data; it's already loaded
    });

    allTimeTab.addEventListener('click', () => {
        // Switch active tab styling
        allTimeTab.classList.add('active');
        monthlyTab.classList.remove('active');

        // Show all-time highscore list
        allTimeHighscoreList.style.display = 'block';
        monthlyHighscoreList.style.display = 'none';

        // Fetch data if not already loaded
        if (!allTimeDataLoaded) {
            fetchHighscore('all_time');
            allTimeDataLoaded = true;
        }
    });

    // Update fetchHighscore function to accept a parameter
    async function fetchHighscore(period) {
        try {
            const response = await fetch(`https://www.sir-nibiru.com/get_highscore.php?period=${period}`);
            const data = await response.json();

            if (data.status === "success") {
                const highscoreList = data.highscore;
                const highscoreContainer = period === 'monthly' ? monthlyHighscoreList : allTimeHighscoreList;

                // Clear any existing content
                highscoreContainer.innerHTML = '';

                highscoreList.forEach((donor, index) => {
                    const donationAmount = parseFloat(donor.total_donations) || 0;

                    // Create new elements for each donor
                    const highscoreItem = document.createElement("div");
                    highscoreItem.className = `highscore-item-${index % 2 === 0 ? 'even' : 'odd'}`;

                    highscoreItem.innerHTML = `
                        <span class="rank">${index + 1}</span>
                        <span class="name">${donor.username}</span>
                        <span class="amount">${donationAmount.toFixed(2)}</span>
                    `;

                    // Append the item to the highscore container
                    highscoreContainer.appendChild(highscoreItem);
                });
            } else {
                console.error("Error fetching highscore data:", data.message);
            }
        } catch (error) {
            console.error("Failed to fetch highscore data:", error);
        }
    }

    // Swipe gesture support for mobile devices
    let startX = 0;
    let endX = 0;

    // Add touch event listeners to the highscore section
    const highscoreSection = document.querySelector('.highscore');

    highscoreSection.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
    });

    highscoreSection.addEventListener('touchmove', (e) => {
        endX = e.touches[0].clientX;
    });

    highscoreSection.addEventListener('touchend', () => {
        if (startX - endX > 50) {
            // Swiped left, show All-Time Top 10
            allTimeTab.click();
        } else if (endX - startX > 50) {
            // Swiped right, show Monthly Top 10
            monthlyTab.click();
        }
    });
    async function fetchLastSitters() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/get_last_sitters.php'); // Update with actual URL
            const data = await response.json();

            if (data.status === "success") {
                const lastSitters = data.last_sitters;
                const lastSitterContainer = document.querySelector(".last-sitter p");

                // Join usernames with line breaks and update HTML
                lastSitterContainer.innerHTML = lastSitters.join("<br>");
            } else {
                console.error("Error fetching last sitters data:", data.message);
                document.querySelector(".last-sitter p").textContent = "No recent sitters found.";
            }
        } catch (error) {
            console.error("Failed to fetch last sitters data:", error);
        }
    }

    // Call the fetch function
    fetchLastSitters();

    // Fetch config values from the backend
    async function fetchConfig() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/fetch_config.php');
            const data = await response.json();
    
            if (data.status === "success") {
                const config = data.config;
                TOKEN_MINT_ADDRESS = new PublicKey(config.tokenMintAddress);
                TOKEN_DECIMALS = parseInt(config.tokenDecimals, 10);
                tokensForFullProgress = parseInt(config.tokensForFullProgress, 10) || tokensForFullProgress;
                decayRatePerHour = parseInt(config.decayRatePerHour, 10) || decayRatePerHour;
            } else {
                console.error("Failed to fetch config:", data.message);
            }
        } catch (error) {
            console.error("Error fetching config:", error);
        }
    }

    // Calculate the increase per token based on tokensForFullProgress
    const increasePerToken = MAX_PROGRESS / tokensForFullProgress;

    // Fetch and process donations based on their timestamp
    async function fetchAndProcessDonations() {
        try {
            // Reset bars to zero at the beginning of each calculation
            bars = { clean: 0, play: 0, feed: 0 };

            const response = await fetch('https://www.sir-nibiru.com/fetch_donations.php');
            const data = await response.json();

            if (data.status === "success") {
                const donations = data.donations;
                const now = new Date();

                donations.forEach(donation => {
                    const action = donation.action;
                    const donationAmount = parseFloat(donation.donation_amount);
                    const donationTimestamp = new Date(donation.timestamp);

                    // Calculate the increase based on donation amount
                    const increase = donationAmount * increasePerToken;

                    // Calculate elapsed hours since the donation
                    const elapsedHours = (now - donationTimestamp) / (1000 * 60 * 60);

                    // Calculate decay based on elapsed hours since donation
                    const decay = elapsedHours * decayRatePerHour;

                    // Calculate the net increase after decay
                    const netIncrease = Math.max(0, increase - decay);

                    // Update the bar, ensuring it doesn't exceed MAX_PROGRESS
                    
                    bars[action] = Math.min(bars[action] + netIncrease, MAX_PROGRESS);
                });
            } else {
                console.error("Failed to fetch donations:", data.message);
            }
            bars = { clean: 100, play: 100, feed: 100 };
            updateProgressBars();

        } catch (error) {
            console.error("Error fetching donations:", error);
        }
    }

    // Function to update DOM with new progress bar values
    function updateProgressBars() {
        document.querySelector('.progress-bar.blue').style.width = `${bars.clean}%`;
        document.querySelector('.progress-bar.green').style.width = `${bars.feed}%`;
        document.querySelector('.progress-bar.yellow').style.width = `${bars.play}%`;
    }

    // Poll backend for donations every 5 minutes
    setInterval(fetchAndProcessDonations, 60000); // 1 minute
    fetchAndProcessDonations();




});
