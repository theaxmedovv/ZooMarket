# Laravel Role Permission System

Bu loyiha Laravel framework yordamida role va permission boshqarish tizimini amalga oshiradi. Spatie Laravel Permission paketi ishlatilgan.

## Texnologiyalar

- **Laravel** 11.x
- **PHP** 8.1+
- **Spatie Laravel Permission** paket
- **Bootstrap 5** (UI uchun)
- **MySQL** (`zoobozor` ma'lumotlar bazasi)

## O'rnatish

1. **Loyihani klonlash:**

    ```bash
    git clone <repository-url>
    cd Role_Permission
    ```

2. **Paketlarni o'rnatish:**

    ```bash
    composer install
    npm install
    ```

3. **Muhit faylini sozlash:**
   `.env` faylini nusxalash va sozlash:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Ma'lumotlar bazasi:**
   - phpMyAdmin yoki MySQL orqali `zoobozor` bazasini tanlang.
   - `.env` faylida:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=zoobozor
     DB_USERNAME=root
     DB_PASSWORD=
     ```

5. **Migratsiya va seeder ishga tushirish:**

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

6. **Assetlarni build qilish:**

    ```bash
    npm run build
    ```

7. **Serverni ishga tushirish:**
    ```bash
    php artisan serve
    ```

## Foydalanish

### Login

Ro'yxatdan o'ting (yoki Google orqali kiring). Bosh admin hisobi: `php artisan admin:create email@example.com`.

Login sahifasi: `http://localhost:8000/login`

### Parolni tiklash (Gmail orqali xat yuborish)

Login sahifasidagi **«Parolni unutdingizmi?»** havolasi: email kiritiladi → emailga 6 xonali kod yuboriladi (10 daqiqa amal qiladi, 5 ta urinish) → kod tasdiqlangach yangi parol o'rnatiladi.

Xat yuborish uchun Gmail sozlang:

1. Google hisobingizda **2 bosqichli tasdiqlash**ni yoqing: <https://myaccount.google.com/security>
2. **App password** yarating: <https://myaccount.google.com/apppasswords> (16 belgili parol; odatiy Gmail parolingiz ishlamaydi).
3. `.env` faylida (namuna: `.env.example`):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sizning@gmail.com
MAIL_PASSWORD=app-parol-16-belgi
MAIL_FROM_ADDRESS="sizning@gmail.com"
```

4. `php artisan migrate` va `php artisan config:clear`.

Lokal sinov uchun `MAIL_MAILER=log` qoldirilsa, kod xat o'rniga `storage/logs/laravel.log` fayliga yoziladi.

### Rollar va Permissions

- **User**: bitta hisob bilan ham sotadi, ham sotib oladi — e'lon joylash/tahrirlash/o'chirish (faqat o'z e'lonlari), kelgan so'rovlarni boshqarish, buyurtma yuborish, sevimlilar, chat
- **Admin** (bosh admin): e'lonlarni tekshirish (moderatsiya) va bannerlar

## Rasm Yuklash (MySQL Database Storage)

Barcha rasmlar (profil avatarlari va e'lonlar rasmlari) to'g'ridan-to'g'ri **`zoobozor` MySQL bazasida** (`database_images` jadvali, `LONGBLOB`) saqlanadi. Loyihaning lokal fayl tizimida hech qanday rasm saqlanmaydi:

- Formatlar: JPG, JPEG, PNG, WEBP, GIF
- Maksimal hajm: 2MB
- Saqlanish joyi: MySQL `zoobozor.database_images`
- Ko'rsatish: `/images/{path}` yoki `/storage/{path}` route orqali to'g'ridan-to'g'ri MySQL dan stream qilinadi.

## Qo'shimcha

Agar muammo yuzaga kelsa, quyidagilarni tekshiring:

- PHP va Composer versiyasi
- Ma'lumotlar bazasi ulanishi
- Permission va role migratsiyalari
