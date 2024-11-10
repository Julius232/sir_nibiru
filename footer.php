<footer class="footer">
    <!-- Impressum Section -->
    <div class="impressum-container">
        <link rel="stylesheet" href="styles/footer.css">
        <link rel="stylesheet" href="styles/styles.css">

        <p id="copyright"></p>

        <div class="da-imp-links">
            <span class="datenschutz-link" onclick="openDatenschutz()">Datenschutz</span> |
            <span class="impressum-link" onclick="toggleImpressum()">Impressum</span>
        </div>

        <!-- Impressum Modal -->
        <div id="impressum-content" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="toggleImpressum()">&times;</span>

                <p><strong>Website Developer: </strong>Julius Szemelliker</p>
                <p><strong>Contact:</strong></p>
                <p>Julius Szemelliker</p>
                <p>Hainfelder Straße 19, 3040 Neulengbach, Österreich</p>
                <p>Tel.: +436765945246</p>
                <p>E-Mail: <a href="mailto:sirnibiru@gmail.com">sirnibiru@gmail.com</a></p>

                <p><strong>Disclaimer:</strong><br>
                    I am a private individual in Austria and run this website as a fan page for my dog. This website
                    does not provide any investment advice for meme tokens or cryptocurrencies. All information
                    shared here is for entertainment purposes only.
                </p>

                <p><strong>Investment Risk Notice:</strong><br>
                    Investments in cryptocurrencies, meme tokens, or other financial assets are inherently risky.
                    Please conduct your own research and consult a qualified financial advisor before making any
                    investment
                    decisions. All participation is at your own risk; I assume no responsibility for any losses or
                    outcomes from using information on this site.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Dynamically set the current year
        document.addEventListener('DOMContentLoaded', function () {
            const currentYear = new Date().getFullYear();
            document.getElementById("copyright").innerHTML = `Copyright © ${currentYear} Sir-Nibiru.com. All rights reserved.`;
        });
        function toggleImpressum() {
            const impressumContent = document.getElementById('impressum-content');
            if (impressumContent.style.display === 'none' || impressumContent.style.display === '') {
                impressumContent.style.display = 'block';
            } else {
                impressumContent.style.display = 'none';
            }
        }

        function openDatenschutz() {
            window.open("https://itrk.legal/V23.8U.P2v-iframe.html", "_blank");
        }

        document.addEventListener('click', function (event) {
            const impressumContent = document.getElementById('impressum-content');
            const isClickInside = impressumContent.contains(event.target) || event.target.classList.contains('impressum-link');
            if (!isClickInside) {
                impressumContent.style.display = 'none';
            }
        });
    </script>
</footer>