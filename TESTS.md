# Build, Deploy & Test Strategy

This document outlines the comprehensive build, deploy, and test strategy for the Mini CMS application built with CakePHP 5.

## <× **Build Process**

### **Development Setup**
```bash
# Clone and setup
git clone <your-repo>
cd cms

# Start DDEV environment
ddev start

# Install dependencies
ddev composer install

# Run database migrations
ddev exec bin/cake migrations migrate

# Seed demo data
ddev exec bin/cake migrations seed --seed DemoDataSeed
```

### **Static Analysis & Code Quality**
```bash
# Check coding standards
ddev exec vendor/bin/phpcs --standard=phpcs.xml src/

# Fix coding standards automatically
ddev exec vendor/bin/phpcbf --standard=phpcs.xml src/

# Run static analysis
ddev exec vendor/bin/phpstan analyse --level=5

# Combined quality check
ddev exec vendor/bin/phpcs src/ && ddev exec vendor/bin/phpstan analyse
```

## >ê **Testing Strategy**

### **Unit & Integration Tests**
```bash
# Run complete test suite
ddev exec vendor/bin/phpunit

# Run specific test categories
ddev exec vendor/bin/phpunit tests/TestCase/Controller/AuthControllerTest.php
ddev exec vendor/bin/phpunit tests/TestCase/Controller/PostsControllerTest.php
ddev exec vendor/bin/phpunit tests/TestCase/Controller/CommentsControllerTest.php
ddev exec vendor/bin/phpunit tests/TestCase/Controller/NotificationsControllerTest.php

# Run with coverage (if configured)
ddev exec vendor/bin/phpunit --coverage-html coverage/
```

### **Test Coverage Areas**

#### **Authentication Tests** (AuthControllerTest.php):
-  Complete registration’verify’login happy path
-  Cannot login if not verified (unverified users redirect to login with error)
-  Duplicate email/username validation edge cases
-  Password reset flow with token validation
-  Email verification with token
-  Login/logout functionality

#### **Posts Tests** (PostsControllerTest.php):
-  Create/list/view posts with proper authentication checks
-  Only owner can edit/delete posts (with admin override)
-  Post validation (title/body required, length limits)
-  @mention processing creates notifications for mentioned users

#### **Comments Tests** (CommentsControllerTest.php):
-  Add comments with authentication requirements
-  Only owner can delete comments (with admin override)
-  Comment validation (body required, length limits)
-  @mention processing in comments creates notifications
-  Comment notifications - post authors get notified when someone comments

#### **Notifications Tests** (NotificationsControllerTest.php):
-  Mark-as-read functionality with ownership validation
-  Mention notifications created correctly
-  Comment-on-post notifications created correctly
-  Notification privacy - users only see their own notifications
-  Newest-first ordering and unread count display

### **Manual Testing Checklist**
```bash
# 1. Authentication Flow
# - Register new user ’ Check email verification
# - Verify email ’ Login succeeds
# - Try login without verification ’ Should fail

# 2. Post Management
# - Create post with @mentions ’ Check notifications created
# - Edit own post ’ Should work
# - Try edit other's post ’ Should fail (unless admin)

# 3. Comment System
# - Add comment to post ’ Post author gets notification
# - Add comment with @mention ’ Mentioned user gets notification
# - Delete own comment ’ Should work

# 4. Notification System
# - View notifications ’ Shows newest first
# - Mark as read ’ Status updates
# - Try access other user's notifications ’ Should fail
```

## =€ **Deployment Options**

### **1. Traditional LAMP Stack**
```bash
# Production requirements
- PHP 8.2+
- MySQL 8.0+
- Apache/Nginx
- Composer

# Deployment steps
1. Upload code to server
2. Run: composer install --no-dev --optimize-autoloader
3. Configure database in config/app_local.php
4. Run: bin/cake migrations migrate
5. Set proper file permissions
6. Configure web server virtual host
```

### **2. Docker Deployment**
```dockerfile
# Dockerfile example
FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application
COPY . /var/www/html/
COPY config/app_local.example.php config/app_local.php

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
```

