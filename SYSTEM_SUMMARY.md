# EMEMHS Guidance System - Complete System Summary

## 📋 Project Overview

**EMEMHS EDUCARE GUIDANCE SYSTEM WITH SMS NOTIFICATION FOR PARENTS**

A comprehensive web-based guidance management system designed for EMEMHS (Enriqueta Montilla De Esteban Memorial High School) that streamlines counseling services, complaint management, lost & found items, and parent communication through automated SMS notifications.

### Project Type
Capstone Project / School Management System

### Institution
EMEMHS (Enriqueta Montilla De Esteban Memorial High School)

---

## 🎯 Core Features

### 1. Student Management
- **Student Registration & Authentication**
  - Secure user registration with email verification
  - Role-based access control (Student, Admin/Staff)
  - Profile management with photo upload
  - Password reset functionality via email

### 2. Complaint & Concern Management
- **Student Portal**
  - Submit complaints/concerns with severity levels (Low, Medium, High, Urgent)
  - Upload evidence (images/documents)
  - Voice-to-text input support
  - Track complaint status (Pending, Scheduled, Resolved)
  - Request reschedule for counseling sessions
  - Real-time notifications

- **Admin Portal**
  - View and manage all complaints
  - Schedule counseling sessions
  - Set available time slots
  - Approve/reject reschedule requests
  - Mark complaints as resolved
  - Filter by status and severity

### 3. Lost & Found Management
- **Report Lost Items**
  - Submit lost item reports with descriptions
  - Upload item photos
  - Opt-in for SMS notifications
  - Track item status (Lost, Found, Claimed)

- **Admin Management**
  - View all lost items
  - Mark items as found
  - Smart matching system (AI-powered suggestions)
  - Verify claimants with photo comparison
  - Process item claims
  - Send SMS notifications to students

### 4. SMS Notification System (Semaphore API)
- **Parent Notifications**
  - Automatic SMS when student submits complaint
  - Severity-based messaging
  - Scheduled session reminders

- **Student Notifications**
  - Lost item found alerts
  - Counseling session reminders
  - Status updates

- **Admin Notifications**
  - Daily summary of pending tasks
  - Urgent complaint alerts
  - Morning/afternoon session reminders
  - Unscheduled complaints notifications

### 5. Anonymous Suggestion Box
- **Public Feature**
  - Submit anonymous suggestions from landing page
  - Rate limiting (1 suggestion per 24 hours via localStorage)
  - No authentication required

- **Admin Management**
  - View all suggestions
  - Filter by read/unread status
  - Mark as read/unread
  - Delete suggestions
  - Statistics dashboard

### 6. Dashboard & Analytics
- **Student Dashboard**
  - Overview of submitted complaints
  - Lost items status
  - Upcoming counseling sessions
  - Notification center

- **Admin/Staff Dashboard**
  - Statistics and charts (Chart.js)
  - Pending complaints count
  - Scheduled sessions overview
  - Lost items summary
  - Quick action buttons
  - Real-time data visualization

### 7. Notification System
- **In-App Notifications**
  - Real-time notification center
  - Unread count badges
  - Mark all as read functionality
  - Role-based notifications
  - Activity logging

### 8. User Management
- **Admin Features**
  - View all students
  - Manage user accounts
  - Access control
  - Activity monitoring

---

## 🛠️ Technology Stack

### Frontend Technologies

#### Core
- **HTML5** - Semantic markup
- **CSS3** - Custom styling and animations
- **JavaScript (ES6+)** - Client-side interactivity

#### CSS Framework
- **Tailwind CSS 3.x** (CDN)
  - Utility-first CSS framework
  - Responsive design
  - Custom color schemes
  - Component styling

#### UI Libraries & Fonts
- **Google Fonts**
  - Inter (400, 500, 600, 700)
  - Plus Jakarta Sans (400, 500, 600, 700)
- **Font Awesome 6.0** - Icons
- **Animate.css 4.1.1** - CSS animations

#### JavaScript Libraries
- **Chart.js** - Data visualization and analytics charts
- **EmailJS 3.x** - Email service for password reset
- **Web Speech API** - Voice-to-text functionality

### Backend Technologies

#### Server-Side
- **PHP 7.4+** - Server-side scripting
  - Object-Oriented Programming (OOP)
  - PDO for database operations
  - MySQLi for legacy support
  - Session management
  - File upload handling

#### Database
- **MySQL 5.7+** / **MariaDB**
  - Relational database management
  - InnoDB engine
  - UTF-8 (utf8mb4) character set
  - Prepared statements for security

