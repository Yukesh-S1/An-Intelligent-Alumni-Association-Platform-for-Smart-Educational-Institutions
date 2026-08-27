# 🚀 Alumni Portal - Quick Start Guide

## Prerequisites
✅ XAMPP installed
✅ Apache running (port 80 or 8080)
✅ MySQL running (port 3306)

## Step 1: Start XAMPP Services
1. Open XAMPP Control Panel as Administrator
2. Click "Start" for Apache (should show green)
3. Click "Start" for MySQL (should show green)

## Step 2: Initialize Database
Open your browser and run:
```
http://localhost:8080/new/init_db.php
```
This will:
- Create the "Alumni" database
- Create all required tables
- Populate with dummy data

## Step 3: Access the Portal

### Main Entry Points:
- **Landing Page**: http://localhost:8080/new/index.php
- **Main Portal**: http://localhost:8080/new/main.php
- **Login Page**: http://localhost:8080/new/login.php
- **Admin Dashboard**: http://localhost:8080/new/admin_dashboard.php

### Test Accounts:

**Admin Account:**
- Email: `admin@swin.edu.my`
- Password: `admin`

**User Accounts:**
- Email: `user0@test.com` (or user1, user2, user5)
- Password: `user`

## Step 4: Test Connection
Run the connection test:
```
http://localhost:8080/new/test_connection.php
```
Should show green success message with database details.

## Important Note:
Your Apache is configured to use port 8080 (not the default port 80).
Files are served from: `C:\xampp\mysql\bin\htdocs\`

## Features Available:
- 👤 User Registration & Login
- 📊 Admin Dashboard
- 📅 Events Management
- 📢 Advertisements
- 💬 Chat System
- 📹 Video Calls
- 🔔 Notifications
- 📱 Mobile App (React Native)

## Troubleshooting:
- If Apache won't start: Check port conflicts (run fix_port_conflict.bat)
- If MySQL won't start: Check port 3306 is free
- If database errors: Run init_db.php again
- If pages don't load: Verify folder name is "new" or update URLs

## Project Structure:
- `/components/` - Reusable PHP components
- `/css/` - Stylesheets
- `/images/` - Image assets
- `/AlumniPortalMobileApp/` - React Native mobile app
- `db_controller.php` - Database connection
- `init_db.php` - Database setup script
