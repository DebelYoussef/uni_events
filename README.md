# University Events Management System

A comprehensive PHP-based web application for managing university events, registrations, and attendance tracking.

## Project Status

- ✅ D1-D3: Database setup, folder structure, configuration
- ✅ D4: Registration system with validation
- ✅ D5: Login system with session authentication
- ✅ D6: Logout functionality and base layout

## Features

### For Students
- Browse and discover university events
- Register for events
- View registration history
- Download attendance certificates
- User dashboard

### For Event Organizers
- Create and manage events
- View registered participants
- Validate attendance
- Generate participant lists
- Track event statistics (pending)

### For Administrators
- Manage user accounts
- Approve organizer registrations
- Manage all events on the platform
- View system-wide statistics
- User activity monitoring (pending)

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Server**: Apache (XAMPP/Laragon)

## Installation & Setup

### Prerequisites
- XAMPP or Laragon
- PHP 7.4 or higher
- MySQL
- Modern web browser

### Step 1: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `uni_events`
3. Run the SQL script to create tables and seed initial data:

```sql
-- Run database.sql to create all tables
-- Tables include: users, events, registrations, attendance, certificates, categories
```

### Step 2: Project Setup

1. Extract the project to your XAMPP/Laragon htdocs folder:
   - XAMPP: `C:\xampp\htdocs\uni_events`
   - Laragon: `C:\laragon\www\uni_events`

2. Update database credentials in `config/database.php`:
```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // Your MySQL password (if any)
$DB_NAME = 'uni_events';
```

3. Update BASE_URL in `config/constants.php` if needed:
```php
define('BASE_URL', 'http://localhost/uni_events/');
```

### Step 3: Run the Application

1. Start Apache and MySQL in XAMPP/Laragon
2. Navigate to: `http://localhost/uni_events/`
3. Create an account or login with default credentials (if available)

## Default Admin Account

When you first set up the system, you may need to create an admin account manually:

```sql
-- Insert admin user (password hashed)
INSERT INTO users (name, email, password, role, is_approved, created_at) 
VALUES ('Admin User', 'admin@unievents.local', '$2y$12$...', 'admin', 1, NOW());
```

Password should be hashed using bcrypt with cost 12.

## Project Structure

```
uni_events/
├── config/
│   ├── constants.php      ← App configuration & constants
│   └── database.php       ← PDO database connection
├── includes/
│   ├── auth.php          ← Authentication guards & session management
│   ├── functions.php     ← Helper functions (sanitization, validation)
│   ├── header.php        ← Navigation header with role-aware menu
│   └── footer.php        ← Footer partial
├── pages/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   └── pending-approval.php
│   ├── student/          ← Protected: Dashboard, Events, Registrations, Certificates
│   ├── organizer/        ← Protected: Dashboard, Create/Edit Events, Participants, Attendance
│   └── admin/            ← Protected: Dashboard, Users, Statistics, Event Management
├── actions/
│   ├── register-user.php ← POST handler for registration
│   └── login-user.php    ← POST handler for login
├── public/
│   ├── css/
│   │   └── style.css     ← Main stylesheet
│   ├── js/
│   │   └── main.js       ← JavaScript utilities
│   ├── images/
│   └── uploads/          ← User uploaded files
├── index.php             ← Home page / Landing page
└── database.sql          ← Database schema & seed data
```

## Security Features

- **Password Security**: Bcrypt hashing with cost 12
- **Input Validation**: Server-side validation for all inputs
- **Sanitization**: HTML escaping and input sanitization
- **CSRF Protection**: Token-based CSRF prevention
- **Session Management**: Secure session handling with timeout
- **SQL Injection Prevention**: Prepared statements (PDO)
- **Role-Based Access Control**: Protected pages with role validation

## Database Schema

### Users Table
- `id`: Primary key
- `name`: User's full name
- `email`: Unique email address
- `password`: Bcrypt hashed password
- `role`: ENUM(student, organizer, admin)
- `is_approved`: For organizer approval workflow
- `student_id`: University student ID
- `created_at`: Account creation timestamp

### Events Table
- `id`: Primary key
- `organizer_id`: FK to users
- `category_id`: FK to categories
- `title`: Event title
- `description`: Event description
- `location`: Event location
- `event_date`: When the event occurs
- `capacity`: Maximum participants
- `status`: ENUM(upcoming, ongoing, past, cancelled)
- `created_at`: Event creation timestamp

### Other Tables
- `registrations`: Student event registrations
- `attendance`: Attendance validation records
- `certificates`: Generated certificates
- `categories`: Event categories

## Form Validation Rules

### Registration
- **Name**: 2-100 characters
- **Email**: Valid email format, must be unique
- **Password**: Min 8 chars, 1 uppercase, 1 number, 1 special character
- **Student ID**: Required for students, max 20 characters
- **Role**: Must select student or organizer

### Login
- **Email**: Valid email format
- **Password**: Cannot be empty

## Session Configuration

- **Timeout**: 30 minutes of inactivity
- **Session Name**: `uni_events_session`
- **Stored Data**: user_id, email, name, role, is_approved, last_activity

## API Endpoints (POST handlers)

### Authentication
- `POST /actions/register-user.php` - Register new user
- `POST /actions/login-user.php` - Authenticate user

### Events (Future Implementation)
- `POST /actions/create-event.php` - Create event (organizer)
- `POST /actions/register-event.php` - Register for event (student)
- `POST /actions/cancel-registration.php` - Cancel registration

### Attendance (Future Implementation)
- `POST /actions/validate-attendance.php` - Validate attendance (organizer)
- `POST /actions/generate-certificate.php` - Generate certificate

## Common Issues & Troubleshooting

### Database Connection Error
- Ensure MySQL is running
- Check credentials in `config/database.php`
- Verify database name is correct

### Session Not Working
- Ensure cookies are enabled in browser
- Check session directory permissions
- Clear browser cookies and try again

### Password Too Weak
- Must include uppercase letter
- Must include number
- Must include special character (!@#$%^&*)
- Minimum 8 characters

### Organizer Account Stuck on Pending Approval
- Admin must approve in admin panel
- Email notification will be sent upon approval
- Contact admin if not approved within 48 hours

## Next Steps (D7+)

The following are planned for future development:

- D7: Student dashboard pages
- D8: Event browsing and details
- D9: Event registration functionality
- D10: Organizer event management
- D11: Attendance validation system
- D12: Certificate generation
- D13: Admin management pages
- D14: Statistics and reporting
- D15: Testing and deployment

## Security Notes

⚠️ **Important for Production**:
- Use HTTPS in production
- Set strong admin password
- Configure proper file permissions
- Enable MySQL secure connection
- Keep PHP and dependencies updated
- Use environment variables for sensitive config
- Implement rate limiting on auth endpoints
- Enable firewall and DDoS protection

## Support & Contact

For issues, questions, or contributions:
- Email: support@unievents.local
- Bug Reports: Submit via issue tracker
- Suggestions: Contribute via pull requests

## License

This project is provided as-is for educational purposes.

## Authors

University Events Management Development Team

---

**Last Updated**: D6 Complete (Logout & Base Layout)
