# Solar Panel Calculation API 🌞⚡

API untuk menghitung kapasitas maksimal daya listrik yang dihasilkan oleh panel surya berdasarkan luas lahan, lokasi geografis, dan data radiasi matahari dari NASA.

## 📋 Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [API Documentation](#api-documentation)
- [Struktur Database](#struktur-database)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

---

## 🚀 Fitur

- ✅ **CRUD Lengkap** untuk kalkulasi panel surya
- 🌍 **Geocoding Otomatis** menggunakan Nominatim API (OpenStreetMap)
- ☀️ **Data Radiasi Matahari** dari NASA POWER API
- 📊 **Perhitungan Komprehensif**:
  - Kapasitas maksimal daya (kW)
  - Produksi energi harian, bulanan, dan tahunan
  - Estimasi biaya instalasi
  - ROI (Return on Investment)
  - Payback period
- 🔒 **Validasi Input** yang ketat
- 📝 **Response API** yang konsisten dan informatif
- 🌐 **Tanpa API Key** - menggunakan API gratis

---

## 🛠 Teknologi

- **Backend Framework**: Laravel 10.x
- **PHP Version**: 8.1 atau lebih tinggi
- **Database**: MySQL 8.0 / MariaDB 10.3+
- **HTTP Client**: Guzzle HTTP
- **External APIs**:
  - [Nominatim API](https://nominatim.org/) - Geocoding
  - [NASA POWER API](https://power.larc.nasa.gov/) - Solar Irradiance Data

---

## 💻 Persyaratan Sistem

Pastikan sistem Anda memiliki:

- **PHP** >= 8.1
- **Composer** >= 2.0
- **MySQL** >= 8.0 atau **MariaDB** >= 10.3
- **Extensions PHP**:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - Curl

---

## 📦 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/solar-panel-api.git
cd solar-panel-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Setup Database

Buat database baru di MySQL/MariaDB:

```sql
CREATE DATABASE solar_panel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Konfigurasi Environment

Edit file `.env` dengan konfigurasi Anda:

```env
APP_NAME="Solar Panel API"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solar_panel_db
DB_USERNAME=root
DB_PASSWORD=your_password

# API Configuration (Opsional - sudah menggunakan API gratis)
# NASA_API_KEY=DEMO_KEY
# OPENCAGE_API_KEY=your_key_here
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. (Opsional) Seed Database dengan Data Dummy

Jika Anda membuat seeder:

```bash
php artisan db:seed
```

---

## ⚙️ Konfigurasi

### API External

#### 1. Nominatim API (OpenStreetMap)
- **URL**: https://nominatim.openstreetmap.org
- **Rate Limit**: 1 request/second
- **Gratis**: Ya, tidak perlu API key
- **Dokumentasi**: https://nominatim.org/release-docs/latest/api/Overview/

#### 2. NASA POWER API
- **URL**: https://power.larc.nasa.gov/api
- **Rate Limit**: Tidak ada
- **Gratis**: Ya, tidak perlu API key
- **Dokumentasi**: https://power.larc.nasa.gov/docs/

### Cache Configuration (Opsional)

Untuk meningkatkan performa, Anda bisa mengaktifkan cache:

```bash
php artisan config:cache
php artisan route:cache
```

---

## 🚀 Menjalankan Aplikasi

### Development Server

```bash
php artisan serve
```

Server akan berjalan di: **http://localhost:8000**

### Menggunakan Port Lain

```bash
php artisan serve --port=8080
```

### Menjalankan dengan Host Tertentu

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api/powerestimation
```

### Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/solar-calculations` | Mendapatkan semua kalkulasi (paginated) |
| GET | `/solar-calculations/{id}` | Mendapatkan detail kalkulasi |
| POST | `/solar-calculations` | Membuat kalkulasi baru |
| PUT/PATCH | `/solar-calculations/{id}` | Update kalkulasi |
| DELETE | `/solar-calculations/{id}` | Hapus kalkulasi |

### 1. CREATE - Membuat Kalkulasi Baru

**Endpoint**: `POST /api/powerestimation/solar-calculations`

**Request Headers**:
```
Content-Type: application/json
Accept: application/json
```

**Request Body**:
```json
{
    "address": "Jl. Sudirman No. 1, Jakarta Pusat",
    "land_area": 100,
    "latitude": -6.2088,
    "longitude": 106.8456,
    "solar_irradiance": 5.2,
    "panel_efficiency": 20,
    "system_losses": 14
}
```

**Parameter**:

| Field | Type | Required | Default | Deskripsi |
|-------|------|----------|---------|-----------|
| address | string | Ya | - | Alamat lokasi panel surya |
| land_area | numeric | Ya | - | Luas lahan dalam m² |
| latitude | numeric | Tidak | auto | Latitude (-90 to 90) |
| longitude | numeric | Tidak | auto | Longitude (-180 to 180) |
| solar_irradiance | numeric | Tidak | auto | Radiasi matahari (kWh/m²/day) |
| panel_efficiency | numeric | Tidak | 20 | Efisiensi panel (1-100%) |
| system_losses | numeric | Tidak | 14 | System losses (0-100%) |

**Response Success (201)**:
```json
{
    "success": true,
    "message": "Kalkulasi berhasil dibuat",
    "data": {
        "calculation": {
            "id": 1,
            "address": "Jl. Sudirman No. 1, Jakarta Pusat",
            "latitude": -6.2088,
            "longitude": 106.8456,
            "land_area": 100,
            "solar_irradiance": 5.2,
            "panel_efficiency": 20,
            "system_losses": 14,
            "max_power_capacity": 15.00,
            "daily_energy_production": 66.90,
            "monthly_energy_production": 2007.00,
            "yearly_energy_production": 24421.50,
            "created_at": "2024-12-09T10:30:00.000000Z",
            "updated_at": "2024-12-09T10:30:00.000000Z"
        },
        "details": {
            "usable_area": 75.00,
            "max_power_capacity": 15.00,
            "daily_energy_production": 66.90,
            "monthly_energy_production": 2007.00,
            "yearly_energy_production": 24421.50,
            "panel_efficiency": 20,
            "system_losses": 14,
            "performance_ratio": 86.00
        },
        "financial_metrics": {
            "installation_cost": 225000000.00,
            "yearly_savings": 35284751.55,
            "payback_period_years": 6.38,
            "roi_25_years": 291.49
        }
    }
}
```

### 2. READ ALL - Mendapatkan Semua Data

**Endpoint**: `GET /api/powerestimation/solar-calculations`

**Query Parameters** (opsional):
- `page`: Nomor halaman (default: 1)
- `per_page`: Jumlah data per halaman (default: 10)

**Example**:
```
GET /api/powerestimation/solar-calculations?page=1&per_page=20
```

### 3. READ ONE - Mendapatkan Data Spesifik

**Endpoint**: `GET /api/powerestimation/solar-calculations/{id}`

**Example**:
```
GET /api/powerestimation/solar-calculations/1
```

### 4. UPDATE - Update Data

**Endpoint**: `PUT /api/powerestimation/solar-calculations/{id}`

**Request Body** (semua field opsional):
```json
{
    "land_area": 120,
    "panel_efficiency": 22
}
```

### 5. DELETE - Hapus Data

**Endpoint**: `DELETE /api/powerestimation/solar-calculations/{id}`

**Example**:
```
DELETE /api/powerestimation/solar-calculations/1
```

### Error Responses

**Validation Error (422)**:
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "land_area": [
            "The land area field is required."
        ]
    }
}
```

**Not Found (404)**:
```json
{
    "success": false,
    "message": "Data tidak ditemukan"
}
```

**Server Error (500)**:
```json
{
    "success": false,
    "message": "Terjadi kesalahan pada server"
}
```

---

## 🗄 Struktur Database

### Table: `solar_calculations`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT UNSIGNED | Primary key |
| address | VARCHAR(255) | Alamat lokasi |
| latitude | DECIMAL(10,8) | Koordinat latitude |
| longitude | DECIMAL(11,8) | Koordinat longitude |
| land_area | DECIMAL(10,2) | Luas lahan (m²) |
| solar_irradiance | DECIMAL(10,2) | Radiasi matahari (kWh/m²/day) |
| panel_efficiency | DECIMAL(5,2) | Efisiensi panel (%) |
| system_losses | DECIMAL(5,2) | System losses (%) |
| max_power_capacity | DECIMAL(10,2) | Kapasitas maksimal (kW) |
| daily_energy_production | DECIMAL(10,2) | Produksi harian (kWh) |
| monthly_energy_production | DECIMAL(10,2) | Produksi bulanan (kWh) |
| yearly_energy_production | DECIMAL(10,2) | Produksi tahunan (kWh) |
| nasa_data | JSON | Data mentah dari NASA API |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

## 🧪 Testing

### Manual Testing dengan cURL

**Create:**
```bash
curl -X POST http://localhost:8000/api/powerestimation/solar-calculations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "address": "Jakarta, Indonesia",
    "land_area": 100
  }'
