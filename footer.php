<!-- Impressum Section -->
<div class="impressum-container">
    <link rel="stylesheet" href="styles/footer.css">
    <button class="impressum-toggle" onclick="toggleImpressum()">Impressum</button>
    <button class="datenschutz-toggle" onclick="openDatenschutz()">Datenschutz</button>
    <div id="impressum-content" class="impressum-content">
        <p><strong>Website Developer:</strong> Julius Szemelliker</p>

        <p><strong>Contact:</strong></p>
        <p>Julius Szemelliker</p>
        <p>Hainfelder Straße 19,</p>
        <p>3040 Neulengbach, Österreich</p>
        <p>Tel.: +436765945246</p>
        <p>E-Mail: <a href="mailto:sirnibiru@gmail.com">sirnibiru@gmail.com</a></p>

        <p><strong>Disclaimer:</strong><br>
            I am a private individual in Austria and run this website as a fan page for my dog. This website
            does not provide any investment advice for meme tokens or cryptocurrencies. All information
            shared
            here is for entertainment purposes only.
        </p>

        <p><strong>Investment Risk Notice:</strong><br>
            Investments in cryptocurrencies, meme tokens, or other financial assets are inherently risky.
            Please
            conduct your own research and consult a qualified financial advisor before making any investment
            decisions. All participation is at your own risk; I assume no responsibility for any losses or
            outcomes from using information on this site.
        </p>
    </div>
</div>
<script>
    function toggleImpressum() {
        const impressumContent = document.getElementById('impressum-content');
        if (impressumContent.style.display === 'none' || impressumContent.style.display === '') {
            impressumContent.style.display = 'block';
            // Add event listener for clicks outside of the Impressum
            document.addEventListener('click', closeImpressumOnOutsideClick);
        } else {
            impressumContent.style.display = 'none';
            // Remove the event listener once closed
            document.removeEventListener('click', closeImpressumOnOutsideClick);
        }
    }

    // Function to close Impressum when clicking outside
    function closeImpressumOnOutsideClick(event) {
        const impressumContent = document.getElementById('impressum-content');
        const toggleButton = document.querySelector('.impressum-toggle');

        // Check if the click is outside both the Impressum content and toggle button
        if (!impressumContent.contains(event.target) && !toggleButton.contains(event.target)) {
            impressumContent.style.display = 'none';
            document.removeEventListener('click', closeImpressumOnOutsideClick);
        }
    }

    function openDatenschutz() {
        window.open("https://itrk.legal/V23.8U.P2v-iframe.html", "_blank");
    }
</script>