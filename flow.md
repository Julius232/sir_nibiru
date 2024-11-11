**Flow of User Connect, Nonce Creation, Nonce Storage, Backend Calls, and Burn Actions**

---

### **1. Page Load and Initialization**

- **Client loads the page.**
  
- **Client fetches configuration data from the backend:**

  ```
  Client GET --> https://www.sir-nibiru.com/fetch_config.php
  ```

  - **Server returns:**

    ```json
    {
      "status": "success",
      "config": {
        "tokenMintAddress": "...",
        "tokenDecimals": "...",
        "tokensForFullProgress": "...",
        "decayRatePerHour": "..."
      }
    }
    ```

- **Client stores configuration data:**

  ```
  TOKEN_MINT_ADDRESS = config.tokenMintAddress
  TOKEN_DECIMALS = config.tokenDecimals
  tokensForFullProgress = config.tokensForFullProgress
  decayRatePerHour = config.decayRatePerHour
  ```

- **Client attempts to restore session from localStorage:**

  ```
  walletAddress = localStorage.getItem('walletAddress')
  username = localStorage.getItem('username')
  ```

  - If both are present, client sets:

    ```
    wallet = new PublicKey(walletAddress)
    connectButton.textContent = username
    ```

### **2. User Connects Wallet**

- **User clicks 'Connect Wallet' button.**

- **Client checks if wallet is connected:**

  - If not, calls `connectWallet()` function.

- **Client initiates connection with Phantom Wallet:**

  ```
  phantomWallet.connect()
  ```

- **Phantom Wallet prompts user for connection approval.**

- **On successful connection:**

  ```
  wallet = phantomWallet.publicKey
  connectButton.textContent = "Options"
  ```

- **Client initiates nonce verification:**

  ```
  handleNonceVerification(wallet)
  ```

### **3. Nonce Creation and Verification**

- **Nonce Request:**

  - **Client sends POST request to request nonce:**

    ```
    Client POST --> https://www.sir-nibiru.com/get_nonce.php
    Body: { "wallet_address": wallet.toString() }
    ```

  - **Server processes and returns nonce:**

    ```json
    { "nonce": "random_nonce_value" }
    ```

  - **Client stores nonce:**

    ```
    localStorage.setItem('nonce', nonce)
    ```

- **Nonce Signing:**

  - **Client encodes nonce:**

    ```
    encodedNonce = new TextEncoder().encode(nonce)
    ```

  - **Client requests signature from Phantom Wallet:**

    ```
    signature = await phantomWallet.signMessage(encodedNonce)
    ```

  - **User approves message signing in Phantom Wallet.**

- **Nonce Verification:**

  - **Client sends signature to backend for verification:**

    ```
    Client POST --> https://www.sir-nibiru.com/verify_user.php
    Body: {
      "wallet_address": wallet.toString(),
      "signature": Buffer.from(signature).toString('base64')
    }
    ```

  - **Server verifies signature and returns status:**

    ```json
    {
      "status": "success",
      "username": "user's username"
    }
    ```

  - **Client updates UI and stores data:**

    ```
    connectButton.textContent = username
    localStorage.setItem('walletAddress', wallet.toString())
    localStorage.setItem('username', username)
    ```

### **4. User Changes Username (Optional)**

- **User accesses wallet options and inputs new username.**

- **Client collects new username and nonce:**

  ```
  newUsername = newUsernameInput.value.trim()
  nonce = localStorage.getItem('nonce')
  ```

- **Client sends POST request to update username:**

  ```
  Client POST --> https://www.sir-nibiru.com/update_username.php
  Body: {
    "wallet_address": wallet.toString(),
    "new_username": newUsername,
    "nonce": nonce
  }
  ```

- **Server updates username and returns new nonce:**

  ```json
  {
    "status": "success",
    "new_nonce": "new_nonce_value"
  }
  ```

- **Client updates localStorage and UI:**

  ```
  localStorage.setItem('username', newUsername)
  localStorage.setItem('nonce', new_nonce)
  connectButton.textContent = newUsername
  ```

### **5. User Disconnects Wallet (Optional)**

- **User selects 'Disconnect' option.**

- **Client sends POST request to disconnect user:**

  ```
  Client POST --> https://www.sir-nibiru.com/disconnect_user.php
  Body: { "wallet_address": wallet.toString() }
  ```

- **Server removes nonce and returns status:**

  ```json
  { "status": "success" }
  ```

- **Client clears stored data and updates UI:**

  ```
  localStorage.removeItem('walletAddress')
  localStorage.removeItem('username')
  localStorage.removeItem('nonce')
  wallet = null
  connectButton.textContent = "Connect Wallet"
  phantomWallet.disconnect()
  ```

