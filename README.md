# Wyoming Trust Platform - Full Stack Implementation

## Overview
Complete full-stack implementation of the Wyoming Trust platform with PHP backend, MySQL database, admin dashboard, user dashboard, and integrated onboarding system.

## Quick Start

### 1. Database Setup
- Import `database/wyomingtrust.sql` into phpMyAdmin or your MySQL server
- The schema includes all necessary tables with default data

### 2. Configuration
- Update `api/config.php` with your database credentials
- Configure SMTP settings for email functionality
- Set encryption key in `api/config.php` (minimum 32 characters)

### 3. Admin Access
- Default admin email: `admin@wyomingtrust.com`
- Use `dashboard/admin/reset-admin-password.php` to reset admin password after setup

## Project Structure

```
/
├── api/                    # Backend API endpoints
│   ├── config.php          # Database connection & configuration
│   ├── helpers.php         # Helper functions (encryption, validation)
│   ├── session.php         # Session management
│   ├── email.php           # Email sending functionality (PHPMailer)
│   ├── login.php           # User login
│   ├── register.php        # User registration
│   ├── verify-email.php    # Email verification
│   ├── pricing.php         # Public pricing API
│   ├── trust-services.php  # Public trust services API
│   ├── admin/              # Admin API endpoints
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── session.php
│   │   ├── password.php
│   │   ├── users.php
│   │   ├── trusts.php
│   │   ├── pricing.php
│   │   ├── settings.php
│   │   └── payments.php
│   └── user/               # User API endpoints
│       ├── profile.php
│       ├── wallets.php     # Encrypted wallet linking
│       ├── trusts.php
│       ├── assets.php
│       ├── transactions.php
│       ├── send.php        # Crypto send
│       ├── receive.php     # Crypto receive
│       ├── swap.php        # Crypto swap
│       └── resend-verification.php
├── includes/               # Shared components
│   ├── header.php          # Site header with navigation
│   └── footer.php          # Site footer
├── database/
│   └── wyomingtrust.sql    # Database schema
├── dashboard/
│   ├── admin/              # Admin dashboard
│   │   ├── index.php       # Main admin dashboard with settings
│   │   ├── login.php       # Admin login page
│   │   └── reset-admin-password.php
│   └── user/               # User dashboard
│       └── dashboard.php
├── onboarding/
│   └── onboarding.php      # Unified onboarding flow
├── PHPMailer/              # PHPMailer library
├── index.php               # Homepage (dynamic)
├── login.php               # Login page
├── pricing.php             # Pricing page (dynamic from database)
├── verify-status.php       # Email verification status page
└── .htaccess               # Security configuration
```

## Key Features

### Backend
- ✅ Secure database connection with PDO
- ✅ Password hashing using `password_hash()`
- ✅ Wallet data encryption (AES-256-CBC)
- ✅ Session management with secure cookies
- ✅ CSRF protection
- ✅ Input validation and sanitization
- ✅ Email verification system with PHPMailer
- ✅ Admin and user authentication

### Admin Dashboard
- ✅ Admin login/logout/session
- ✅ User management (CRUD, direct password reset)
- ✅ Trust services management (CRUD, free/paid toggle)
- ✅ Pricing plans management (CRUD, dynamic frontend display)
- ✅ Payment methods management
- ✅ Site settings (email verification toggle, site name, tagline)

### User Features
- ✅ User registration with email verification
- ✅ User login/logout
- ✅ Profile management
- ✅ Encrypted wallet linking
- ✅ Trust creation and management
- ✅ Crypto asset management (send, receive, swap)
- ✅ Transaction history

### Frontend
- ✅ Shared header with dropdown navigation
- ✅ Shared footer with menu structure
- ✅ Dynamic homepage matching design
- ✅ Dynamic pricing page (fetches from database)
- ✅ Login page with email verification flow
- ✅ Email verification status page with resend functionality

## Email Verification System

