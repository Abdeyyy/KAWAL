# KAWAL 🛡️

> **Kawal Informasi, Tangkis Hoaks.**  
> WhatsApp Bot berbasis Google Cloud Platform (GCP) & Gemini API untuk verifikasi dan deteksi dini berita atau informasi hoaks secara cepat.

---

## 📌 Tentang Proyek

**KAWAL** adalah bot WhatsApp interaktif yang dirancang untuk membantu masyarakat mengidentifikasi kebenaran suatu informasi, artikel, atau klaim pesan berantai. Dengan memanfaatkan ekosistem **Google Cloud Platform (GCP)** dan keandalan **Gemini API**, KAWAL menganalisis konteks teks, mengidentifikasi indikator disinformasi, dan memberikan ringkasan verifikasi fakta secara *real-time* di dalam infrastruktur *cloud* yang stabil dan *scalable*.

---

## ✨ Fitur Utama

- 🔍 **Cek Teks & Berita:** Pengguna cukup meneruskan (*forward*) atau menempelkan (*paste*) teks berita ke WhatsApp bot.
- 🤖 **Analisis Berbasis AI:** Menggunakan Gemini API untuk mengevaluasi klaim, logika argumen, dan indikator hoaks.
- ☁️ **Infrastruktur GCP:** Memanfaatkan Cloud Run / Cloud Functions untuk eksekusi *serverless*, Secret Manager untuk keamanan kredensial, dan Cloud Logging untuk pemantauan.
- 📊 **Hasil Ringkas & Jelas:** Memberikan status verifikasi beserta poin alasannya.
- 💬 **Responsif via WhatsApp:** Mudah diakses oleh berbagai kalangan tanpa perlu menginstal aplikasi tambahan.

---

## 🏗️ Arsitektur Sistem

```text
[ User / WhatsApp ]
        │
        ▼
[ WhatsApp API Engine / Webhook ]
        │
        ▼
[ GCP Cloud Run / Functions ] ─── ( Fetch Key ) ───> [ GCP Secret Manager ]
        │
        ▼
[ Google Gemini API / Vertex AI ]
        │
        ▼
[ Format Balasan ] ───> [ WhatsApp User ]
```

---

## 🛠️ Teknologi & Cloud Services

| Kategori | Teknologi / Layanan |
| :--- | :--- |
| **Cloud Platform (GCP)** | Google Cloud Run / Cloud Functions, Secret Manager, Cloud Logging |
| **AI Engine** | Google Gemini API / Vertex AI |
| **Language / Backend** | Node.js |
| **WhatsApp API** | Baileys / WhatsApp Business API / FOWeb Engine |
| **Environment Management** | dotenv |

---

## 💻 Cara Menjalankan Secara Lokal (Local Development)

### 1. Prasyarat
- Node.js versi 18+ terinstall di komputer kamu.
- **Gemini API Key** yang bisa kamu dapatkan secara gratis di [Google AI Studio](https://aistudio.google.com/).
- Nomor WhatsApp aktif yang akan digunakan sebagai bot.

### 2. Kloning Repositori
```bash
git clone [https://github.com/username/KAWAL.git](https://github.com/username/KAWAL.git)
cd KAWAL
```

### 3. Install Dependensi
Jalankan perintah ini di terminal untuk mengunduh semua *package* / library yang dibutuhkan:
```bash
npm install
# atau
npm i
```

### 4. Konfigurasi Environment Variable (`.env`)
1. Buat file baru bernama `.env` di direktori utama (sejajar dengan `package.json`).
2. Masukkan konfigurasi berikut dan isi `GEMINI_API_KEY` dengan API Key milikmu:

```env
GEMINI_API_KEY=AIzaSy_YOUR_GEMINI_API_KEY_HERE
PORT=3000
GCP_PROJECT_ID=your-gcp-project-id
```

### 5. Jalankan Bot
Start server lokal dengan perintah:
```bash
npm start
```

### 6. Hubungkan WhatsApp Bot
1. Setelah dijalankan, terminal akan menampilkan **QR Code**.
2. Buka aplikasi WhatsApp di HP kamu.
3. Masuk ke **Setelan / Settings** ➔ **Perangkat Tertaut (Linked Devices)**.
4. Pindai (*scan*) QR Code yang muncul di terminal.
5. Setelah terhubung, bot KAWAL siap digunakan!

---

## ☁️ Deployment ke Google Cloud Platform (GCP)

Jika ingin men-deploy proyek ini ke cloud menggunakan GCP Cloud Run:

```bash
# Build container image ke Google Cloud Build
gcloud builds submit --tag gcr.io/YOUR_PROJECT_ID/kawal-bot

# Deploy container ke Cloud Run
gcloud run deploy kawal-bot \
  --image gcr.io/YOUR_PROJECT_ID/kawal-bot \
  --platform managed \
  --region asia-southeast1 \
  --allow-unauthenticated \
  --set-env-vars GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE
```

---

## 📝 Contoh Penggunaan

1. **Kirim Pesan (User):**
   > *"Apakah benar pemerintah akan membagikan kuota internet gratis 100GB besok? Buka link berikut..."*

2. **Respon Bot (KAWAL):**
*ℹ️ INFORMASI KAWAL*

Halo Om/Tante/Bapak/Ibu... 😊

Kawal sudah membaca informasinya. Saat ini status info tersebut masih MERAGUKAN karena belum ada konfirmasi resmi dari pihak berwenang.

Untuk sementara, lebih baik kita tidak terburu-buru menyebarkannya ya Om/Tante, agar tidak menimbulkan kepanikan di grup. Kawal akan terus memantau info ini. Tetap teduh dan jaga kesehatan! 🙏✨

---

## 🤝 Kontribusi

Kontribusi selalu terbuka! Jika ingin memperbaiki bug atau menambahkan fitur baru:
1. *Fork* repositori ini
2. Buat *feature branch* (`git checkout -b fitur-baru`)
3. *Commit* perubahan kamu (`git commit -m 'Menambahkan fitur integrasi baru'`)
4. *Push* ke *branch* (`git push origin fitur-baru`)
5. Buka **Pull Request**