### **6. User Performs an Action (Burn Tokens)**

- **User selects an action (e.g., 'clean').**

- **Client displays token amount input overlay.**

- **User inputs token amount and submits.**

- **Client validates input and initiates burn action:**

  ```
  tokenAmount = parseFloat(tokenAmountInput.value.trim())
  submitAction()
  ```

- **Client calls `performBurnAction(amount, action)`:**

  - **Ensures wallet is connected.**

  - **Connects to Solana via RPC endpoint.**

  - **Finds associated token account:**

    ```
    associatedTokenAddress = await getAssociatedTokenAddress(TOKEN_MINT_ADDRESS, wallet)
    ```

  - **Retrieves user token balance and validates amount.**

  - **Creates burn and memo instructions.**

  - **Creates and signs transaction:**

    ```
    transaction = new Transaction().add(memoInstruction, burnInstruction)
    signedTransaction = await phantomWallet.signTransaction(transaction)
    ```

    - **User approves transaction in Phantom Wallet.**

  - **Sends transaction to Solana blockchain:**

    ```
    signature = await connection.sendRawTransaction(signedTransaction.serialize())
    ```

  - **Confirms transaction and returns signature.**

- **Client submits transaction signature to backend:**

  ```
  Client POST --> https://www.sir-nibiru.com/submit_burn.php
  Body: {
    "wallet_address": wallet.toString(),
    "action": selectedAction,
    "signature": transactionSignature
  }
  ```

- **Server verifies burn action and updates records:**

  ```json
  { "status": "success" }
  ```

- **Client informs user of success and refreshes data.**

### **7. Fetching Donations and Updating Progress Bars**

- **Client periodically fetches donation data:**

  ```
  setInterval(fetchAndProcessDonations, 60000)
  ```

  - **Client GET --> https://www.sir-nibiru.com/fetch_donations.php**

  - **Server returns donation data:**

    ```json
    {
      "status": "success",
      "donations": [
        { "action": "...", "donation_amount": "...", "timestamp": "..." },
        ...
      ]
    }
    ```

  - **Client updates progress bars based on data.**

### **8. Fetching Highscores and Other Data**

- **Client fetches highscore data:**

  ```
  Client GET --> https://www.sir-nibiru.com/get_highscore.php?period=monthly
  ```

  - **Server returns:**

    ```json
    {
      "status": "success",
      "highscore": [
        { "username": "...", "total_donations": "..." },
        ...
      ]
    }
    ```

- **Client fetches best friend data:**

  ```
  Client GET --> https://www.sir-nibiru.com/get_best_friend.php
  ```

  - **Server returns:**

    ```json
    {
      "status": "success",
      "username": "...",
      "topDonation": "..."
    }
    ```

- **Client fetches total burned tokens:**

  ```
  Client GET --> https://www.sir-nibiru.com/get_total_burned.php
  ```

  - **Server returns:**

    ```json
    {
      "status": "success",
      "total_donations": "..."
    }
    ```

- **Client fetches last sitters:**

  ```
  Client GET --> https://www.sir-nibiru.com/get_last_sitters.php
  ```

  - **Server returns:**

    ```json
    {
      "status": "success",
      "last_sitters": ["username1", "username2", ...]
    }
    ```

### **Summary of Data Storage and Interactions**

- **Client stores data in `localStorage`:**

  - `walletAddress`
  - `username`
  - `nonce`

- **Client calls Phantom Wallet for:**

  - Connecting the wallet.
  - Signing messages (nonces).
  - Signing transactions (burn actions).

- **Backend PHP endpoints involved:**

  - `/get_nonce.php`: Provides nonce for authentication.
  - `/verify_user.php`: Verifies signed nonce.
  - `/update_username.php`: Updates user's username.
  - `/disconnect_user.php`: Disconnects user and invalidates nonce.
  - `/submit_burn.php`: Processes burn transaction signatures.
  - `/fetch_donations.php`: Provides donation data for progress bars.
  - `/get_highscore.php`: Provides highscore data.
  - `/get_best_friend.php`: Provides top donor information.
  - `/get_total_burned.php`: Provides total tokens burned.
  - `/get_last_sitters.php`: Provides list of recent participants.

### **Note:**

- **All interactions are secured via HTTPS and require appropriate validation and error handling both on the client and server sides.**

- **Burn actions are irreversible, and users are prompted to confirm transactions through their Phantom Wallet, ensuring security and intentional participation.**

---

This flow outlines the entire process from the user's initial connection to performing actions that involve burning tokens, including all client-side data storage and interactions with both the Phantom Wallet and the backend server at `sir-nibiru.com`.