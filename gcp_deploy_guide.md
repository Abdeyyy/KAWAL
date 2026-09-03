# 🚀 Panduan Deploy KAWAL ke Google Cloud Platform (GCP)

Panduan ini menjelaskan cara memindahkan aplikasi KAWAL (Backend PHP + Database MySQL + WhatsApp Bot Node.js) ke server **Google Compute Engine (GCE) VM Instance** menggunakan Docker Compose dan PM2.

---

## 📋 Prasyarat
1. Akun Google Cloud Platform (GCP) yang aktif.
2. Repositori Git proyek KAWAL (atau upload folder KAWAL via SSH).

---

## 1. Membuat VM Instance di GCP

1. Masuk ke **Google Cloud Console** ([console.cloud.google.com](https://console.cloud.google.com/)).
2. Buka menu navigasi kiri lalu pilih **Compute Engine** ➡️ **VM Instances**.
3. Klik **Create Instance**.
4. Atur konfigurasi berikut:
   * **Name**: `kawal-server`
   * **Region & Zone**: Pilih terdekat (misal: `asia-southeast2` Jakarta).
   * **Machine configuration**: General-purpose.
   * **Machine type**: `e2-medium` (2 vCPUs, 4 GB RAM). 
     *(PENTING: Jangan gunakan e2-micro karena Node.js WhatsApp Web Puppeteer membutuhkan minimal 2GB RAM untuk memproses Chromium).*
   * **Boot disk**: Klik Change ➡️ Pilih **Ubuntu** (versi `Ubuntu 22.04 LTS`) dengan ukuran disk minimal **20 GB**.
   * **Firewall**: Centang **Allow HTTP traffic** dan **Allow HTTPS traffic**.
5. Klik **Create** dan tunggu hingga VM aktif dan mendapatkan **External IP** (IP Publik).

---

## 2. Mengatur Firewall Rules GCP (Port 80 & 443)

Secura default, port HTTP (80) telah dibuka saat mencentang "Allow HTTP traffic". Namun, pastikan kembali port tersebut terbuka untuk umum:
1. Cari **VPC network** ➡️ **Firewall** di kolom pencarian GCP.
2. Pastikan terdapat aturan firewall bernama `default-allow-http` dengan target `http-server` pada port `tcp:80` untuk IP source `0.0.0.0/0`.

---

## 3. Instalasi Server (Docker, Node.js, & PM2)

Hubungkan ke VM Anda menggunakan tombol **SSH** di baris VM Instance Anda. Setelah terminal SSH terbuka, jalankan perintah di bawah ini secara berurutan untuk menginstal alat yang dibutuhkan:

```bash
# Update system package
sudo apt-get update && sudo apt-get upgrade -y

# 1. Instalasi Docker & Docker Compose
sudo apt-get install -y docker.io docker-compose-v2
sudo systemctl start docker
sudo systemctl enable docker

# Berikan izin user saat ini agar bisa menjalankan docker tanpa sudo
sudo usermod -aG docker $USER
# Terapkan perubahan grup (atau alternatifnya: logout dan login SSH kembali)
newgrp docker

# 2. Instalasi Node.js (v20 LTS) & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# 3. Instalasi PM2 (Process Manager untuk menjalankan WhatsApp Bot 24/7)
sudo npm install --global pm2
```

---

## 4. Memindahkan Code & Konfigurasi ke Server

### Langkah A: Clone / Upload Code ke VM
Anda bisa mengunggah folder proyek KAWAL melalui menu **Upload File** di pojok kanan atas jendela terminal SSH Google Cloud, atau melakukan `git clone` jika menggunakan repositori Git.
Pastikan struktur folder di server berada di direktori home Anda, misalnya: `~/KAWAL/`.

### Langkah B: Konfigurasi Environment di Server
Masuk ke folder proyek KAWAL di VM:
```bash
cd ~/KAWAL
```

1. **Konfigurasi Gemini API Key di Server:**
   Edit file `.env` di root proyek:
   ```bash
   nano .env
   ```
   Masukkan API Key Gemini Anda:
   ```env
   GEMINI_API_KEY=AIzaSy... (API Key Anda)
   ```
   Tekan `CTRL + O`, `Enter` untuk menyimpan, lalu `CTRL + X` untuk keluar.

2. **Konfigurasi Bot API Endpoint:**
   Masuk ke folder `bot`:
   ```bash
   cd bot
   nano .env
   ```
   Karena bot berjalan di mesin yang sama dengan Docker PHP, ganti API URL menggunakan localhost port 80 (produksi):
   ```env
   API_URL=http://localhost/api.php
   ```
   Simpan dan keluar.

---

## 5. Menjalankan Backend PHP & Database MySQL (Docker)

Kembali ke root folder `KAWAL/` lalu jalankan Docker Compose dengan file override produksi:
```bash
cd ~/KAWAL
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Verifikasi kontainer sudah berjalan dengan:
```bash
docker ps
```
Anda akan melihat kontainer `kawal-web` (port 80) dan `kawal-db` (port 3306) berstatus **Up**.

---

## 6. Menjalankan WhatsApp Bot dengan PM2

Aplikasi WhatsApp bot memerlukan Puppeteer. Kita harus menginstal dependensi library grafis (Chromium) yang dibutuhkan oleh Ubuntu agar Puppeteer bisa berjalan lancar:

```bash
# Instalasi library Chrome Headless yang dibutuhkan Puppeteer di Ubuntu
sudo apt-get install -y libxss1 libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxtst6 ca-certificates fonts-liberation libnss3 lsb-release xdg-utils wget
```

Masuk ke folder `bot/` dan jalankan instalasi Node:
```bash
cd ~/KAWAL/bot
npm install
```

### Memulai Bot & Scan QR Code Pertama Kali
Jalankan bot menggunakan perintah Node biasa agar QR Code tercetak di terminal SSH Anda untuk discan:
```bash
node bot.js
```
*   Scan QR Code tersebut menggunakan WhatsApp di handphone Anda.
*   Setelah muncul tulisan `✅ BOT KAWAL AKTIF & SIAP MENERIMA PESAN!`, hentikan proses dengan menekan `CTRL + C`.

### Menjalankan Bot di Background menggunakan PM2 (24/7)
Sekarang, jalankan bot secara permanen di latar belakang menggunakan PM2:
```bash
pm2 start bot.js --name "kawal-bot"
```

Untuk memantau status atau log bot kapan saja, gunakan perintah:
```bash
# Melihat daftar aplikasi PM2 yang berjalan
pm2 list

# Melihat log aktivitas bot secara real-time
pm2 logs kawal-bot

# Menghentikan bot
pm2 stop kawal-bot

# Memulai ulang bot
pm2 restart kawal-bot
```

---

## 🔗 Mengakses Aplikasi

Aplikasi KAWAL Anda kini dapat diakses oleh publik (termasuk Juri):
*   **Landing Page & Live Simulator:** Buka browser dan ketik **`http://<IP-PUBLIK-VM-ANDA>`**
*   **Dashboard Admin:** Buka **`http://<IP-PUBLIK-VM-ANDA>/dashboard.php`** (Username: `admin`, Password: `kawal2026`)
*   **WhatsApp Bot:** Bot akan merespons pesan secara otomatis di latar belakang selama VM aktif.
