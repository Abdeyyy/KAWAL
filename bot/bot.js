const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
require('dotenv').config();

// PHP API endpoint. When PHP runs in Docker mapped to port 8080,
// this local Node.js process accesses it via localhost:8080.
const API_URL = process.env.API_URL || 'http://localhost:8080/api.php';

console.log('==================================================');
console.log('      KAWAL (Klarifikasi Wacana & Berita Lokal)   ');
console.log('==================================================');
console.log(`PHP API Endpoint: ${API_URL}`);
console.log('Menghubungkan ke WhatsApp Web, mohon tunggu...\n');

// Initialize WhatsApp client with local session persistence
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './.wwebjs_auth'
    }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox', 
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--single-process', // helps save memory
            '--disable-gpu'
        ]
    }
});

// Generate QR code for terminal scan
client.on('qr', (qr) => {
    console.log('==================================================');
    console.log('👉 SCAN QR CODE DI BAWAH DENGAN APLIKASI WHATSAPP');
    console.log('==================================================');
    qrcode.generate(qr, { small: true });
    console.log('==================================================\n');
});

// Client logged in and ready
client.on('ready', () => {
    console.log('\n==================================================');
    console.log('✅ BOT KAWAL AKTIF & SIAP MENERIMA PESAN!         ');
    console.log('==================================================\n');
});

// Handle incoming messages
client.on('message', async (msg) => {
    // Ignore messages sent by the bot itself
    if (msg.fromMe) return;

    // Ignore empty messages
    if (!msg.body || msg.body.trim() === '') return;

    // Retrieve sender identifier (phone number)
    const senderNumber = msg.from.split('@')[0];

    // Log message
    console.log(`[PESAN MASUK] Pengirim: ${senderNumber} | Teks: "${msg.body.substring(0, 60)}${msg.body.length > 60 ? '...' : ''}"`);

    // Set "typing..." status in WhatsApp chat
    try {
        const chat = await msg.getChat();
        await chat.sendStateTyping();
    } catch (err) {
        // Silently skip typing indicator if not supported or error
    }

    try {
        // Forward wacana text to PHP API via Axios
        const response = await axios.post(API_URL, {
            text_input: msg.body,
            whatsapp_number: senderNumber
        }, {
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data && response.data.success) {
            // Send back the polite AI clarification response
            await msg.reply(response.data.explanation);
            console.log(`[BALASAN DIKIRIM] Penerima: ${senderNumber} | Status: ${response.data.status.toUpperCase()}`);
        } else {
            console.log(`[API ERROR] Format respons API tidak valid.`);
            await msg.reply('Maaf Om/Tante/Bapak/Ibu, sistem KAWAL sedang mengalami gangguan teknis sejenak. Silakan coba kembali nanti ya. 🙏');
        }
    } catch (error) {
        console.error(`[KONEKSI ERROR] Gagal menghubungi API di ${API_URL}:`, error.message);
        await msg.reply('Maaf Om/Tante/Bapak/Ibu, KAWAL tidak bisa menghubungi server verifikasi saat ini. Silakan hubungi admin. 🔌');
    }
});

// Start bot client
client.initialize();
