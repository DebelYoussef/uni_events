# University Events Management System - Quick Setup Guide

## Prerequisites
- XAMPP (or Laragon/WAMP)
- PHP 7.4+
- MySQL 5.7+
- Modern web browser

---

## Step 1: Extract Project Files

1. Download the project
2. Extract to your web server directory:
   - XAMPP: `C:\xampp\htdocs\uni_events\`
   - Laragon: `C:\laragon\www\uni_events\`

---

## Step 2: Create Database

### Option A: Using MySQL Command Line
```bash
# Open MySQL command prompt
mysql -u root -p

# Run database script
source /path/to/database.sql
```

### Option B: Using phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import"
3. Select `database.sql`
4. Click "Import"

---

## Step 3: Update Database Config

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');    // Change if needed
define('DB_USER', 'root');         // Your MySQL username
define('DB_PASS', '');             // Your MySQL password (if any)
define('DB_NAME', 'uni_events');   // Database name
```

---

## Step 4: Update Base URL (if needed)

Edit `config/constants.php`:

```php
define('BASE_URL', 'http://localhost/uni_events/');
// OR if on different port:
// define('BASE_URL', 'http://localhost:8080/uni_events/');
```

---

## Step 5: Create Uploads Directory

Run in project root:
```bash
mkdir -p public/uploads
chmod 755 public/uploads
```

Or create `public/uploads/` folder manually via file explorer.

---

## Step 6: Start Web Server

### XAMPP
1. Start Apache & MySQL from Control Panel
2. Or use: `xampp-control.exe`

### Laragon
1. Click "Start All"
2. Laragon will start all services

---

## Step 7: Test Application

Open in browser:
```
http://localhost/uni_events/
```

---

## Test Credentials

### Admin Account
- **Email**: admin@unievents.local
- **Password**: Admin@123

### Create Test Accounts

1. Go to Register page
2. Create a Student account
3. Create an Organizer account (will be pending approval)
4. Login as Admin to approve organizer

---

## Folder Structure

```
uni_events/
├── config/              ← Configuration files
├── includes/            ← Shared PHP includes (header, footer, auth)
├── pages/              ← Application pages
│   └── auth/           ← Authentication pages (login, register, logout)
├── actions/            ← POST handlers (no HTML output)
├── public/             ← Static files (CSS, JS, images)
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/        ← User uploaded files
├── lib/                ← Libraries (PDF, etc.)
├── index.php           ← Home page
├── database.sql        ← Database schema
└── README.md           ← Documentation
```

---

## Current Implementation (D4-D6)

✅ **D4: Registration**
- User registration with role selection
- Password validation
- Email uniqueness checking
- Bcrypt password hashing

✅ **D5: Login**
- Email/password authentication
- Session creation
- Role-based redirect
- Organizer approval workflow

✅ **D6: Logout & Layout**
- Secure logout
- Role-aware navigation
- Landing page
- Footer

---

## Database Tables

- **users** - User accounts (student, organizer, admin)
- **categories** - Event categories
- **events** - University events
- **registrations** - Student event registrations
- **attendance** - Event attendance records
- **certificates** - Digital certificates

---

## Security Features

✓ Bcrypt password hashing  
✓ CSRF token protection  
✓ SQL injection prevention (prepared statements)  
✓ Session timeout (30 minutes)  
✓ Input sanitization  
✓ Role-based access control  
✓ Generic error messages  

---

## Troubleshooting

### White screen on home page?
1. Check `config/database.php` credentials
2. Check MySQL is running
3. Ensure `database.sql` was imported

### 404 errors on pages?
1. Verify BASE_URL in `config/constants.php`
2. Check folder structure exists
3. Ensure all files are uploaded

### Login not working?
1. Check database was imported with sample data
2. Verify email/password in database
3. Check session is enabled in PHP

### Database connection error?
1. Verify MySQL is running
2. Check credentials in `config/database.php`
3. Ensure database `uni_events` exists

---

## Next Steps

Ready to expand? The foundation (D4-D6) is complete. Ready to build:

- **D7**: Student Dashboard pages
- **D8**: Event management (create, edit)
- **D9**: Attendance validation
- **D10**: Admin panel
- **D11**: Certificate generation

---

## Support

For detailed implementation documentation, see `D4-D6-COMPLETED.md`

For general info, see `README.md`
