# EMEMHS Guidance System

**EMEMHS EDUCARE GUIDANCE SYSTEM WITH SMS NOTIFICATION FOR PARENTS**

A comprehensive web-based guidance management system for EMEMHS (Emilio M. Espinosa Memorial High School) that streamlines counseling services, complaint management, lost & found items, and parent communication through automated SMS notifications.

## 🎯 Overview

This capstone project provides a complete digital solution for school guidance services, featuring:
- Student complaint and concern management
- Lost and found item tracking with smart matching
- Automated SMS notifications for parents and students
- Anonymous suggestion box
- Real-time dashboards and analytics
- Counseling session scheduling and management

## ✨ Key Features

### For Students
- 📝 Submit complaints/concerns with evidence upload
- 🎤 Voice-to-text input support
- 📦 Report lost items with photo upload
- 📅 Request counseling session reschedules
- 🔔 Real-time notifications
- 📊 Personal dashboard

### For Admin/Staff
- 👥 Manage all student complaints and concerns
- 📅 Schedule counseling sessions
- 🔍 Smart lost item matching system
- 📱 Send automated SMS notifications
- 📈 Analytics and reporting dashboard
- 💬 View anonymous suggestions
- 👨‍🎓 Student management

### For Parents
- 📲 Receive SMS notifications when student submits complaints
- 📅 Get counseling session reminders
- ℹ️ Stay informed about student concerns

## 🛠️ Technology Stack

### Frontend
- HTML5, CSS3, JavaScript (ES6+)
- Tailwind CSS 3.x
- Chart.js (Analytics)
- Font Awesome 6.0
- Animate.css
- Web Speech API (Voice-to-text)

### Backend
- PHP 7.4+
- MySQL 5.7+ / MariaDB
- PDO & MySQLi
- Session-based authentication

### Third-Party Services
- Semaphore SMS API (Philippine SMS gateway)
- EmailJS (Password reset emails)

### Server
- Apache 2.4+ with mod_rewrite
- XAMPP / WAMP / LAMP compatible

## 📋 System Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ with mod_rewrite enabled
- PHP Extensions: PDO, MySQLi, cURL, GD, mbstring
- 500MB disk space minimum
- 128MB PHP memory limit

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/EMEMHS-Guidance-System.git
cd EMEMHS-Guidance-System
```

### 2. Configure Database
```bash
# Import database schema
mysql -u root -p < guidancesystem.sql

# Or use phpMyAdmin to import the SQL file
```

### 3. Setup Configuration
```bash
# Copy configuration template
copy config.example.php config.php

# Edit config.php with your settings:
# - Database credentials
# - Semaphore API key
# - Application URL
```

### 4. Set Permissions (Linux/Mac)
```bash
chmod 600 config.php
chmod 755 uploads/
chmod 755 logs/
```

### 5. Configure Apache
Ensure mod_rewrite is enabled and .htaccess is allowed.

### 6. Access System
```
http://localhost/EMEMHS-Guidance-System
```

## 📁 Project Structure

```
EMEMHS-Guidance-System/
├── config.php              # Main configuration (create from example)
├── index.php               # Landing page
├── .htaccess              # Apache configuration
├── image/                 # System images
├── js/                    # JavaScript files
├── logic/                 # Backend logic & API
├── pages/                 # Frontend pages
│   ├── admin-*.php       # Admin pages
│   ├── student-*.php     # Student pages
│   └── components/       # Reusable components
└── guidancesystem.sql    # Database schema
```

## 🔐 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection
- ✅ CSRF protection (configurable)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ File upload validation
- ✅ Security headers
- ✅ Centralized credential management

## 📱 User Roles

### Student
- Submit and track complaints/concerns
- Report and claim lost items
- View personal dashboard
- Receive notifications

### Admin/Staff
- All student permissions
- Manage all complaints and lost items
- Schedule counseling sessions
- Send SMS notifications
- Access analytics dashboard
- Manage student accounts

### Public
- View landing page
- Submit anonymous suggestions
- Register new account

## 📚 Documentation

- [System Summary](SYSTEM_SUMMARY.md) - Complete system overview
- [Configuration Setup](CONFIG_SETUP.md) - Detailed setup guide
- [Configuration Migration](CONFIGURATION_MIGRATION.md) - Migration details
- [Quick Reference](QUICK_CONFIG_REFERENCE.md) - Quick config guide
- [Anonymous Suggestions](ANONYMOUS_SUGGESTIONS_FEATURE.md) - Feature docs

## 🧪 Testing

### Test Database Connection
```bash
php logic/test_sms.php
```

### Test SMS Sending
```bash
php logic/test_semaphore.php
```

## 🔧 Configuration

### Database Settings (config.php)
```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'guidancesystem');
```

### Semaphore SMS API (config.php)
```php
define('SEMAPHORE_API_KEY', 'your-api-key-here');
define('SEMAPHORE_SENDER_NAME', 'EMEMHS');
```

Get your API key from: https://semaphore.co/

## 🌟 Key Features Explained

### Smart Lost Item Matching
AI-powered algorithm that suggests potential matches between lost and found items based on description similarity.

### Voice-to-Text Input
Students can dictate their complaints using the Web Speech API for faster submission.

### Anonymous Suggestion Box
Public-facing feature with rate limiting (1 suggestion per 24 hours) using localStorage.

### Automated SMS Notifications
- Parents receive SMS when student submits complaint
- Students get notified when lost items are found
- Admins receive daily summaries and urgent alerts

### Real-time Dashboard
Live statistics and charts showing complaint trends, session schedules, and lost item metrics.

## 🎓 Educational Context

This is a capstone project demonstrating:
- Full-stack web development
- Database design and management
- API integration
- Security best practices
- User experience design
- Professional documentation

## 🤝 Contributing

This is a capstone project. For educational purposes, please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## ⚠️ Important Notes

- Never commit `config.php` to version control
- Keep your Semaphore API key secure
- Regularly backup your database
- Use HTTPS in production
- Set proper file permissions
- Monitor error logs

## 📞 Support

For issues or questions:
- Check the documentation in the `/docs` folder
- Review error logs in `logs/error.log`
- Test connections with provided test files

## 📄 License

This project is created for educational purposes as a capstone project for EMEMHS.

## 🏆 Credits

### Third-Party Services & Libraries
- Semaphore SMS API
- EmailJS
- Tailwind CSS
- Chart.js
- Font Awesome
- Google Fonts
- Animate.css

### Institution
EMEMHS (Emilio M. Espinosa Memorial High School)

---

**Version**: 1.0  
**Status**: Production Ready  
**Last Updated**: February 2025

For complete system documentation, see [SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md)
