import {
    Connection,
    PublicKey,
    Transaction,
    TransactionInstruction,
    sendAndConfirmRawTransaction, // Ensure this is imported
} from '@solana/web3.js';
import { PhantomWalletAdapter } from '@solana/wallet-adapter-phantom';
import { Buffer } from 'buffer';
import {
    getAssociatedTokenAddress,
    createBurnCheckedInstruction,
} from '@solana/spl-token';

if (!window.Buffer) window.Buffer = Buffer;

// Global Variables
let wallet = null;
const phantomWallet = new PhantomWalletAdapter();
let TOKEN_MINT_ADDRESS, TOKEN_DECIMALS;
const MAX_PROGRESS = 100;
let tokensForFullProgress = 10000; // Default values
let decayRatePerHour = 10; // Default values
let bars = { clean: 0, play: 0, feed: 0 }; // Initial bar values
let isSigning = false;
let selectedAction = null;
const increasePerToken = MAX_PROGRESS / tokensForFullProgress;

document.addEventListener("DOMContentLoaded", async () => {
    // DOM Elements
    const connectButton = document.getElementById('connect-wallet');
    const overlay = document.getElementById('walletOptionsOverlay');
    const newUsernameInput = document.getElementById('newUsernameInput');
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('nav-links');
    const scrollTopButton = document.getElementById('scrollTop');
    const monthlyTab = document.getElementById('monthlyTab');
    const allTimeTab = document.getElementById('allTimeTab');
    const monthlyHighscoreList = document.getElementById('monthlyHighscore');
    const allTimeHighscoreList = document.getElementById('allTimeHighscore');
    const highscoreSection = document.querySelector('.highscore');

    // Variables
    let monthlyDataLoaded = true; // Since we load it by default
    let allTimeDataLoaded = false;
    let startX = 0;
    let endX = 0;

    // Initialize
    await fetchConfig(); // Initialize configuration values
    restoreSession(); // Restore session from local storage
    setupWalletEvents(); // Set up wallet event listeners
    fetchBestFriend(); // Fetch and display the best friend
    fetchHighscore('monthly'); // Fetch and display the monthly highscore
    fetchTotalBurned();
    fetchLastSitters(); // Fetch and display last sitters
    fetchAndProcessDonations(); // Fetch donations and update progress bars
    setInterval(fetchAndProcessDonations, 60000); // Poll backend every minute


    // Event Listeners
    connectButton.addEventListener("click", () => {
        if (wallet) {
            showWalletOptions();
        } else {
            connectWallet();
        }
    });

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    scrollTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    monthlyTab.addEventListener('click', () => {
        switchToMonthlyTab();
    });

    allTimeTab.addEventListener('click', () => {
        switchToAllTimeTab();
    });

    highscoreSection.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
    });

    highscoreSection.addEventListener('touchmove', (e) => {
        endX = e.touches[0].clientX;
    });

    highscoreSection.addEventListener('touchend', () => {
        handleHighscoreSwipe();
    });

    // Function to check if the nonce is valid (i.e., not older than 6 days)
    function isNonceValid() {
        const nonceTimestamp = localStorage.getItem('nonceTimestamp');
        if (!nonceTimestamp) return false;
        const nonceAgeMs = Date.now() - parseInt(nonceTimestamp, 10);
        const nonceAgeDays = nonceAgeMs / (1000 * 60 * 60 * 24);
        return nonceAgeDays < 6; // Nonce is valid if it's less than 6 days old
    }

    // Function to refresh the nonce by fetching a new one and signing it
    async function refreshNonce() {
        if (isSigning) return false; // Prevent duplicate calls

        isSigning = true;

        try {
            const walletAddress = wallet.toString();

            // Request a new nonce from the backend
            const nonceResponse = await fetch('https://www.sir-nibiru.com/get_nonce.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_address: walletAddress })
            });
            const nonceData = await nonceResponse.json();

            if (!nonceData.nonce) {
                console.error("Nonce not provided by the backend.");
                return false;
            }

            const nonce = nonceData.nonce;

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
                localStorage.setItem('nonce', nonce);
                localStorage.setItem('nonceTimestamp', Date.now()); // Store the timestamp
                return true;
            } else {
                console.error("Nonce verification failed:", data.error);
                return false;
            }
        } catch (err) {
            console.error("Failed to verify nonce with backend:", err);
            return false;
        } finally {
            isSigning = false;
        }
    }

    // Function to ensure the nonce is valid before proceeding
    async function ensureNonceValid() {
        if (isNonceValid()) {
            return true;
        } else {
            const success = await refreshNonce();
            return success;
        }
    }

    // Restore session from local storage
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

    // Set up wallet event listeners
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

    // Adjusted 'handleNonceVerification' function to store nonce timestamp
    async function handleNonceVerification(wallet) {
        if (isSigning) return;

        isSigning = true;

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
            localStorage.setItem('nonceTimestamp', Date.now()); // Store the timestamp

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
            isSigning = false;
        }
    }

    // Fetch configuration from backend
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

                // Update Jupiter link and address
                updateJupiterLink(TOKEN_MINT_ADDRESS.toString());
                updateAddress(TOKEN_MINT_ADDRESS.toString());
            } else {
                console.error("Failed to fetch config:", data.message);
            }
        } catch (error) {
            console.error("Error fetching config:", error);
        }
    }

    // Update Jupiter link with token mint address
    function updateJupiterLink(tokenMint) {
        const jupiterLink = document.querySelector('.icon.jupiter');
        if (jupiterLink) {
            jupiterLink.href = `https://jup.ag/swap/SOL-${tokenMint}`;
        }
    }

    // Update copy address button
    function updateAddress(newAddress) {
        document.getElementById("copyButton").setAttribute("data-address", newAddress);
    }

    // Fetch and display the best friend
    async function fetchBestFriend() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/get_best_friend.php');
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

    // Fetch and display highscore
    async function fetchHighscore(period) {
        const highscoreContainer = period === 'monthly' ? monthlyHighscoreList : allTimeHighscoreList;

        try {
            const response = await fetch(`https://www.sir-nibiru.com/get_highscore.php?period=${period}`);
            const data = await response.json();

            if (data.status === "success") {
                data.highscore.forEach((donor, index) => {
                    const highscoreItem = document.createElement("div");
                    highscoreItem.className = `highscore-item highscore-item-${index % 2 === 0 ? 'even' : 'odd'}`;

                    highscoreItem.innerHTML = `
                        <span class="rank">${index + 1}</span>
                        <span class="username">${donor.username}</span>
                        <span class="score">${parseFloat(donor.total_donations).toFixed(0)}</span>
                    `;

                    highscoreContainer.appendChild(highscoreItem);
                });
            } else {
                console.error("Error fetching highscore data:", data.message);
            }
        } catch (error) {
            console.error("Failed to fetch highscore data:", error);
        }
    }

    // Fetch and display total burned tokens
    async function fetchTotalBurned() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/get_total_burned.php');
            const data = await response.json();

            if (data.status === "success") {
                // Update the total burned amount in the menu
                document.getElementById("total-burn-amount").textContent = parseFloat(data.total_donations).toFixed(0);
            } else {
                console.error("Error fetching total burned data:", data.message);
            }
        } catch (error) {
            console.error("Failed to fetch total burned data:", error);
        }
    }

    // Switch to Monthly Tab
    function switchToMonthlyTab() {
        monthlyTab.classList.add('active');
        allTimeTab.classList.remove('active');
        monthlyHighscoreList.style.display = 'block';
        allTimeHighscoreList.style.display = 'none';
    }

    // Switch to All-Time Tab
    function switchToAllTimeTab() {
        allTimeTab.classList.add('active');
        monthlyTab.classList.remove('active');
        allTimeHighscoreList.style.display = 'block';
        monthlyHighscoreList.style.display = 'none';

        if (!allTimeDataLoaded) {
            fetchHighscore('all_time');
            allTimeDataLoaded = true;
        }
    }

    // Handle swipe gestures for highscore section
    function handleHighscoreSwipe() {
        if (startX - endX > 50) {
            // Swiped left, show All-Time Top 10
            allTimeTab.click();
        } else if (endX - startX > 50) {
            // Swiped right, show Monthly Top 10
            monthlyTab.click();
        }
    }

    // Fetch and display last sitters
    async function fetchLastSitters() {
        try {
            const response = await fetch('https://www.sir-nibiru.com/get_last_sitters.php');
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

    // Fetch and process donations
    async function fetchAndProcessDonations() {
        try {
            // Reset bars to zero
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

                    // Calculate decay
                    const decay = elapsedHours * decayRatePerHour;

                    // Calculate net increase after decay
                    const netIncrease = Math.max(0, increase - decay);

                    // Update the bar, ensuring it doesn't exceed MAX_PROGRESS
                    bars[action] = Math.min(bars[action] + netIncrease, MAX_PROGRESS);
                });
            } else {
                console.error("Failed to fetch donations:", data.message);
            }

            updateProgressBars();
        } catch (error) {
            console.error("Error fetching donations:", error);
        }
    }

    // Update progress bars in the DOM
    function updateProgressBars() {
        document.querySelector('.progress-bar.blue').style.width = `${bars.clean}%`;
        document.querySelector('.progress-bar.green').style.width = `${bars.feed}%`;
        document.querySelector('.progress-bar.yellow').style.width = `${bars.play}%`;
    }

    // Show wallet options overlay
    function showWalletOptions() {
        overlay.style.display = 'flex';
    }

    // Close wallet options overlay
    window.closeWalletOptions = function () {
        overlay.style.display = 'none';
    };

    // Connect wallet function
    async function connectWallet() {
        if (wallet) {
            showWalletOptions();
            return;
        }

        try {
            await phantomWallet.connect();
            if (!phantomWallet.connected) {
                throw 'Failed to connect to Phantom wallet.';
            }

            wallet = phantomWallet.publicKey;
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

    // Adjusted 'changeUsername' function
    window.changeUsername = async function () {
        if (!wallet) {
            alert("Please connect your wallet first.");
            return;
        }

        const nonceValid = await ensureNonceValid();
        if (!nonceValid) {
            alert("Failed to refresh session. Please try again.");
            return;
        }

        const newUsername = newUsernameInput.value.trim();
        const walletAddress = wallet.toString();
        const nonce = localStorage.getItem('nonce');
        const maxUsernameLength = 12;

        // Frontend Validation
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
                localStorage.setItem('nonceTimestamp', Date.now()); // Update nonce timestamp
                connectButton.textContent = newUsername;
                closeWalletOptions();
            } else {
                alert("Failed to update username: " + data.message);
            }
        } catch (error) {
            alert("An error occurred while updating the username.");
        }
    };

    // Adjusted 'openActionForm' function
    window.openActionForm = async function (action) {
        if (!wallet) {
            alert("Please connect your wallet first.");
            return;
        }

        const nonceValid = await ensureNonceValid();
        if (!nonceValid) {
            alert("Failed to refresh session. Please try again.");
            return;
        }

        selectedAction = action;
        document.getElementById("actionTitle").textContent = `Perform ${action.charAt(0).toUpperCase() + action.slice(1)} Action`;
        document.getElementById("actionOverlay").style.display = "flex";
    };

    // Close action form overlay
    window.closeActionForm = function () {
        document.getElementById("actionOverlay").style.display = "none";
    };

    // Adjusted 'submitAction' function
    window.submitAction = async function () {
        const nonceValid = await ensureNonceValid();
        if (!nonceValid) {
            alert("Failed to refresh session. Please try again.");
            return;
        }

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

            const nonce = localStorage.getItem('nonce'); // Get the nonce

            // Send the transaction signature and nonce to the backend
            const response = await fetch('https://www.sir-nibiru.com/submit_burn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: wallet.toString(),
                    action: selectedAction,
                    signature: transactionSignature,
                    nonce: nonce
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
    
            // Use your RPC endpoint with finalized commitment
            const connection = new Connection('https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7', 'finalized');
    
            // Find the associated token account of the user
            const associatedTokenAddress = await getAssociatedTokenAddress(
                TOKEN_MINT_ADDRESS,
                wallet
            );
    
            // Validate user input
            const userTokenAccountInfo = await connection.getTokenAccountBalance(associatedTokenAddress, 'finalized');
            const userBalance = userTokenAccountInfo.value.uiAmount;
    
            if (amount > userBalance) {
                alert("Insufficient token balance.");
                return null;
            }
    
            // Calculate the amount in smallest units
            const amountInSmallestUnits = Math.round(amount * Math.pow(10, TOKEN_DECIMALS));
    
            // Create the burn instruction
            const burnInstruction = createBurnCheckedInstruction(
                associatedTokenAddress,
                TOKEN_MINT_ADDRESS,
                wallet,
                amountInSmallestUnits,
                TOKEN_DECIMALS
            );
    
            // Create a memo instruction
            const memoText = `Burning ${amount} Sir.Nibiru tokens for action[${action}]`;
            const memoInstruction = new TransactionInstruction({
                keys: [],
                programId: new PublicKey('MemoSq4gqABAXKb96qnH8TysNcWxMyWCqXgDLGmfcHr'),
                data: Buffer.from(memoText, 'utf-8'),
            });
    
            // Create a new transaction and add the instructions
            let transaction = new Transaction().add(memoInstruction, burnInstruction);
    
            // Get the latest blockhash
            const latestBlockhash = await connection.getLatestBlockhash('finalized');
    
            // Set transaction recentBlockhash and feePayer
            transaction.recentBlockhash = latestBlockhash.blockhash;
            transaction.feePayer = wallet;
    
            // Sign the transaction
            let signedTransaction = await phantomWallet.signTransaction(transaction);
    
            // Serialize the transaction
            const serializedTransaction = signedTransaction.serialize();
    
            // Send the transaction with finalized commitment
            const signature = await connection.sendRawTransaction(serializedTransaction, { preflightCommitment: 'finalized' });
    
            // Wait for the transaction to reach finalized status
            const maxWaitTime = 60000; // Maximum wait time of 60 seconds
            const startTime = Date.now();
    
            while (Date.now() - startTime < maxWaitTime) {
                // Check transaction status for finalized confirmation
                const status = await connection.getSignatureStatus(signature, { searchTransactionHistory: true });
    
                if (status.value?.confirmationStatus === 'finalized') {
                    return signature; // Transaction is finalized
                }
    
                // Wait for the next poll interval
                await new Promise(resolve => setTimeout(resolve, 5000)); // Poll every 2 seconds
            }
    
            // If transaction wasn't confirmed within the max wait time, throw an error
            throw new Error('Transaction confirmation timed out.');
        } catch (error) {
            console.error("Error performing burn action:", error);
            alert(`Transaction failed: ${error.message}`);
            return null;
        } finally {
            // Hide the loading overlay
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    }
    

    // Show "How To" modal and disable body scrolling
    window.showHowTo = function () {
        document.getElementById("howToModal").style.display = "block";
        document.body.style.overflow = "hidden";
    };

    // Close modal and re-enable body scrolling
    window.closeModal = function () {
        document.getElementById("howToModal").style.display = "none";
        document.body.style.overflow = "auto";
    };

    // Close modal when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById("howToModal");
        if (event.target === modal) {
            closeModal();
        }
    };
});