# Human Resource Analytics Dashboard

Project mata kuliah **Pemrograman Web Lanjut** untuk menganalisis risiko attrition karyawan dengan Laravel 13, MySQL, Chart.js, scheduler sync, dan fallback CSV.

**Universitas Dian Nusantara**  
**Kelompok 3 - Human Resource Analytics**

| Nama                  | NIM       |
| --------------------- | --------- |
| Septian Dwi Saputra   | 411232056 |
| Tiara Adisa Marcianda | 411232040 |
| Izatul Janah          | 411232019 |

# 📊 Human Resource Analytics Dashboard

## 📝 Deskripsi Aplikasi

**Human Resource Analytics Dashboard** adalah aplikasi berbasis web yang dirancang untuk membantu tim Human Resource (HR) dalam menganalisis risiko **attrition (turnover) karyawan**. Aplikasi ini mengintegrasikan data dari berbagai sumber, seperti **CSV, JSON API, Google Sheets, dan MySQL eksternal**, kemudian menyajikannya dalam bentuk dashboard interaktif yang mudah dipahami.

Dashboard menampilkan berbagai **Key Performance Indicator (KPI)**, visualisasi data, tren, insight, serta rekomendasi otomatis berbasis aturan (rule-based) untuk membantu manajemen dalam mengambil keputusan terkait pengelolaan sumber daya manusia.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| **Framework** | Laravel 13 |
| **Bahasa Pemrograman** | PHP 8.3+, JavaScript, SQL |
| **Frontend** | Blade Template, Bootstrap 5.3, CSS |
| **Build Tool** | Vite 7 |
| **Visualisasi Data** | Chart.js 4.5 |
| **Database** | MySQL |
| **AI Recommendation** | Rule-Based Engine (If-Else) |

---

## 🤖 Sistem Rekomendasi

Aplikasi menggunakan **Rule-Based Recommendation Engine**, bukan model AI generatif seperti OpenAI.

Rekomendasi dihasilkan berdasarkan logika **if-else** yang terdapat pada method:

```php
HrDashboardService::recommendation()
```

Logika rekomendasi memanfaatkan beberapa parameter, yaitu:

- Attrition Risk Level
- Job Satisfaction
- Monthly Work Hours
- Work Life Balance
- Monthly Income
- Projects Count

Berdasarkan kombinasi nilai tersebut, sistem akan memberikan rekomendasi tindakan yang dapat membantu HR dalam mengurangi risiko turnover karyawan.

---

## 🗄️ Struktur Database

Database menggunakan **MySQL** dengan nama database:

```text
hr_analytics
```

Tabel utama yang digunakan antara lain:

- `employees`
- `hr_sync_logs`
- `analytics_daily_data`
- `hr_data_sources`

---

## 🔄 Alur Kerja Aplikasi

```text
Data Source
(CSV URL / JSON API / Google Sheets / MySQL Eksternal / Local CSV)
                    │
                    ▼
        HrDataSyncService
 (Sinkronisasi data setiap 15 menit)
                    │
                    ▼
             MySQL Database
(employees, hr_sync_logs, analytics_daily_data, dll.)
                    │
                    ▼
             Service Layer
     ├── HrDashboardService
     │     • KPI Dashboard
     │     • Overview
     │     • Charts
     │     • Insights
     │     • Recommendation
     │
     └── AnalyticsDataService
           • Summary
           • Trend Analysis
           • Department Risk
                    │
                    ▼
           Controller Layer
 ├── HrAnalyticsController
 ├── AnalyticsController
 └── HrPageController
                    │
                    ▼
              View Layer
      (Blade + Bootstrap + Chart.js)
                    │
                    ▼
        Dashboard Interaktif HR Analytics
```

---

## ✨ Fitur Utama

- Dashboard interaktif dengan visualisasi data.
- Monitoring Key Performance Indicator (KPI).
- Analisis risiko attrition karyawan.
- Insight otomatis berdasarkan data HR.
- Rekomendasi berbasis Rule-Based Engine.
- Integrasi berbagai sumber data (CSV, JSON API, Google Sheets, MySQL).
- Sinkronisasi data otomatis setiap 15 menit.
- Visualisasi data menggunakan Chart.js.
- Analisis tren dan performa departemen.

Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run dev
php artisan migrate
php artisan db:seed
php artisan hr:sync
php artisan serve
```
<img width="1434" height="780" alt="Screenshot 2026-07-10 at 19 34 54" src="https://github.com/user-attachments/assets/f76b9239-0f22-4b17-8132-cf375446a380" />

<img width="1433" height="784" alt="Screenshot 2026-07-10 at 19 21 34" src="https://github.com/user-attachments/assets/f0ea651a-d38b-4917-b778-6b37a6b2a35f" />

