# SignUpAlphaGrow - SCM SaaS Platform

A complete Supply Chain Management SaaS application built with Laravel 10 + MySQL.

## 🎯 Features

- **Multi-Wing Support**: Manufacturer, Warehouse, Distributor
- **Operations**: Inventory, Orders, Production, BOM, Logistics, Machines
- **Supply Chain**: Traceability, GR/QR Posting, Returns, Warehouse Capacity
- **Finance & HR**: Invoices, Procurement, Expenses, Payroll, CRM, POS
- **Intelligence**: Analytics, BI Dashboard, Reports, DMS
- **Administration**: Company Setup, Users, Roles, Audit Trail

## 🛠️ Tech Stack

- **Backend**: Laravel 10 (PHP 8.1+)
- **Database**: MySQL
- **Frontend**: Bootstrap 5 (Offline), Custom CSS
- **Charts**: Chart.js

## 📦 Installation

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/signupalphagrow.git
cd signupalphagrow

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env then run:
php artisan migrate
php artisan db:seed --class=SuperAdminSeeder

# Start the server
php artisan serve

🔐 Default Login
Email: admin@alphagrow.com
Password: password123
📊 Project Stats
Total Pages: 27
Total Modules: 6 Groups
Total Database Tables: 48
Total Roles: 17
Total Wings: 3
👨‍💻 Author
Built by Kundan Giriya
📄 License
Private Project - All Rights Reserved


---

## Step 3: Create a `.env.example` File (Important!)

Since `.env` is excluded, you need a template file so others know what variables are needed. Create **`.env.example`**:

```env
APP_NAME=SignUpAlphaGrow
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=signupalphagrow
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120



---

Step 4: Initialize Git and Push to GitHub
4.1 Create a GitHub Repository First
Go to https://github.com/new
Repository name: signupalphagrow
DO NOT check "Add a README file" (we already have one)
DO NOT add .gitignore or license (we already have them)
Click Create repository
Copy the repository URL (e.g., https://github.com/YOUR_USERNAME/signupalphagrow.git)
4.2 Run These Commands in Your Terminal
Open PowerShell in your project folder (C:\xampp\htdocs\signupalphagrow) and run:

# Initialize Git repository
git init

# Add all files (respects .gitignore automatically)
git add .

# First commit
git commit -m "Initial commit: SignUpAlphaGrow SCM SaaS Platform"

# Add GitHub as remote origin (replace with YOUR URL)
git remote add origin https://github.com/YOUR_USERNAME/signupalphagrow.git

# Rename branch to main (GitHub default)
git branch -M main

# Push to GitHub
git push -u origin main

