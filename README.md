# 🌐 Website Uptime Monitor

![GitHub repo size](https://img.shields.io/github/repo-size/Bishnupkl/laravelmonitoring) ![GitHub last commit](https://img.shields.io/github/last-commit/Bishnupkl/laravelmonitoring) ![GitHub license](https://img.shields.io/github/license/Bishnupkl/laravelmonitoring)

**A robust Laravel-powered uptime monitoring system with a sleek Vue.js frontend.**

Welcome to the **Website Uptime Monitor**, a final project by **Bishnu Pokhrel** (Student ID: [Your Student ID Here]). This application monitors website availability every 15 minutes, sends real-time email alerts when sites go down, and provides a foundation for a modern dashboard built with Vue.js. Whether it’s a timeout, DNS failure, or server issue, you’ll know the moment it happens!

---

## 🗃️ Project Overview

This project combines a powerful Laravel backend with a Vue.js frontend to deliver a seamless uptime monitoring experience. It checks registered websites, detects issues like timeouts (e.g., cURL error 28), and notifies users via email—all while offering a scalable, queue-driven architecture.

### ✨ Key Features
- **Website Registration**: Add websites with client emails for monitoring.
- **Real-Time Monitoring**: Checks every 15 minutes with a 10-second timeout threshold.
- **Email Alerts**: Instant notifications via Mailtrap when sites are down.
- **Queue Processing**: Powered by Redis for efficient, asynchronous checks.
- **Vue.js Frontend**: A responsive dashboard (in progress) to visualize uptime status.

---

## 🛠️ Technology Stack

### Backend
- **Laravel 10** - PHP framework for robust backend logic.
- **PHP 8.x** - Modern PHP runtime.
- **Redis** - Queue driver for async job processing.
- **MySQL** - Database for storing clients and websites.
- **Guzzle** - HTTP client for website checks.

### Frontend
- **Vue.js 3** - Reactive JavaScript framework for a dynamic UI.
- **Vite** - Fast frontend build tool bundled with Laravel.

### Email
- **Mailtrap** - SMTP service for testing email alerts.
- **Laravel Mail** - Email sending integration.

### Tools
- **Supervisor** - Keeps monitoring and queue processes running.
- **Artisan** - Custom commands for monitoring (`websites:monitor`).

---

## 🚀 Getting Started

Follow these steps to set up the project locally or deploy it to a server.

### Prerequisites
- 🖥️ PHP 8.x & Composer
- 🌐 Node.js 18.x & npm
- 🗄️ MySQL
- 🔴 Redis
- 💎 Mailtrap account
- ☁️ Optional: AWS account for production

---

### Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/Bishnupkl/laravelmonitoring
cd laravelmonitoring
```

#### 2. Backend Setup (Laravel)
1. **Install PHP Dependencies**:
   ```bash
   composer install
   ```
2. **Set Up Environment**:
    - Copy `.env.example` to `.env`:
      ```bash
      cp .env.example .env
      ```
    - Edit `.env` with your credentials:
      ```env
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=uptime_monitor
      DB_USERNAME=root
      DB_PASSWORD=your_password

      QUEUE_CONNECTION=redis
      REDIS_HOST=127.0.0.1
      REDIS_PASSWORD=null
      REDIS_PORT=6379

      MAIL_MAILER=smtp
      MAIL_HOST=smtp.mailtrap.io
      MAIL_PORT=2525
      MAIL_USERNAME=your-mailtrap-username
      MAIL_PASSWORD=your-mailtrap-password
      MAIL_ENCRYPTION=tls
      MAIL_FROM_ADDRESS=do-not-reply@example.com
      MAIL_FROM_NAME="Uptime Monitor"
      ```
3. **Generate App Key**:
   ```bash
   php artisan key:generate
   ```
4. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

#### 3. Frontend Setup (Vue.js)
1. **Install Node Dependencies**:
   ```bash
   npm install
   ```
2. **Start Development Server**:
   ```bash
   npm run dev  # Or use npm run watch
   ```
3. **Build Frontend for Production**:
   ```bash
   npm run build
   ```

#### 4. Start the Application
- **Run Laravel Server**:
  ```bash
  php artisan serve
  ```
- **Run Monitoring Daemon**:
  ```bash
  php artisan websites:monitor
  ```
- **Run Queue Worker**:
  ```bash
  php artisan queue:work redis --queue=website_checks --tries=3 --timeout=30
  ```

---

## 🧠 Automated Monitoring with Cron
The system is already set up to work with **cron jobs**. Add the following cron job to run the monitor command automatically:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

This ensures Laravel's scheduler runs as expected.

---

## 🧬 Testing
1. **Run Monitoring Tests**:
   ```bash
   php artisan test --filter=CheckWebsitesTest
   php artisan test --filter=MonitorWebsitesTest
   ```
2. **Run General Tests**:
   ```bash
   php artisan test
   ```
3. **Manually Check Monitoring**:
   ```bash
   php artisan websites:monitor
   ```

Two test files have been created for monitoring commands to ensure reliability.

---

## 📸 Screenshots
https://github.com/Bishnupkl/laravelmonitoring/blob/master/screenshots/Screenshot%20from%202025-03-19%2000-06-52.png
https://github.com/Bishnupkl/laravelmonitoring/blob/master/screenshots/Screenshot%20from%202025-03-19%2000-06-57.png
https://github.com/Bishnupkl/laravelmonitoring/blob/master/screenshots/Screenshot%20from%202025-03-19%2000-07-07.png
https://github.com/Bishnupkl/laravelmonitoring/blob/master/screenshots/Screenshot%20from%202025-03-19%2000-13-01.png

)

