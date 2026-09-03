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

Test userlar:

- **Seller**: seller@example.com / password
- **User**: user@example.com / password

Login sahifasi: `http://localhost:8000/login`

### Rollar va Permissions

- **Seller (Admin)**: Barcha amallar
- **User**: Ko'rish, buyurtma yuborish, chat

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