#### Database Tables
```
- users (authentication)
- students (student information)
- parents (parent contact info)
- complaints_concerns (complaint management)
- lost_items (lost & found)
- reschedule_requests (session rescheduling)
- notifications (in-app notifications)
- anonymous_suggestions (suggestion box)
- activity_logs (system logging)
```

### Third-Party APIs

#### SMS Service
- **Semaphore SMS API**
  - Philippine SMS gateway
  - Bulk SMS support
  - Delivery tracking
  - API key authentication

#### Email Service
- **EmailJS**
  - Password reset emails
  - Email templates
  - Client-side email sending

### Server & Deployment

#### Web Server
- **Apache 2.4+**
  - mod_rewrite enabled
  - .htaccess configuration
  - URL rewriting
  - Security headers

#### Development Environment
- **XAMPP** / **WAMP** / **LAMP**
  - Apache
  - MySQL/MariaDB
  - PHP
  - phpMyAdmin

### Security Features

#### Authentication & Authorization
- **Session-based authentication**
- **Role-based access control (RBAC)**
- **Password hashing** (PHP password_hash)
- **CSRF protection** (configurable)
- **SQL injection prevention** (prepared statements)
- **XSS protection** (htmlspecialchars, security headers)

#### Security Headers (.htaccess)
```
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
```

#### File Security
- Sensitive file blocking (config.php, .env)
- Directory listing disabled
- File upload validation
- MIME type checking

### Configuration Management

#### Centralized Configuration
- **config.php** - Main configuration file
  - Database credentials
  - API keys (Semaphore)
  - Application settings
  - Security settings
  - File upload limits
  - Environment configuration

#### Helper Functions
```php
getSemaphoreConfig() // SMS API configuration
getDatabaseConfig()  // Database configuration
```

### Development Tools

#### Version Control
- **Git** - Source control
- **.gitignore** - Exclude sensitive files

#### Code Editor Support
- **VS Code** configuration
- PHP IntelliSense
- Syntax highlighting

---

## 📁 Project Structure

```
EMEMHS-Guidance-System/
├── .git/                          # Git repository
├── .vscode/                       # VS Code settings
├── config/                        # Configuration files
├── image/                         # System images & assets
│   ├── default-image.png
│   ├── ememhs-logo.png
│   ├── landing-*.jpg/svg/jpeg
│   └── login-*.png/avif
├── js/                           # JavaScript files
│   └── complaint-concern.js
├── logic/                        # Backend logic
│   ├── ajaxLogic/               # AJAX handlers
│   ├── activity_logger.php      # Activity logging
│   ├── admin_sms_notif.php      # Admin SMS notifications
│   ├── admin_sms_notifications.php # Admin SMS class
│   ├── claim_item_logic.php     # Item claiming logic
│   ├── db_connection.php        # Database connection
│   ├── db_migrations*.sql       # Database migrations
│   ├── delete_complaint_logic.php
│   ├── get_matches.php          # Smart matching
│   ├── login_logic.php          # Authentication
│   ├── logout_logic.php
│   ├── notification_logic.php   # Notification system
│   ├── process_audio.php        # Voice processing
│   ├── register_logic.php       # Registration
│   ├── reset_password_handler.php
│   ├── smart_matching.php       # AI matching
│   ├── sql_querries.php         # SQL queries
│   ├── store_reset_token.php
│   ├── student_sms_notifications.php # Student SMS class
│   ├── submit_complaint_concern_logic.php
│   ├── submit_lost_item_logic.php
│   ├── submit_suggestion.php    # Anonymous suggestions
│   ├── test_semaphore.php       # SMS testing
│   └── test_sms.php             # DB testing
├── pages/                        # Frontend pages
│   ├── components/              # Reusable components
│   ├── admin-lost-items.php     # Admin lost items
│   ├── admin-suggestions.php    # Suggestion management
│   ├── analytics.php            # Analytics dashboard
│   ├── claim-item.php           # Claim item page
│   ├── complaint-concern-admin.php # Admin complaints
│   ├── complaint-concern-form.php  # Submit complaint
│   ├── complaint-concern.php    # Student complaints
│   ├── forgot-password.php      # Password recovery
│   ├── home.php                 # Home page
│   ├── login.php                # Login page
│   ├── lost_item.php            # Lost items (student)
│   ├── lost-item-form.php       # Report lost item
│   ├── navigation-admin.php     # Admin navigation
│   ├── navigation.php           # Student navigation
│   ├── notifications.php        # Notification center
│   ├── profile.php              # User profile
│   ├── register.php             # Registration
│   ├── reschedule-request.php   # Request reschedule
│   ├── reschedule-requests-admin.php # Admin reschedule
│   ├── reset-password.php       # Reset password
│   ├── scheduled-complaints.php # Scheduled sessions
│   ├── set-schedule.php         # Set time slots
│   ├── staff-dashboard.php      # Admin dashboard
│   ├── student_dashboard.php    # Student dashboard
│   ├── students-list.php        # Student management
│   └── view-lost-item.php       # Lost item details
├── .gitignore                    # Git ignore rules
├── .htaccess                     # Apache configuration
├── config.php                    # Main configuration (excluded from git)
├── config.example.php            # Configuration template
├── index.php                     # Landing page
├── README.md                     # Project readme
├── guidancesystem.sql            # Database schema
├── guidancesystem_final.sql      # Final database
├── guidancesystem_updated.sql    # Updated schema
├── seed_users.sql                # Sample data
├── ANONYMOUS_SUGGESTIONS_FEATURE.md # Feature docs
├── CONFIG_SETUP.md               # Configuration guide
├── CONFIGURATION_MIGRATION.md    # Migration guide
└── QUICK_CONFIG_REFERENCE.md     # Quick reference
```

