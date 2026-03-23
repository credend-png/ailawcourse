# LexLearnAI Platform — Deployment Guide

## 📁 Folder Structure
```
public_html/           ← Upload everything here (or your domain root)
├── .htaccess
├── config.php         ← ⚠️ MUST configure first
├── database.sql       ← Import this into MySQL
├── index.php
├── admin/
├── auth/
├── assets/
│   ├── css/
│   └── js/
├── includes/
├── pages/
├── student/
└── uploads/           ← Must be writable (chmod 755)
    ├── certificates/
    ├── courses/
    ├── materials/
    └── profiles/
```

---

## 🚀 Step 1: Upload Files
Upload all files to your hosting's `public_html` folder (or a subdirectory if installing in a subfolder).

---

## 🗄️ Step 2: Create MySQL Database
1. Log in to your cPanel
2. Go to **MySQL Databases**
3. Create a new database (e.g., `lexlearn_db`)
4. Create a MySQL user and set a strong password
5. Add the user to the database with **All Privileges**
6. Go to **phpMyAdmin**, select your database, and import `database.sql`

---

## ⚙️ Step 3: Configure config.php
Open `config.php` and update these values:

```php
// Site URL — no trailing slash
define('SITE_URL', 'https://yourdomain.com');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// PayU Credentials (get from PayU dashboard)
define('PAYU_MERCHANT_KEY', 'your_payu_merchant_key');
define('PAYU_MERCHANT_SALT', 'your_payu_salt');
define('PAYU_MODE', 'test'); // Change to 'live' for production

// SMTP Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your@gmail.com');
define('SMTP_PASS', 'your_app_password'); // Use Gmail App Password
define('SMTP_FROM_NAME', 'LexLearnAI');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');

// Paths (update if installing in subfolder)
define('ADMIN_URL', SITE_URL . '/admin');
define('STUDENT_URL', SITE_URL . '/student');
define('AUTH_URL', SITE_URL . '/auth');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');
```

---

## 📁 Step 4: Set Folder Permissions
In your hosting File Manager or via SSH:
```
chmod 755 uploads/
chmod 755 uploads/certificates/
chmod 755 uploads/courses/
chmod 755 uploads/materials/
chmod 755 uploads/profiles/
```

---

## 💳 Step 5: PayU Setup

### Test Credentials (for testing):
- Get test credentials from: https://onboarding.payu.in/
- Test card: 5123456789012346, CVV: 123, Expiry: any future date
- Test URL (auto-set in code): `https://test.payu.in/_payment`

### Going Live:
1. Complete PayU KYC at https://onboarding.payu.in/
2. Get live Key and Salt from PayU dashboard
3. Update config.php: `PAYU_MERCHANT_KEY`, `PAYU_MERCHANT_SALT`, `PAYU_MODE = 'live'`
4. Update `student/payment-success.php` and `student/enroll.php` URLs to live

### PayU Callback URLs (configure in PayU dashboard):
- **Success URL**: `https://yourdomain.com/student/payment-success.php`
- **Failure URL**: `https://yourdomain.com/student/payment-failure.php`

---

## 📧 Step 6: Gmail SMTP Setup
1. Enable 2-Factor Authentication on your Gmail account
2. Go to Google Account → Security → App Passwords
3. Generate an App Password for "Mail"
4. Use this as `SMTP_PASS` in config.php

---

## 🔐 Step 7: Admin Login
Default admin credentials (change immediately after login!):
- **URL**: `https://yourdomain.com/admin/login.php`
- **Email**: `admin@lexlearnai.com`
- **Password**: `Admin@12345`

### To change admin password:
1. Log in to phpMyAdmin
2. Go to the `admins` table
3. Click Edit on the admin row
4. Replace `password` value with: `password_hash('YourNewPassword', PASSWORD_BCRYPT)`
   - Or use this PHP snippet: `echo password_hash('YourNewPassword', PASSWORD_BCRYPT);`

---

## 🔒 Step 8: Security Checklist
- [ ] Change default admin password
- [ ] Set a strong CSRF secret in config.php (`SESSION_SECRET`)
- [ ] Enable HTTPS (uncomment SSL redirect in .htaccess)
- [ ] Set `PAYU_MODE` to `'live'` before accepting real payments
- [ ] Ensure `uploads/` is not publicly executable (PHP blocked)
- [ ] Remove or restrict access to `database.sql` after import
- [ ] Set secure session cookie settings (already done in config.php)

---

## 🧪 Test Checklist
- [ ] Homepage loads correctly
- [ ] Student registration works
- [ ] Student login works
- [ ] Course listing shows active courses
- [ ] PayU test payment flow completes (use test card)
- [ ] Payment success creates enrollment
- [ ] Student dashboard shows enrolled course
- [ ] Certificate verification works
- [ ] Admin panel login works
- [ ] Admin can add courses, schedule classes, upload materials
- [ ] Admin can issue certificates

---

## 📂 Key Files Reference
| File | Purpose |
|------|---------|
| `config.php` | All configuration, DB, helpers |
| `database.sql` | Full MySQL schema + seed data |
| `admin/dashboard.php` | Admin home |
| `student/dashboard.php` | Student home |
| `student/enroll.php` | Payment initiation |
| `student/payment-success.php` | PayU callback handler |
| `pages/verify-certificate.php` | Public cert verification |
| `assets/css/style.css` | Public site styles |
| `assets/css/admin.css` | Admin panel styles |

---

## 🆘 Common Issues

**Database connection error**: Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` in config.php

**PayU hash mismatch**: Ensure `PAYU_MERCHANT_SALT` exactly matches what's in your PayU dashboard (copy-paste, no spaces)

**Emails not sending**: Ensure SMTP credentials are correct. Gmail requires App Password, not regular password.

**Upload not working**: Check `uploads/` folder permissions (must be 755 or 777)

**Blank pages**: Enable PHP error display temporarily: add `ini_set('display_errors',1); error_reporting(E_ALL);` at top of config.php

---

## 📞 Support
For deployment assistance, contact: support@lexlearnai.com