### Features
- PHPMailer integration with branded email templates
- Email verification required before dashboard access (admin configurable)
- 60-second cooldown on resend verification emails
- Visual countdown timer and progress bar
- Professional HTML email templates with site branding

### Configuration
Set SMTP settings in `api/config.php`:
- `SMTP_HOST` - SMTP server hostname
- `SMTP_PORT` - SMTP port (usually 587 or 465)
- `SMTP_USERNAME` - SMTP username
- `SMTP_PASSWORD` - SMTP password
- `SMTP_ENCRYPTION` - 'tls' or 'ssl'
- `SMTP_FROM_EMAIL` - From email address
- `SMTP_FROM_NAME` - From name

### Admin Control
Admins can enable/disable email verification:
- Go to `dashboard/admin/index.php`
- Navigate to "Site Settings" section
- Toggle "Email Verification" switch
- Changes apply immediately to new registrations

## Dynamic Pricing

### How It Works
1. Admin updates pricing in admin dashboard
2. Changes saved to `pricing_plans` database table
3. Frontend `pricing.php` fetches from `api/pricing.php` on page load
4. Pricing plans displayed dynamically with features

### API Endpoints
- **Public**: `GET /api/pricing.php` - Returns active pricing plans
- **Admin**: `PATCH /api/admin/pricing.php` - Update pricing plans (requires auth)

## Security Features

- Password hashing using `password_hash()` with `PASSWORD_DEFAULT`
- Prepared statements for all SQL queries (SQL injection prevention)
- CSRF token protection available
- Secure session configuration (httponly, secure cookies)
- Wallet data encryption with AES-256-CBC
- Input validation and sanitization
- XSS protection via HTML escaping
- Admin authentication required for admin endpoints

## Database Schema

### Key Tables
- `users` - User accounts with email verification
- `admins` - Admin accounts
- `trust_services` - Available trust service types
- `pricing_plans` - Dynamic pricing plans
- `user_trusts` - User-created trusts
- `linked_wallets` - Encrypted wallet information
- `user_assets` - User crypto asset balances
- `transactions` - Transaction history
- `payment_methods` - Payment gateway configuration
- `site_settings` - Site configuration (email verification toggle, etc.)
- `coins` - Available cryptocurrencies

## API Endpoints

All API endpoints return JSON responses with `success` and `message` fields.

### Error Responses
- 400: Bad Request (invalid input)
- 401: Unauthorized (not logged in)
- 403: Forbidden (email not verified, CSRF token invalid)
- 404: Not Found
- 405: Method Not Allowed
- 409: Conflict (duplicate entry)
- 429: Too Many Requests (cooldown active)
- 500: Internal Server Error

## Important Notes

1. **Encryption Key**: Change the default encryption key in `api/config.php` for production
2. **SMTP Configuration**: Required for email verification to work
3. **Admin Password**: Reset immediately after setup using `dashboard/admin/reset-admin-password.php`
4. **Database Credentials**: Update in `api/config.php` (direct configuration or .env file)
5. **Documentation Files**: .md files are blocked from public access via .htaccess

## Testing Checklist

### Email Verification
- [x] PHPMailer integration works
- [x] Email template displays correctly with branding
- [x] Verification email sends successfully
- [x] Resend email enforces 60-second cooldown
- [x] Countdown timer works correctly
- [x] Unverified users cannot access dashboard
- [x] Verified users can access dashboard

### Dynamic Pricing
- [x] Admin can create/update/delete pricing plans
- [x] Frontend displays active plans from database
- [x] Frontend shows updated prices after admin changes
- [x] Loading and error states work correctly

### Security
- [x] SQL injection protection via prepared statements
- [x] XSS protection via HTML escaping
- [x] Password hashing secure
- [x] Wallet encryption working
- [x] Admin authentication enforced

## File Security

All `.md` documentation files are blocked from public web access via `.htaccess`. They are for internal development use only.

## Support

For issues or questions, refer to the codebase structure and inline comments. All security-critical functions include error logging for debugging.
