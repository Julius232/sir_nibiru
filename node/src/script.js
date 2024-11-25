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

// State to track mute
let isMuted = true;

// Popup functions
function showPopup() {
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.style.display = 'flex'; // Use 'flex' if you're using flexbox for centering
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    // DOM Elements
    const connectButton = document.getElementById('connect-wallet');
    const connectText = document.getElementById('connect-text');
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
    const eggPartyMusic = document.getElementById('eggPartyMusic');
    const postEggPartyMusic = document.getElementById('postEggPartyMusic');
    const muteButton = document.getElementById('muteButton');
    const closePopup = document.getElementById('closePopupButton');
    const redirectToDownload = document.getElementById('redirectToDownloadButton');

    eggPartyMusic.volume = 0.2; // Set volume to 20%
    postEggPartyMusic.volume = 0.2; // Set volume to 20%

    // Variables
    let monthlyDataLoaded = true; // Since we load it by default
    let allTimeDataLoaded = false;
    let startX = 0;
    let endX = 0;

    // Initialize
    await fetchConfig(); // Initialize configuration values
    restoreSession(); // Restore session from local storage
    fetchBestFriend(); // Fetch and display the best friend
    fetchHighscore('monthly'); // Fetch and display the monthly highscore
    fetchTotalBurned();
    fetchLastSitters(); // Fetch and display last sitters
    fetchAndProcessDonations(); // Fetch donations and update progress bars
    setInterval(fetchAndProcessDonations, 60000); // Poll backend every minute
    setupWalletEvents(); // Set up wallet event listeners
    handleDeepLinkReturn();

    closePopup.addEventListener('click', () => {
        const overlay = document.getElementById('overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    });

    redirectToDownload.addEventListener('click', () => {
        window.open('https://phantom.app/download', '_blank');
    });

    muteButton.addEventListener("click", () => {
        playMusicBasedOnPhase();
    });

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

    // Function to toggle mute
    function toggleMute() {
        isMuted = !isMuted;

        // Mute or unmute the audio
        eggPartyMusic.muted = isMuted;
        postEggPartyMusic.muted = isMuted;

        // Update button text
        muteButton.textContent = isMuted ? 'Play Audio' : 'Pause Audio';
    }

    // Attach toggleMute to the global window object
    window.toggleMute = toggleMute;

    // Function to play music based on phase
    function playMusicBasedOnPhase() {
        if (isEggParty) {
            postEggPartyMusic.pause();
            postEggPartyMusic.currentTime = 0;
            if (!isMuted) {
                eggPartyMusic.play().catch(error => console.error("Audio play error:", error));
            }
        } else {
            eggPartyMusic.pause();
            eggPartyMusic.currentTime = 0;
            if (!isMuted) {
                postEggPartyMusic.play().catch(error => console.error("Audio play error:", error));
            }
        }
    }

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
            const nonceResponse = await fetch('/sir_nibiru/get_nonce', {
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
            const verificationResponse = await fetch('/sir_nibiru/verify_user', {
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
                connectButton.style.color = 'black';
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
            document.getElementById('connect-wallet').classList.add('connected');
            connectText.textContent = storedUsername || "Options";
            connectText.style.color = 'black';
        }
    }

    // Set up wallet event listeners
    function setupWalletEvents() {
        phantomWallet.on('connect', () => {
            wallet = phantomWallet.publicKey;
            console.log("Wallet connected:", wallet.toString());
            connectText.textContent = "Options";
            connectText.style.color = 'black';
            handleNonceVerification(wallet);

            // Add connected class
            document.getElementById('connect-wallet').classList.add('connected');
        });

        phantomWallet.on('disconnect', () => {
            wallet = null;
            console.log("Wallet disconnected");

            // Remove connected class
            connectText.textContent = "";
            document.getElementById('connect-wallet').classList.remove('connected');
        });

    }


    // Handle nonce verification
    async function handleNonceVerification(wallet) {
        if (isSigning) return;

        isSigning = true;

        try {
            const walletAddress = wallet.toString();

            // Request a nonce from the backend
            const nonceResponse = await fetch('/sir_nibiru/get_nonce', {
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
            const verificationResponse = await fetch('/sir_nibiru/verify_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    signature: Buffer.from(signature).toString('base64')
                })
            });
            const data = await verificationResponse.json();

            if (data.status === "success") {
                connectText.textContent = data.username;
                connectText.style.color = 'black';
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
            const response = await fetch('/sir_nibiru/fetch_config');
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
            const response = await fetch('/sir_nibiru/get_best_friend');
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
            const response = await fetch(`/sir_nibiru/get_highscore?period=${period}`);
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
            const response = await fetch('/sir_nibiru/get_total_burned');
            const data = await response.json();

            if (data.status === "success") {
                // Update the total burned amount in the menu
                totalBurnAmount = parseFloat(data.total_donations).toFixed(0);
                isEggParty = totalBurnAmount < EGG_PARTY_TARGET;
                toggleButtonVisibility();
                document.getElementById("total-burn-amount").textContent = totalBurnAmount;
            } else {
                console.error("Error fetching total burned data:", data.message);
            }
        } catch (error) {
            console.error("Failed to fetch total burned data:", error);
        }
        updateProgressBars();
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
            const response = await fetch('/sir_nibiru/get_last_sitters');
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

            const response = await fetch('/sir_nibiru/fetch_donations');
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

    function toggleButtonVisibility() {
        const cleanButton = document.querySelector('.btn-clean');
        const playButton = document.querySelector('.btn-play');
        const feedButton = document.querySelector('.btn-feed');
        const howToButton = document.querySelector('.btn-howto');
        const hatchButton = document.querySelector('.btn-hatch');

        if (isEggParty) {
            cleanButton.style.display = 'none';
            playButton.style.display = 'none';
            feedButton.style.display = 'none';
            howToButton.style.display = 'none';
            hatchButton.style.display = 'flex'; // Show hatch button only in egg party mode
        } else {
            cleanButton.style.display = 'flex';
            playButton.style.display = 'flex';
            feedButton.style.display = 'flex';
            howToButton.style.display = 'flex';
            hatchButton.style.display = 'none'; // Hide hatch button if not in egg party mode
        }
    }

    // Update progress bars in the DOM
    function updateProgressBars() {
        const eggPartyPercentage = Math.min(totalBurnAmount / EGG_PARTY_TARGET, 1) * 100;

        if (isEggParty) {
            // Set all bars to the same value based on eggPartyPercentage
            document.querySelector('.progress-bar.yellow').style.width = `${eggPartyPercentage}%`;
            document.querySelector('.progress-bar.blue').style.width = `${eggPartyPercentage}%`;
            document.querySelector('.progress-bar.green').style.width = `${eggPartyPercentage}%`;
        }
        else {
            document.querySelector('.progress-bar.blue').style.width = `${bars.clean}%`;
            document.querySelector('.progress-bar.green').style.width = `${bars.feed}%`;
            document.querySelector('.progress-bar.yellow').style.width = `${bars.play}%`;
        }
    }

    async function fetchTransactionDetails() {
        // Retrieve values from local storage
        const walletAddress = localStorage.getItem('walletAddress');
        const username = localStorage.getItem('username');
        const nonce = localStorage.getItem('nonce');

        if (!walletAddress || !username || !nonce) {
            console.error("Missing parameters for fetching transaction details.");
            return;
        }

        try {
            const response = await fetch('/sir_nibiru/fetch_user_donations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    username: username,
                    nonce: nonce
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                const transactions = result.transactions;
                const transactionDetailsDiv = document.getElementById('transactionDetails');
                transactionDetailsDiv.innerHTML = '<h4>Transactions (maximum last 10):</h4>';

                transactions.forEach(tx => {
                    // Create container for each transaction item
                    const transactionItem = document.createElement('div');
                    transactionItem.className = 'transaction-item';
                    transactionItem.innerHTML = `
                        <div class="transaction-signature">
                            <span class="label">Signature:</span>
                            <a href="https://solscan.io/tx/${tx.signature}" target="_blank">Solscan</a>
                        </div>
                        <div class="transaction-action">
                            <span class="label">Action:</span> ${tx.action}
                        </div>
                        <div class="transaction-state">
                            <span class="label">State:</span> ${tx.state}
                        </div>
                        <div class="transaction-amount">
                            <span class="label">Amount:</span> ${tx.donation_amount}
                        </div>
                    `;
                    transactionDetailsDiv.appendChild(transactionItem);
                });
            } else {
                console.error('Failed to fetch transactions:', result.message);
            }
        } catch (error) {
            console.error('Error fetching transaction details:', error);
        }
    }

    async function fetchUserDonationTotal() {
        // Retrieve values from local storage
        const walletAddress = localStorage.getItem('walletAddress');
        const username = localStorage.getItem('username');
        const nonce = localStorage.getItem('nonce');

        if (!walletAddress || !username || !nonce) {
            console.error("Missing parameters for fetching donation total.");
            return;
        }

        try {
            const response = await fetch('/sir_nibiru/fetch_user_donation_total', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    username: username,
                    nonce: nonce
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Update the user's burned tokens total in the UI
                const totalDonations = result.total_donations || 0; // Fallback to 0 if not returned
                const userBurnAmount = document.getElementById('user-burn-amount');
                userBurnAmount.innerHTML = totalDonations;
            } else {
                console.error('Failed to fetch donation total:', result.message);
            }
        } catch (error) {
            console.error('Error fetching donation total:', error);
        }
    }

    // Show wallet options overlay
    function showWalletOptions() {
        const username = localStorage.getItem('username') || 'Not Set';
        // Update the username display
        const currentUsernameElement = document.getElementById('currentUsername');
        currentUsernameElement.innerHTML = username;
        overlay.style.display = 'flex';
        fetchUserDonationTotal();
        fetchTransactionDetails();
    }

    // Close wallet options overlay
    window.closeWalletOptions = function () {
        overlay.style.display = 'none';
    };

    // Utility function to detect iPhone devices
    function isIPhoneDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    // Utility function to detect Android devices
    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }


    // Handle deep link return from Phantom
    function handleDeepLinkReturn() {
        const urlParams = new URLSearchParams(window.location.search);
        const publicKey = urlParams.get('public_key');
        const session = urlParams.get('session');
        const signature = urlParams.get('signature');
        const errorCode = urlParams.get('errorCode');
        const errorMessage = urlParams.get('errorMessage');

        if (errorCode) {
            console.error('Error from Phantom:', errorMessage);
            alert(`Error from Phantom: ${errorMessage}`);
            return;
        }

        if (publicKey && signature) {
            // User has signed the message
            const walletAddress = publicKey;

            // Retrieve the nonce from sessionStorage
            const nonce = sessionStorage.getItem('nonce');

            if (!nonce) {
                console.error('Nonce not found in session storage.');
                return;
            }

            // Send signature to backend for verification
            fetch('/sir_nibiru/verify_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    wallet_address: walletAddress,
                    signature: signature,
                    nonce: nonce, // Include nonce for verification
                    public_key: publicKey
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        connectText.textContent = data.username;
                        connectText.style.color = 'black';
                        sessionStorage.setItem('walletAddress', walletAddress);
                        sessionStorage.setItem('username', data.username);
                    } else {
                        console.error("Nonce verification failed:", data.error);
                    }
                })
                .catch(err => {
                    console.error("Failed to verify nonce with backend:", err);
                });
        } else if (publicKey && session) {
            // Initial connection after connect deep link
            wallet = publicKey;
            connectText.textContent = "Options";
            connectText.style.color = 'black';

            // Store the session for later use
            sessionStorage.setItem('phantomSession', session);

            handleNonceVerification(wallet);
        }
    }

    async function connectWallet() {
        console.log("connectWallet function triggered");
        if (wallet) {
            showWalletOptions();
            return;
        }

        try {
            const isiPhone = isIPhoneDevice();
            const isAndroid = isAndroidDevice();
            const isMobile = isiPhone || isAndroid;
            const provider = window.phantom?.solana || window.solana;
            const isPhantomInstalled = provider && provider.isPhantom;

            if (isMobile) {
                if (isPhantomInstalled) {
                    await phantomWallet.connect();
                    if (!phantomWallet.connected) {
                        throw 'Failed to connect to Phantom wallet.';
                    }
                } else {
                    if (isiPhone) {
                        await phantomWallet.connect();
                    }
                    showPopup();
                    return;
                }
            } else {
                if (!isPhantomInstalled) {
                    showPopup();
                    return;
                }
                await phantomWallet.connect();
                if (!phantomWallet.connected) {
                    throw 'Failed to connect to Phantom wallet.';
                }
            }

            wallet = phantomWallet.publicKey;
            connectText.textContent = "Options";
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
            const response = await fetch('/sir_nibiru/disconnect_user', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_address: walletAddress })
            });
            const result = await response.json();

            if (result.status === "success") {
                // Clear localStorage and reset wallet variable
                localStorage.removeItem('walletAddress');
                localStorage.removeItem('username');
                localStorage.removeItem('nonce');
                wallet = null;

                // Update connect button
                connectText.textContent = "";
                document.getElementById('connect-wallet').classList.remove('connected');
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
            const response = await fetch('/sir_nibiru/update_username', {
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
                connectText.textContent = newUsername;
                closeWalletOptions();
            } else {
                alert("Failed to update username: " + data.message);
            }
        } catch (error) {
            alert("An error occurred while updating the username.");
        }
    };

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
        document.getElementById("actionTitle").textContent = `BURN FOR ${action.charAt(0).toUpperCase() + action.slice(1)}`;

        const actionDescription = document.getElementById("actionDescription");

        // Set a unique mystical description for each action
        if (action === "hatch") {
            actionDescription.innerHTML = `
                <div class="triangle-text">
                    <p class="triangle-line large">Through tokens burned, the magic’s bound,</p>
                    <p class="triangle-line medium">In sacred fire, new life is found.</p>
                    <p class="triangle-line small">Embers dance, the spell takes hold,</p>
                    <p class="triangle-line tiny">Unlock the gate with courage bold.</p>
                    <p class="triangle-line center">Awake, Nibiru, rise from flame.</p>
                </div>
                `;
        } else if (action === "clean") {
            actionDescription.innerHTML = `
                <div class="triangle-text">
                    <p class="triangle-line large">By water’s grace and spirit’s light,</p>
                    <p class="triangle-line medium">Purge the darkness, make all bright.</p>
                    <p class="triangle-line small">Through cleansing fire, the soul renews,</p>
                    <p class="triangle-line tiny">Pure and whole, in sacred hues.</p>
                    <p class="triangle-line center">Restore, Nibiru, fresh and true.</p>
                </div>
                `;
        } else if (action === "play") {
            actionDescription.innerHTML = `
                <div class="triangle-text">
                    <p class="triangle-line large">With sparks of joy and laughter’s call,</p>
                    <p class="triangle-line medium">The soul awakens, free for all.</p>
                    <p class="triangle-line small">A dance of light, a leap of faith,</p>
                    <p class="triangle-line tiny">Revive the spirit, pure and safe.</p>
                    <p class="triangle-line center">Rejoice, Nibiru, in boundless play.</p>
                </div>
                `;
        } else if (action === "feed") {
            actionDescription.innerHTML = `
                <div class="triangle-text">
                    <p class="triangle-line large">With gifts of earth, the spirit grows,</p>
                    <p class="triangle-line medium">Through bounty’s hand, life’s current flows.</p>
                    <p class="triangle-line small">Feed the heart, fuel the fire within,</p>
                    <p class="triangle-line tiny">Strength and grace shall now begin.</p>
                    <p class="triangle-line center">Thrive, Nibiru, with life anew.</p>
                </div>
                `;
        }

        actionDescription.style.display = "block"; // Ensure the description is visible
        document.getElementById("actionOverlay").style.display = "flex";
    };

    // Close action form overlay
    window.closeActionForm = function () {
        document.getElementById("actionOverlay").style.display = "none";
    };

    window.updateTokenAmountColor = async function () {
        const inputField = document.getElementById('tokenAmountInput');
        const value = parseInt(inputField.value) || 0; // Parse the value as an integer, default to 0 if empty or invalid
        if (value >= 1000 && value < 10000) {
            inputField.style.color = 'yellow';
        } else if (value >= 10000 && value < 1000000) {
            inputField.style.color = 'orange';
        } else if (value >= 1000000 && value < 10000000) {
            inputField.style.color = 'red';
        } else if (value >= 10000000) {
            inputField.style.color = 'black';
        }
    }

    // Adjusted 'submitAction' function
    window.submitAction = async function () {
        const tokenAmountInput = parseInt(document.getElementById("tokenAmountInput").value) || 0;
        if (tokenAmountInput < 1000) {
            alert("The minimum token amount is 1000. Please enter a valid amount.");
            return;
        }
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
            const response = await fetch('/sir_nibiru/submit_burn', {
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
                // Close the action overlay
                closeActionForm();
                // Fetch and process donations to update progress bars
                await fetchAndProcessDonations();
                // Update total burned tokens
                await fetchTotalBurned();
                // Update last sitters
                await fetchLastSitters();
                // Update best friend
                await fetchBestFriend();
                // Update user donation total if the user is logged in
                if (wallet) {
                    await fetchUserDonationTotal();
                }
            } else {
                alert(`Action failed: ${result.message}`);
            }
        } catch (error) {
            console.error("Error submitting action:", error);
            alert("Failed to submit action. Please try again.");
        }
    };

    // Helper function to decode base64
    function decodeBase64(encodedString) {
        return Buffer.from(encodedString, 'base64').toString('ascii');
    }

    async function performBurnAction(amount, action) {
        const loadingOverlay = document.getElementById('loadingOverlay');
        const overlayContent = document.querySelector('#loadingOverlay .overlay-content p');

        // Ensure the overlay and content are present
        if (!loadingOverlay || !overlayContent) {
            console.error("Loading overlay or content not found in DOM.");
            return;
        }

        // Show the overlay
        loadingOverlay.style.display = 'flex';

        try {
            // Update text to indicate wallet connection
            overlayContent.textContent = 'Connecting wallet...';

            // Ensure the wallet is connected
            if (!phantomWallet.connected) {
                await phantomWallet.connect();
            }

            // Decode the unique ID at runtime
            overlayContent.textContent = 'Initializing connection...';
            const rpcEndpoint = 'https://tame-few-season.solana-mainnet.quiknode.pro/f6d67e803c7016d76b4d81614f5c3b48531639d7';
            const connection = new Connection(rpcEndpoint, 'finalized');

            // Retrieve associated token account
            overlayContent.textContent = 'Retrieving token account...';
            const associatedTokenAddress = await getAssociatedTokenAddress(
                TOKEN_MINT_ADDRESS,
                wallet
            );

            // Verify token balance
            overlayContent.textContent = 'Checking token balance...';
            const userTokenAccountInfo = await connection.getTokenAccountBalance(associatedTokenAddress, 'finalized');
            const userBalance = userTokenAccountInfo.value.uiAmount;

            if (amount > userBalance) {
                alert("Insufficient token balance.");
                return null;
            }

            // Calculate the amount in smallest units
            const amountInSmallestUnits = Math.round(amount * Math.pow(10, TOKEN_DECIMALS));

            // Create burn instruction
            overlayContent.textContent = 'Preparing burn transaction...';
            const burnInstruction = createBurnCheckedInstruction(
                associatedTokenAddress,
                TOKEN_MINT_ADDRESS,
                wallet,
                amountInSmallestUnits,
                TOKEN_DECIMALS
            );

            // Add memo instruction
            const memoText = `Burning ${amount} Sir.Nibiru tokens for action [${action}]`;
            const memoInstruction = new TransactionInstruction({
                keys: [],
                programId: new PublicKey('MemoSq4gqABAXKb96qnH8TysNcWxMyWCqXgDLGmfcHr'),
                data: Buffer.from(memoText, 'utf-8'),
            });

            // Create and prepare transaction
            overlayContent.textContent = 'Finalizing transaction...';
            let transaction = new Transaction().add(memoInstruction, burnInstruction);
            const latestBlockhash = await connection.getLatestBlockhash('finalized');
            transaction.recentBlockhash = latestBlockhash.blockhash;
            transaction.feePayer = wallet;

            // Sign transaction
            overlayContent.textContent = 'Signing transaction...';
            let signedTransaction = await phantomWallet.signTransaction(transaction);

            // Serialize and send transaction
            overlayContent.textContent = 'Sending transaction to network...';
            const serializedTransaction = signedTransaction.serialize();
            const signature = await connection.sendRawTransaction(serializedTransaction, { preflightCommitment: 'finalized' });

            // Confirm transaction status
            const maxWaitTime = 60000; // 60 seconds max wait
            const startTime = Date.now();
            overlayContent.textContent = 'Waiting for transaction confirmation...';

            // Update the status message
            overlayContent.textContent = 'Waiting for transaction confirmation...';

            // Add a new line for "DO NOT CLOSE THE BROWSER WINDOW"
            let doNotCloseMessage = document.querySelector('.do-not-close'); // Check if it already exists
            if (!doNotCloseMessage) {
                doNotCloseMessage = document.createElement('p');
                doNotCloseMessage.textContent = 'DO NOT CLOSE THE BROWSER WINDOW';
                doNotCloseMessage.style.color = 'red';
                doNotCloseMessage.style.fontWeight = 'bold';
                doNotCloseMessage.style.marginTop = '10px';
                doNotCloseMessage.classList.add('do-not-close');
                overlayContent.parentElement.appendChild(doNotCloseMessage); // Append it after the current overlay content
            }


            while (Date.now() - startTime < maxWaitTime) {
                const status = await connection.getSignatureStatus(signature, { searchTransactionHistory: true });
                if (status.value?.confirmationStatus === 'finalized') {
                    overlayContent.textContent = 'Transaction confirmed!';
                    await new Promise(resolve => setTimeout(resolve, 2000)); // Briefly show confirmation
                    return signature;
                }
                await new Promise(resolve => setTimeout(resolve, 2000)); // Poll every 2 seconds
            }

            throw new Error('Transaction confirmation timed out.');
        } catch (error) {
            console.error("Error performing burn action:", error);
            overlayContent.textContent = 'Transaction failed! Please try again.';
            alert(`Transaction failed: ${error.message}`);
            return null;
        } finally {
            // Hide the loading overlay after processing
            await new Promise(resolve => setTimeout(resolve, 2000)); // Optional delay to display the final status
            loadingOverlay.style.display = 'none';
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