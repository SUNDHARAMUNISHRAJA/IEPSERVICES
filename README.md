# Integrated Engineers Point — Web Application
## Setup Instructions

### Requirements
- PHP 7.4+ (PHP 8.x recommended)
- MySQL 5.7+ / MariaDB 10.3+
- Apache / Nginx with mod_rewrite
- Web browser (Chrome, Firefox, Safari, Edge)

---

### 1. Database Setup
1. Open phpMyAdmin or MySQL CLI
2. Run the contents of `database.sql` to create the database and tables
3. The script creates the `iep_db` database with all required tables and sample data

### 2. Configure Database Connection
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'iep_db');
```

### 3. Deploy Files
Copy the entire `iep/` folder to your web server's root (e.g., `htdocs/` or `public_html/`)

### 4. File Structure
```
iep/
├── index.php               # Main website (homepage)
├── database.sql            # Database schema + seed data
├── includes/
│   └── config.php          # DB config & helper functions
├── ajax/
│   ├── submit_enquiry.php  # Handles enquiry form submission
│   ├── submit_feedback.php # Handles feedback form submission
│   └── get_testimonials.php # Returns approved reviews as JSON
├── admin/
│   ├── login.php           # Admin login page
│   ├── dashboard.php       # Admin panel (enquiries + feedback)
│   └── logout.php          # Session destroy
└── assets/
    ├── css/style.css       # Complete stylesheet
    └── js/main.js          # All JavaScript (AJAX, animations)
```

### 5. Admin Panel Access
URL: `http://yourdomain.com/iep/admin/login.php`
- **Username:** `admin`
- **Password:** `password`  ← Change this immediately!

To change the admin password, run in MySQL:
```sql
UPDATE admin_users SET password_hash = '$2y$10$YOUR_HASH' WHERE username = 'admin';
```
Generate hash in PHP: `echo password_hash('your_new_password', PASSWORD_DEFAULT);`

---

### Features
**Customer-facing:**
- ✅ Hero section with Enquiry Form (AJAX, validated)
- ✅ About Us section
- ✅ 8 Service categories with details
- ✅ AMC / Maintenance plans (Basic, Comprehensive, Industrial)
- ✅ Why Choose Us section
- ✅ Client logos marquee (Tata, Godrej, L&T, Adani, etc.)
- ✅ Customer Testimonials (loaded from DB via AJAX)
- ✅ Feedback / Review submission form with star rating
- ✅ Contact section with call/WhatsApp/email links
- ✅ Floating WhatsApp button
- ✅ Smooth scroll navigation
- ✅ Animated counters
- ✅ Fully responsive (mobile, tablet, desktop)

**Admin Panel:**
- ✅ Secure login with password hashing
- ✅ Dashboard with stats
- ✅ Enquiry management (view, update status, delete)
- ✅ Feedback moderation (approve/reject)
- ✅ Published reviews management
- ✅ Live search/filter

---

### Customization
- **Phone number:** Search and replace `98765 43210` with your actual number
- **Email:** Replace `info@integratedengineerspoint.com`
- **Address:** Update in `index.php` contact section
- **Logo:** Add your actual logo image to `assets/images/` and update HTML
- **Colors:** Edit CSS variables in `assets/css/style.css` `:root` block
