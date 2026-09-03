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
- 📊 **Hasil Ringkas & Jelas:** Memberikan status verifikasi (misal: 🔴 **Hoaks**, 🟢 **Fakta**, atau 🟡 **Perlu Verifikasi**) beserta poin alasannya.
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
