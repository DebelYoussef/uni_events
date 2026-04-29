# Quick Reference Guide - University Events Management System

## 🚀 Quick Start

```bash
1. Import database.sql → mysql -u root -p < database.sql
2. Update config/database.php with your MySQL credentials
3. Create public/uploads/ folder
4. Open http://localhost/uni_events/
5. Register → Login → Redirected to dashboard
```

## 📁 Important Files at a Glance

| File | Purpose |
|------|---------|
| `config/constants.php` | App settings (BASE_URL, timeout, roles) |
| `config/database.php` | MySQL connection |
| `includes/auth.php` | Authentication guards |
| `includes/functions.php` | Helper functions |
| `includes/header.php` | Navigation bar |
| `pages/auth/register.php` | Registration form |
| `pages/auth/login.php` | Login form |
| `public/css/style.css` | All styling |
| `database.sql` | Database schema |

## 🔐 Default Login Credentials

```
Email: admin@unievents.local
Password: Admin@123
```

## 📝 How to Use Key Functions

### Authentication Guards
```php
// In any page that needs authentication:
require_login();  // Redirect to login if not logged in

// For role-specific pages:
require_role(ROLE_STUDENT);  // Only students allowed
require_role([ROLE_ADMIN, ROLE_ORGANIZER]);  // Multiple roles

// For approved organizers only:
require_approved_organizer();
```

### Getting User Info
```php
// Get current user ID
$user_id = get_current_user_id();

// Get current user role
$role = get_current_role();

// Get all user data
$user = get_current_user();
echo $user['name'];
echo $user['email'];
echo $user['role'];
echo $user['is_approved'];
```

### Password Operations
```php
// Hash a password
$hashed = hash_password($password);

// Verify password
if (verify_password($input_password, $hashed)) {
    // Password matches
}

// Check password strength
$errors = get_password_feedback($password);
if (!empty($errors)) {
    // Show errors
}
```

### Email Operations
```php
// Validate email format
if (is_valid_email($email)) {
    // Valid
}

// Check if email exists
if (email_exists($pdo, $email)) {
    // Already registered
}
```

### Messages (Flash Messages)
```php
// Set a message
set_message('Account created successfully!', SUCCESS);

// Display message on next page
display_flash_message();

// Or get it for manual display
$flash = get_flash_message();
if ($flash) {
    echo $flash['message'];  // Message text
    echo $flash['type'];      // success, error, warning, info
}
```

### CSRF Protection
```php
// In form, output CSRF token
<input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

// In handler, verify token
if (!verify_csrf_token($_POST['csrf_token'])) {
    die('CSRF token invalid');
}
```

### Redirection
```php
// Simple redirect
redirect('pages/student/dashboard.php');

// With BASE_URL
redirect('index.php');

// To external URL
header('Location: http://example.com');
exit;
```

## 🗄️ Database Tables Quick Reference

### users
```sql
SELECT * FROM users;
-- id, name, email, password, role, is_approved, student_id, created_at
```

### events
```sql
SELECT e.*, c.name as category FROM events e 
LEFT JOIN categories c ON e.category_id = c.id;
-- id, organizer_id, category_id, title, description, location, event_date, capacity, status
```

### registrations
```sql
SELECT * FROM registrations WHERE student_id = ?;
-- id, student_id, event_id, registered_at, status
```

### attendance
```sql
SELECT * FROM attendance WHERE registration_id = ?;
-- id, registration_id, validated_by, validated_at, notes
```

## 🎨 CSS Variables Available

```css
--primary-color: #2563eb
--primary-dark: #1e40af
--secondary-color: #f59e0b
--success-color: #10b981
--danger-color: #ef4444
--warning-color: #f97316
--info-color: #0ea5e9
--light-bg: #f9fafb
--dark-bg: #111827
--border-color: #e5e7eb
--text-dark: #1f2937
--text-light: #6b7280
--spacing-unit: 0.5rem
```

## 🔄 Common Form Pattern

```php
<?php
// At top of page
require_once '../../includes/auth.php';

// Require login
require_login();

// Check POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('Invalid request', ERROR);
        redirect('page.php');
    }
    
    // Get & sanitize input
    $input = sanitize_input($_POST['field'] ?? '');
    
    // Validate
    if (is_empty($input)) {
        $errors[] = 'Field required';
    }
    
    // Process if valid
    if (empty($errors)) {
        // Do something
        set_message('Success!', SUCCESS);
        redirect('somewhere.php');
    }
}

// Display form
?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <!-- Fields -->
    <button type="submit">Submit</button>
</form>
```

## 📱 Responsive Utilities

```html
<!-- Flexbox -->
<div style="display: flex; gap: 1rem; justify-content: space-between;">

<!-- Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">

<!-- Spacing -->
margin: calc(var(--spacing-unit) * 2);  <!-- 1rem -->
padding: calc(var(--spacing-unit) * 4); <!-- 2rem -->
gap: calc(var(--spacing-unit) * 3);      <!-- 1.5rem -->
```

## 🛠️ Debugging

```php
// Log message
error_log("Debug: " . print_r($variable, true));

// Check session
var_dump($_SESSION);

// Check if logged in
if (!is_logged_in()) {
    echo "Not logged in";
}

// View current user
echo get_current_user()['email'];
```

## 📋 File Naming Conventions

- **Pages**: `page-name.php` (kebab-case)
- **Actions**: `action-name.php` (kebab-case)
- **Functions**: `function_name()` (snake_case)
- **Classes**: `ClassName` (PascalCase)
- **Constants**: `CONSTANT_NAME` (UPPER_SNAKE_CASE)

## ⚙️ Session Variables

```php
$_SESSION['user_id']      // User ID (int)
$_SESSION['email']        // User email
$_SESSION['name']         // User display name
$_SESSION['role']         // User role (student/organizer/admin)
$_SESSION['is_approved']  // Organizer approval status (0/1)
$_SESSION['csrf_token']   // CSRF protection token
$_SESSION['last_activity'] // Last activity timestamp
```

## 🔍 Important Constants

```php
ROLE_STUDENT      // 'student'
ROLE_ORGANIZER    // 'organizer'
ROLE_ADMIN        // 'admin'
BASE_URL          // 'http://localhost/uni_events/'
SESSION_TIMEOUT   // 1800 (seconds)
MIN_PASSWORD_LENGTH    // 8
BCRYPT_ROUNDS     // 12
```

## 📚 Common Queries

```php
// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get user by email
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Count events
$stmt = $pdo->query("SELECT COUNT(*) FROM events");
$count = $stmt->fetchColumn();

// Get events with categories
$stmt = $pdo->query("
    SELECT e.*, c.name as category_name 
    FROM events e 
    LEFT JOIN categories c ON e.category_id = c.id
");
$events = $stmt->fetchAll();
```

## 🐛 Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| 500 error on login | Check config/database.php credentials |
| Redirect loop | Check BASE_URL in config/constants.php |
| CSS not loading | Verify BASE_URL and public/css/ exists |
| Session not persisting | Ensure session_start() called early |
| CSRF token invalid | Check token value matches in form & handler |

## 📞 Support Resources

- **Setup**: See `SETUP.md`
- **Implementation Details**: See `D4-D6-COMPLETED.md`
- **Full Docs**: See `README.md`
- **Project Status**: See `PROJECT-SUMMARY.txt`

---

**Remember**: All files must be in the correct directory structure under `/uni_events/` for the application to work correctly!