---

## 🔐 Security Features

### Authentication
- ✅ Secure password hashing (bcrypt)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Login attempt limiting
- ✅ Session timeout
- ✅ Password reset via email

### Data Protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ CSRF protection (configurable)
- ✅ File upload validation
- ✅ MIME type checking
- ✅ Sensitive file blocking

### Configuration Security
- ✅ Centralized credential management
- ✅ Config file excluded from version control
- ✅ Environment-based configuration
- ✅ API key protection
- ✅ Database credential protection

### Server Security
- ✅ Security headers (X-Frame-Options, etc.)
- ✅ Directory listing disabled
- ✅ Error page handling (404, 403)
- ✅ HTTPS ready
- ✅ File permission controls

---

## 📊 Database Schema

### Core Tables

#### users
- User authentication and roles
- Fields: id, email, password, role, contact_number, created_at

#### students
- Student information
- Fields: id, user_id, first_name, last_name, grade_level, section, phone_number, profile_photo

#### parents
- Parent contact information
- Fields: id, student_id, parent_name, contact_number, relationship

#### complaints_concerns
- Complaint management
- Fields: id, student_id, type, severity, description, evidence, status, scheduled_date, scheduled_time, date_created

#### lost_items
- Lost & found management
- Fields: id, student_id, item_name, description, photo, status, location_lost, date_lost, receive_sms, phone_number

#### reschedule_requests
- Session rescheduling
- Fields: id, complaint_id, student_id, current_date, requested_date, reason, status

#### notifications
- In-app notifications
- Fields: id, user_id, type, message, is_read, created_at

#### anonymous_suggestions
- Anonymous suggestion box
- Fields: id, suggestion, submitted_at, is_read

#### activity_logs
- System activity tracking
- Fields: id, user_id, action, details, timestamp

---

## 🚀 Key Functionalities

### Smart Matching System
- AI-powered lost item matching
- Similarity scoring algorithm
- Automatic match suggestions
- Admin review and approval

### Voice-to-Text
- Web Speech API integration
- Real-time transcription
- Complaint description input
- Browser compatibility handling

### Real-time Notifications
- In-app notification system
- Unread count badges
- Auto-refresh functionality
- Role-based filtering

### SMS Integration
- Automated parent notifications
- Student alerts
- Admin reminders
- Bulk SMS support
- Delivery tracking

### Analytics Dashboard
- Chart.js visualizations
- Real-time statistics
- Complaint trends
- Session analytics
- Lost item metrics

### Responsive Design
- Mobile-first approach
- Tablet optimization
- Desktop layouts
- Touch-friendly interfaces
- Adaptive navigation

---

## 📱 User Roles & Permissions

### Student Role
- ✅ Submit complaints/concerns
- ✅ Report lost items
- ✅ View own submissions
- ✅ Request reschedule
- ✅ Receive notifications
- ✅ Update profile
- ✅ View dashboard

### Admin/Staff Role
- ✅ All student permissions
- ✅ View all complaints
- ✅ Schedule counseling sessions
- ✅ Manage lost items
- ✅ Approve reschedule requests
- ✅ View all students
- ✅ Access analytics
- ✅ Manage suggestions
- ✅ Send SMS notifications
- ✅ View activity logs

