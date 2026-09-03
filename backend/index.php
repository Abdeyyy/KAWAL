<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAWAL - Klarifikasi Wacana & Berita Lokal</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <header>
        <div class="logo">
            <i class="fa-solid fa-shield-halved" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
            KAWAL<span>.</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" style="color: var(--primary);">Simulator</a></li>
            <li><a href="login.php">Admin Login</a></li>
        </ul>
    </header>

    <main class="hero-container">
        <!-- Left Side: Copywriting -->
        <div class="hero-text">
            <h1>Menjaga Keharmonisan Keluarga dari <span>Hoaks</span></h1>
            <p>
                KAWAL (Klarifikasi Wacana & Berita Lokal) membantu anak muda meluruskan hoaks yang beredar di grup WhatsApp keluarga secara cerdas, sopan, dan tanpa memicu gesekan sosial dibantu teknologi Kecerdasan Buatan (AI).
            </p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="#simulator" class="glow-btn">
                    <i class="fa-solid fa-play"></i> Uji Coba Simulator
                </a>
                <a href="login.php" class="glow-btn" style="background: rgba(255,255,255,0.05); border: 1px solid var(--card-border); box-shadow: none;">
                    <i class="fa-solid fa-chart-line"></i> Dashboard Admin
                </a>
            </div>
        </div>

        <!-- Right Side: Live Simulator Mockup -->
        <div class="simulator-wrapper" id="simulator">
            <div class="phone-mockup">
                <!-- WhatsApp Top Header -->
                <div class="wa-header">
                    <div class="wa-avatar">
                        <i class="fa-solid fa-robot" style="color: var(--wa-header);"></i>
                    </div>
                    <div class="wa-status-info">
                        <div class="wa-name">KAWAL Asisten Bot</div>
                        <div class="wa-status" id="bot-status">online</div>
                    </div>
                    <div>
                        <i class="fa-solid fa-video" style="margin-right: 12px; cursor: pointer; opacity: 0.8;"></i>
                        <i class="fa-solid fa-phone" style="margin-right: 12px; cursor: pointer; opacity: 0.8;"></i>
                        <i class="fa-solid fa-ellipsis-vertical" style="cursor: pointer; opacity: 0.8;"></i>
                    </div>
                </div>

                <!-- Chat Body -->
                <div class="wa-body" id="chat-body">
                    <!-- Default Bot greeting -->
                    <div class="bubble received">
                        Halo! Saya KAWAL, asisten pelindung keluarga dari hoaks. 🛡️😊
                        <br><br>
                        Kirimkan kabar burung, isu hangat, atau tautan mencurigakan yang beredar di grup keluarga Om/Tante/Bapak/Ibu ke sini. Saya akan bantu memverifikasinya dengan bahasa yang santun dan teduh!
                        <span class="bubble-time" id="chat-time-init">14:00</span>
                    </div>

                    <!-- Dynamic bubbles will be appended here -->

                    <!-- Typing Indicator -->
                    <div class="typing-bubble" id="typing-indicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <!-- Footer / Message Input -->
                <div class="wa-footer">
                    <div class="wa-input-container">
                        <input type="text" class="wa-input" id="chat-input" placeholder="Ketik isu/hoaks di sini..." autocomplete="off">
                    </div>
                    <button class="wa-send-btn" id="send-button">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Preset queries container below hero -->
    <div style="max-width: 1400px; margin: 0 auto 80px auto; padding: 0 8%; text-align: center;">
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 12px;">Atau langsung klik contoh klaim/hoaks di bawah ini untuk menguji:</p>
        <div class="simulator-presets">
            <button class="preset-chip" data-text="Pemerintah membagikan kuota internet gratis 100GB untuk seluruh warga selama masa pandemi dengan mengklik link http://kuota-gratis.pemerintah-indonesia.xyz sekarang!">
                🚨 Link Kuota Gratis 100GB
            </button>
            <button class="preset-chip" data-text="Menteri Kesehatan merilis surat edaran resmi bahwa penularan flu varian baru meningkat lewat botol minum isi ulang di sekolah-sekolah dasar. Mohon waspada bapak ibu.">
                ⚠️ Flu Varian Baru & Air Isi Ulang
            </button>
            <button class="preset-chip" data-text="Kementerian Sosial membagikan bantuan sosial tunai tambahan Rp 1.500.000 bagi keluarga yang mendaftar menggunakan NIK di website Kemensos-Bansos-2026.com">
                💰 Bansos Kemensos Palsu
            </button>
            <button class="preset-chip" data-text="Situs resmi Kemkominfo mengumumkan pelaksanaan program literasi digital nasional untuk mencegah penyebaran berita bohong menjelang Pemilu.">
                ✅ Berita Resmi Kemkominfo
            </button>
        </div>
    </div>

    <script>
        // Set dynamic time for default message
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        document.getElementById('chat-time-init').textContent = timeString;

        const chatBody = document.getElementById('chat-body');
        const chatInput = document.getElementById('chat-input');
        const sendButton = document.getElementById('send-button');
        const botStatus = document.getElementById('bot-status');
        const typingIndicator = document.getElementById('typing-indicator');

        // Function to append message bubble to UI
        function appendMessage(text, type) {
            const time = new Date();
            const timeStr = time.getHours().toString().padStart(2, '0') + ':' + time.getMinutes().toString().padStart(2, '0');
            
            const bubble = document.createElement('div');
            bubble.className = `bubble ${type}`;
            
            // Format newlines as HTML line breaks for bubble text
            const formattedText = text.replace(/\n/g, '<br>');
            
            // Highlight bold formatting used by Gemini (*bold* -> <strong>bold</strong>)
            let parsedText = formattedText.replace(/\*(.*?)\*/g, '<strong>$1</strong>');
            
            bubble.innerHTML = `${parsedText}<span class="bubble-time">${timeStr}</span>`;
            
            // Insert before typing indicator
            chatBody.insertBefore(bubble, typingIndicator);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Main function to send request to backend api.php
        async function checkHoax(text) {
            if (!text.trim()) return;

            // Clear input
            chatInput.value = '';

            // Add user message
            appendMessage(text, 'sent');

            // Show typing indicator & set status to typing
            typingIndicator.style.display = 'block';
            botStatus.textContent = 'mengetik...';
            chatBody.scrollTop = chatBody.scrollHeight;

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        text_input: text,
                        whatsapp_number: 'simulator_user'
                    })
                });

                const result = await response.json();
                
                // Add artificial delay for organic typing feel (1.5 seconds)
                setTimeout(() => {
                    typingIndicator.style.display = 'none';
                    botStatus.textContent = 'online';
                    
                    if (result.success) {
                        appendMessage(result.explanation, 'received');
                    } else {
                        appendMessage('Maaf Om/Tante/Bapak/Ibu, sistem Kawal sedang mengalami gangguan teknis sejenak. Silakan coba kembali nanti ya. 🙏', 'received');
                    }
                }, 1500);

            } catch (error) {
                console.error('Error fetching API:', error);
                setTimeout(() => {
                    typingIndicator.style.display = 'none';
                    botStatus.textContent = 'online';
                    appendMessage('Maaf, koneksi ke server KAWAL terputus. Pastikan server PHP dan database sudah berjalan dengan baik ya. 🔌', 'received');
                }, 1500);
            }
        }

        // Trigger on Send button click
        sendButton.addEventListener('click', () => {
            const text = chatInput.value;
            checkHoax(text);
        });

        // Trigger on Enter key in input
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const text = chatInput.value;
                checkHoax(text);
            }
        });

        // Trigger preset chips
        document.querySelectorAll('.preset-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const presetText = chip.getAttribute('data-text');
                checkHoax(presetText);
            });
        });
    </script>

</body>
</html>