```

**Read All:**
```bash
curl -X GET http://localhost:8000/api/powerestimation/solar-calculations \
  -H "Accept: application/json"
```

**Read One:**
```bash
curl -X GET http://localhost:8000/api/powerestimation/solar-calculations/1 \
  -H "Accept: application/json"
```

**Update:**
```bash
curl -X PUT http://localhost:8000/api/powerestimation/solar-calculations/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "land_area": 120
  }'
```

**Delete:**
```bash
curl -X DELETE http://localhost:8000/api/powerestimation/solar-calculations/1 \
  -H "Accept: application/json"
```

### Testing dengan Postman

1. Import collection dari file `postman_collection.json` (jika tersedia)
2. Set environment variable:
   - `base_url`: http://localhost:8000/api/powerestimation
3. Jalankan request sesuai kebutuhan

### Unit Testing (Opsional)

Jika Anda membuat unit test:

```bash
php artisan test
```

atau dengan coverage:

```bash
php artisan test --coverage
```

---

## 🚀 Deployment

### Deployment ke Production

1. **Set Environment ke Production**

Edit `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

2. **Optimize Application**

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Setup Web Server**

**Apache (.htaccess sudah termasuk dalam Laravel)**

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/solar-panel-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

4. **Set Permissions**

```bash
sudo chown -R www-data:www-data /var/www/solar-panel-api
sudo chmod -R 755 /var/www/solar-panel-api
sudo chmod -R 775 /var/www/solar-panel-api/storage
sudo chmod -R 775 /var/www/solar-panel-api/bootstrap/cache
```