### **3. Cloud Platform (Heroku-style)**
```bash
# For platforms like Heroku, Railway, etc.
echo "web: vendor/bin/heroku-php-apache2 webroot/" > Procfile

# Environment variables needed:
DATABASE_URL=mysql://user:pass@host:port/dbname
APP_NAME="Mini CMS"
APP_DEBUG=false
SECURITY_SALT="your-random-salt"
SMTP_HOST=smtp.mailgun.org
SMTP_PORT=587
SMTP_USER=your-smtp-user
SMTP_PASS=your-smtp-password
```

## =ç **Email Configuration**

### **Development (Mailpit)**
```bash
# Already configured in DDEV
# Access: https://cms.ddev.site:8026
# All emails caught locally
```

### **Production (SMTP)**
```php
// config/app_local.php
'EmailTransport' => [
    'default' => [
        'className' => 'Smtp',
        'host' => env('SMTP_HOST', 'smtp.gmail.com'),
        'port' => env('SMTP_PORT', 587),
        'username' => env('SMTP_USER'),
        'password' => env('SMTP_PASS'),
        'tls' => true,
    ],
],
```

## =' **CI/CD Pipeline Example**

### **GitHub Actions**
```yaml
# .github/workflows/ci.yml
name: CI/CD
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_cms
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        
    - name: Install dependencies
      run: composer install
      
    - name: Run migrations
      run: bin/cake migrations migrate
      
    - name: Run tests
      run: vendor/bin/phpunit
      
    - name: Check coding standards
      run: vendor/bin/phpcs src/
      
    - name: Static analysis
      run: vendor/bin/phpstan analyse
```

## = **Health Checks & Monitoring**

### **Application Health Endpoint**
```php
// Add to routes.php
$routes->connect('/health', [
    'controller' => 'Health', 
    'action' => 'check'
]);

// Create HealthController
public function check() {
    $checks = [
        'database' => $this->checkDatabase(),
        'email' => $this->checkEmail(),
        'storage' => $this->checkStorage(),
    ];
    
    $this->set([
        'status' => 'ok',
        'checks' => $checks,
        '_serialize' => ['status', 'checks']
    ]);
}
```

### **Performance Testing**
```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://your-cms-domain.com/

# Or use Artillery.io
artillery quick --count 10 --num 100 https://your-cms-domain.com/
```

## =Ê **Production Monitoring**

### **Log Monitoring**
```bash
# Monitor CakePHP logs
tail -f logs/error.log
tail -f logs/debug.log

# Set up log rotation
# Add to logrotate.d/cakephp
/path/to/cms/logs/*.log {
    daily
    rotate 7
    compress
    missingok
    notifempty
}
```

### **Key Metrics to Monitor**
- Response time (< 200ms for cached pages)
- Database query performance
- Memory usage
- Email delivery success rate
- User registration/login success rate
- Notification delivery rate

## <Á **Definition of Done Checklist**

Based on SPEC.md Section 13, ensure all items are completed:

-  All routes implemented with CSRF & FormProtection
-  DB migrations applied; seeds create 3 demo users + 5 posts + comments
-  Static analysis (phpstan level 5+) clean; phpcs passes; test suite green
-  Basic 404/403 handling and flash messages
-  README updated with setup & DDEV commands

## =¨ **Pre-Production Checklist**

1. **Security**
   - [ ] All forms have CSRF protection
   - [ ] All user inputs are validated and sanitized
   - [ ] Authentication middleware properly configured
   - [ ] Authorization policies enforced
   - [ ] No sensitive data in logs

2. **Performance**
   - [ ] Database queries optimized
   - [ ] Proper indexes on all foreign keys
   - [ ] Assets minified and compressed
   - [ ] Caching configured appropriately

3. **Monitoring**
   - [ ] Error logging configured
   - [ ] Health check endpoint working
   - [ ] Email delivery monitoring in place
   - [ ] Database backup strategy implemented

4. **Testing**
   - [ ] All tests passing
   - [ ] Code coverage > 80%
   - [ ] Manual testing completed
   - [ ] Load testing performed

This comprehensive strategy ensures your Mini CMS is production-ready with proper quality gates, monitoring, and deployment flexibility!