### Public (Unauthenticated)
- ✅ View landing page
- ✅ Submit anonymous suggestions
- ✅ Register account
- ✅ Login
- ✅ Password recovery

---

## 🔧 Configuration & Setup

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Apache**: 2.4+ with mod_rewrite
- **PHP Extensions**: PDO, MySQLi, cURL, GD, mbstring
- **Disk Space**: 500MB minimum
- **Memory**: 128MB PHP memory limit

### Installation Steps
1. Clone repository
2. Copy `config.example.php` to `config.php`
3. Configure database credentials
4. Add Semaphore API key
5. Import database schema
6. Set file permissions
7. Configure Apache virtual host
8. Test installation

### Environment Configuration
- **Development**: Full error reporting, debug mode
- **Staging**: Limited error reporting, testing mode
- **Production**: Error logging only, optimized settings

---

## 📈 Performance Features

### Optimization
- Lazy loading images
- Minified CSS/JS (CDN)
- Database query optimization
- Prepared statements caching
- Session management
- Efficient file uploads

### Caching
- Browser caching headers
- Static asset caching
- Database query results
- Session data

---

## 🎨 Design Features

### UI/UX
- Modern, clean interface
- Consistent color scheme (Maroon #800000)
- Smooth animations
- Intuitive navigation
- Accessibility considerations
- Loading states
- Error handling
- Success feedback

### Responsive Breakpoints
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

---

## 📝 Documentation

### Available Documentation
- ✅ System Summary (this file)
- ✅ Configuration Setup Guide
- ✅ Configuration Migration Guide
- ✅ Quick Configuration Reference
- ✅ Anonymous Suggestions Feature
- ✅ Database Schema
- ✅ API Documentation (inline)

---

## 🔄 Version Control

### Git Configuration
- Repository initialized
- .gitignore configured
- Sensitive files excluded
- Branch management ready

### Excluded Files
- config.php
- .env files
- uploads/
- logs/
- vendor/
- IDE files

---

## 🌟 Unique Features

1. **Smart Lost Item Matching** - AI-powered similarity detection
2. **Voice-to-Text Input** - Hands-free complaint submission
3. **Anonymous Suggestion Box** - Rate-limited public feedback
4. **Automated SMS Notifications** - Parent and student alerts
5. **Real-time Dashboard** - Live statistics and charts
6. **Photo Verification** - Claimant identity verification
7. **Flexible Scheduling** - Time slot management
8. **Activity Logging** - Complete audit trail
9. **Responsive Design** - Works on all devices
10. **Centralized Configuration** - Secure credential management

---

## 🎓 Educational Context

### Capstone Project
- Demonstrates full-stack development skills
- Real-world problem solving
- Industry-standard practices
- Professional documentation
- Security awareness
- User-centered design

### Learning Outcomes
- PHP backend development
- MySQL database design
- Frontend development (HTML/CSS/JS)
- API integration
- Security implementation
- Project management
- Documentation skills

---

## 📞 Support & Maintenance

### System Monitoring
- Error logging
- Activity tracking
- SMS delivery monitoring
- Database performance
- User activity analytics

### Maintenance Tasks
- Regular backups
- Security updates
- API key rotation
- Database optimization
- Log file management
- User account management

---

## 🏆 Project Highlights

✨ **Comprehensive Solution** - Complete guidance management system  
✨ **Modern Tech Stack** - Latest web technologies  
✨ **Security First** - Multiple security layers  
✨ **User-Friendly** - Intuitive interface design  
✨ **Scalable** - Ready for growth  
✨ **Well-Documented** - Extensive documentation  
✨ **Production-Ready** - Deployment-ready code  
✨ **Mobile-Responsive** - Works everywhere  
✨ **Feature-Rich** - Multiple integrated systems  
✨ **Professional** - Industry-standard practices  

---

## 📄 License & Credits

### Project Information
- **Project Name**: EMEMHS Guidance System
- **Type**: Capstone Project
- **Institution**: EMEMHS (Emilio M. Espinosa Memorial High School)
- **Purpose**: Educational / School Management

### Third-Party Services
- Semaphore SMS API
- EmailJS
- Tailwind CSS
- Chart.js
- Font Awesome
- Google Fonts

---

**Last Updated**: February 2025  
**Version**: 1.0  
**Status**: Production Ready
