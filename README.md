# Human Resource Analytics Dashboard

Project mata kuliah **Pemrograman Web Lanjut** untuk menganalisis risiko attrition karyawan dengan Laravel 13, MySQL, Chart.js, scheduler sync, dan fallback CSV.

**Universitas Dian Nusantara**  
**Kelompok 3 - Human Resource Analytics**

| Nama                  | NIM       |
| --------------------- | --------- |
| Septian Dwi Saputra   | 411232056 |
| Tiara Adisa Marcianda | 411232040 |
| Izatul Janah          | 411232019 |

Penjelasan

Aplikasi **Human Resource Analytics Dashboard** adalah sistem analisis risiko _attrition_ (turnover) karyawan yang mengintegrasikan data dari berbagai sumber (CSV, JSON API, Google Sheets, MySQL eksternal) ke dalam satu dashboard interaktif. Sistem menyajikan visualisasi KPI, tren, serta rekomendasi berbasis aturan untuk membantu HR dalam pengambilan keputusan.

Stack / Teknis

| Komponen | Teknologi |
| Bahasa Pemrograman | PHP 8.3+ (Laravel 13), JavaScript (Vite 7, Chart.js 4.5, Bootstrap 5.3), Blade, CSS, SQL |
| AI Recommendation | _Rule-based engine_ (bukan OpenAI) — rekomendasi ditentukan oleh logika if-else di `HrDashboardService::recommendation()` berdasarkan field `attrition_risk_level`, `job_satisfaction`, `monthly_work_hours`, `work_life_balance`, `monthly_income`, dan `projects_count` |
| Database | MySQL (`hr_analytics`) dengan tabel utama `employees`, `hr_sync_logs`, `analytics_daily_data`, dan `hr_data_sources` |

Flow Aplikasi

Data Source (CSV URL / JSON API / Google Sheets / MySQL Eksternal / Local CSV)
│
▼
HrDataSyncService (orchestrator sync, dijadwalkan tiap 15 menit)
│
▼
MySQL Database (employees, hr_sync_logs, analytics_daily_data, dll.)
│
▼
Service Layer
├─ HrDashboardService → overview, KPI, charts, rekomendasi, insights
└─ AnalyticsDataService → summary, trend, risiko departemen
│
▼
Controller Layer (HrAnalyticsController, AnalyticsController, HrPageController)
│
▼
View Layer (Blade + Chart.js + Bootstrap) → Dashboard interaktif

Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate
php artisan db:seed
php artisan hr:sync
php artisan serve
npm run dev
```
<img width="1436" height="819" alt="Screenshot 2026-07-10 at 18 57 20" src="https://github.com/user-attachments/assets/d02890bb-2d4a-4cba-8296-00e1a79c2cdc" />
<img width="1433" height="784" alt="Screenshot 2026-07-10 at 19 21 34" src="https://github.com/user-attachments/assets/f0ea651a-d38b-4917-b778-6b37a6b2a35f" />