### Deployment dengan Docker (Opsional)

Jika Anda menggunakan Docker, buat `Dockerfile`:

```dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

---

## 🔧 Troubleshooting

### Problem: "Connection refused" saat hit API external

**Solution**: 
- Pastikan koneksi internet aktif
- Cek firewall yang mungkin memblokir request keluar
- Verifikasi URL API masih aktif

### Problem: "Too Many Requests" dari Nominatim

**Solution**:
- Nominatim memiliki rate limit 1 request/second
- Gunakan koordinat manual untuk menghindari geocoding
- Implementasi caching untuk alamat yang sering digunakan

### Problem: Migration Failed

**Solution**:
```bash
php artisan migrate:fresh
```

Atau reset database:
```bash
php artisan migrate:reset
php artisan migrate
```

### Problem: Composer Install Error

**Solution**:
```bash
composer clear-cache
composer install --no-cache
```

### Problem: Permission Denied di Storage

**Solution**:
```bash
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
```

---

## 🤝 Kontribusi

Kontribusi selalu diterima! Berikut cara berkontribusi:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Coding Standards

- Follow PSR-12 coding standard
- Write clear commit messages
- Add comments untuk logic yang kompleks
- Update documentation jika diperlukan

---

## 📝 Catatan Penting

### Tentang API Gratis

1. **Nominatim API**:
   - Rate limit: 1 request/second
   - Tidak untuk production heavy-load
   - Pertimbangkan self-hosting Nominatim untuk production

2. **NASA POWER API**:
   - Gratis dan reliable
   - Data updated regularly
   - Cocok untuk production use

### Formula Perhitungan

**Kapasitas Maksimal (kW)**:
```
Kapasitas = Luas Panel × Efisiensi × 1 kW/m²
Luas Panel = Luas Lahan × 75%
```

**Energi Harian (kWh/day)**:
```
Energi = Kapasitas × Radiasi Matahari × Performance Ratio
Performance Ratio = 1 - (System Losses / 100)
```

**Biaya & ROI**:
```
Biaya Instalasi = Kapasitas × Rp 15.000.000/kW
Penghematan Tahunan = Energi Tahunan × Tarif Listrik
Payback Period = Biaya / Penghematan Tahunan
```

---

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👥 Tim Pengembang

- **Rafi Hidayatulloh & Bila** - *Initial work* 


## 🙏 Acknowledgments

- [Laravel Framework](https://laravel.com)
- [NASA POWER Project](https://power.larc.nasa.gov)


---

