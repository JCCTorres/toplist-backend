# TopList Orlando Deployment Guide

## Overview

This guide walks you through deploying the TopList Orlando vacation rental website. The application consists of two parts:

1. **React Frontend** - The user-facing website (what visitors see)
2. **Laravel Backend** - The API server (handles data and business logic)

**Architecture:** Laravel serves both the API and the React frontend. The React app is built into static files and placed in Laravel's `public/` directory. This "same-origin" setup simplifies deployment and avoids CORS issues.

**Hosting:** We use Cloudways, a managed cloud hosting platform that handles server maintenance, security updates, and backups.

---

## Prerequisites Checklist

Before starting, ensure you have:

- [ ] Access to the project code (GitHub repository or local files)
- [ ] A Cloudways account (we'll create one if needed)
- [ ] A domain name (optional but recommended)
- [ ] The Laravel API credentials and Bookerville API keys
- [ ] About 30-60 minutes for the initial setup

---

## Step 1: Create Cloudways Account and Server

### 1.1 Create Account

1. Go to [cloudways.com](https://www.cloudways.com)
2. Click "Start Free" or "Sign Up"
3. Fill in your details and verify your email
4. Add a payment method (they offer a free trial)

### 1.2 Create a Server

1. In the Cloudways dashboard, click "Launch" or "Add Server"
2. Choose these settings:
   - **Application:** PHP (Laravel)
   - **Server Size:** Start with 1GB RAM ($11/month) - you can upgrade later
   - **Cloud Provider:** DigitalOcean (recommended for cost)
   - **Server Location:** Choose closest to your users (e.g., Miami for Orlando guests)
3. Click "Launch Now" and wait 5-10 minutes for provisioning

### 1.3 Note Your Credentials

After the server is ready, Cloudways provides:
- **Server IP Address:** (e.g., 167.99.225.xxx)
- **SSH/SFTP Username:** (usually "master")
- **SSH/SFTP Password:** (copy and save this securely)
- **MySQL Credentials:** (database name, user, password)

Save all these credentials securely - you'll need them!

---

## Step 2: Create Laravel Application

### 2.1 Add Application

1. In Cloudways, click your server, then "Applications"
2. Click "Add Application"
3. Select "PHP" as the application type
4. Choose a name (e.g., "toplist-orlando")
5. Click "Add Application"

### 2.2 Access Application Details

1. Click on your new application
2. Note the "Application URL" (temporary Cloudways URL)
3. Go to "Access Details" tab - save:
   - Application path (e.g., `/home/master/applications/toplist-orlando/public_html`)
   - Database name, user, and password

---

## Step 3: Upload Code to Server

### 3.1 Connect via SFTP

1. Download and install [FileZilla](https://filezilla-project.org/) (free SFTP client)
2. Open FileZilla and enter:
   - **Host:** Your server IP (sftp://167.99.225.xxx)
   - **Username:** Your SSH username
   - **Password:** Your SSH password
   - **Port:** 22
3. Click "Quickconnect"

### 3.2 Upload Laravel Backend

1. Navigate to the application folder: `applications/your-app-name/public_html/`
2. Delete any existing files (default Laravel installation)
3. On your local computer, find the `control_toplist-homolog` folder
4. Upload ALL contents of `control_toplist-homolog` to `public_html/`
5. Wait for upload to complete (may take 10-20 minutes)

### 3.3 Set Permissions

Connect via SSH (Cloudways provides SSH access in the dashboard) and run:

```bash
cd ~/applications/your-app-name/public_html
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

## Step 4: Configure Environment Variables

### 4.1 Create .env File

1. In FileZilla, navigate to `public_html/`
2. Find the `.env.example` file
3. Download it to your computer
4. Rename it to `.env`
5. Edit it with your actual values (see below)
6. Upload the `.env` file back to `public_html/`

### 4.2 Required Environment Variables

Edit your `.env` file with these values:

```env
# Application Settings
APP_NAME="TopList Orlando"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_KEY
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (from Cloudways Application details)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Bookerville API Configuration
BOOKERVILLE_API_KEY=your_bookerville_api_key
BOOKERVILLE_API_URL=https://app.bookerville.com/api/v2
BOOKERVILLE_CLIENT_ID=your_client_id

# Mail Configuration (for contact forms)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="TopList Orlando"
```

### 4.3 Generate Application Key

Connect via SSH and run:

```bash
cd ~/applications/your-app-name/public_html
php artisan key:generate
```

This automatically updates your `.env` file with a secure key.

### 4.4 Environment Variables Explained

| Variable | Description |
|----------|-------------|
| APP_KEY | Security key for encryption (auto-generated) |
| APP_DEBUG | Set to `false` in production (hides errors from users) |
| APP_URL | Your public domain (with https://) |
| DB_* | Database credentials from Cloudways |
| BOOKERVILLE_* | API credentials from Bookerville dashboard |
| MAIL_* | Email service for sending contact form messages |

---

## Step 5: Build and Deploy React Frontend

### 5.1 Build the React App Locally

On your local computer:

1. Open a terminal/command prompt
2. Navigate to the `toplist-main` folder
3. Run these commands:

```bash
npm install          # Install dependencies (first time only)
npm run build        # Create production build
```

4. This creates a `dist/` folder with the built files

### 5.2 Upload Built Files to Laravel

1. In FileZilla, navigate to `public_html/public/`
2. Upload the entire contents of your local `dist/` folder
3. The structure should look like:
   ```
   public_html/
     public/
       index.html
       assets/
       images/
       ...
   ```

### 5.3 Configure Index File

If your `index.html` ends up in `public/`, rename it or adjust Laravel's routing to serve it (see Step 6).

---

## Step 6: Configure Laravel to Serve React

### 6.1 Create Catch-All Route

Laravel needs to serve the React app for all non-API routes.

1. Edit `routes/web.php` and add at the end:

```php
// Serve React app for all non-API routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
```

2. Save and upload the file

### 6.2 Clear Laravel Cache

Connect via SSH and run:

```bash
cd ~/applications/your-app-name/public_html
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 7: Point Domain and Enable SSL

### 7.1 Add Domain in Cloudways

1. In Cloudways, go to your Application > Domain Management
2. Click "Add Domain"
3. Enter your domain (e.g., `toplistorlando.com`)
4. Also add `www.toplistorlando.com`

### 7.2 Update DNS Settings

At your domain registrar (GoDaddy, Namecheap, etc.):

1. Go to DNS settings
2. Create/update an A record:
   - **Type:** A
   - **Name:** @ (or leave blank)
   - **Value:** Your Cloudways server IP
   - **TTL:** 3600 (or default)
3. Create another A record for www:
   - **Type:** A
   - **Name:** www
   - **Value:** Your Cloudways server IP

4. Wait for DNS propagation (can take up to 48 hours, usually 1-2 hours)

### 7.3 Enable Free SSL

1. In Cloudways, go to your Application > SSL Certificate
2. Click "Let's Encrypt"
3. Enter your domain and email
4. Click "Install Certificate"
5. Enable "Force HTTPS Redirect"

---

## Step 8: Verify Deployment

### 8.1 Checklist

Test each of these:

- [ ] **Homepage loads:** Visit your domain, see the hero section with search bar
- [ ] **Properties page:** Click "View All Properties", see property cards
- [ ] **Property details:** Click a property, see details, images, calendar
- [ ] **Search works:** Use search bar, results appear
- [ ] **Date picker:** Select dates on property details, unavailable dates blocked
- [ ] **Contact form:** Submit a test message
- [ ] **Mobile view:** Test on phone or resize browser
- [ ] **SSL working:** Check for padlock icon in browser

### 8.2 Quick Tests

```
https://your-domain.com                  # Homepage
https://your-domain.com/properties       # Properties listing
https://your-domain.com/properties/123   # Property details
https://your-domain.com/api/properties   # API endpoint (should return JSON)
```

---

## Troubleshooting

### "500 Internal Server Error"

1. Check Laravel logs: `storage/logs/laravel.log`
2. Ensure `.env` file exists with correct values
3. Verify file permissions on `storage/` and `bootstrap/cache/`
4. Run: `php artisan config:clear`

### "Page Not Found" for React routes

1. Ensure the catch-all route is in `routes/web.php`
2. Clear route cache: `php artisan route:clear`
3. Verify `index.html` is in `public/` directory

### "CORS Error" in browser console

1. Check that React is served from same domain as API
2. Verify `APP_URL` in `.env` matches your domain
3. Clear browser cache and try again

### "Cannot connect to database"

1. Verify database credentials in `.env`
2. Check database server is running in Cloudways
3. Ensure DB_HOST is `127.0.0.1` (not `localhost` on some servers)

### Images not loading

1. Check image paths are relative, not absolute
2. Verify images were uploaded to `public/images/`
3. Check file permissions

### API returning errors

1. Check Bookerville API credentials
2. Verify API key hasn't expired
3. Check Laravel logs for specific error messages

---

## Security Reminders

1. **Never commit `.env` to git** - Contains passwords and API keys
2. **Keep APP_DEBUG=false** in production - Prevents exposing sensitive info
3. **Enable SSL** - Always use HTTPS
4. **Regular backups** - Cloudways does automatic backups, but verify they're enabled
5. **Update regularly** - Keep Laravel and packages updated for security patches
6. **Strong passwords** - Use unique, strong passwords for all accounts

---

## Maintenance

### How to Update the React Frontend

1. Make changes locally
2. Run `npm run build`
3. Upload new `dist/` contents to `public_html/public/`
4. Clear browser cache

### How to Update the Laravel Backend

1. Upload changed files via SFTP
2. Run via SSH:
   ```bash
   php artisan migrate        # If database changes
   php artisan config:cache   # Refresh config
   php artisan route:cache    # Refresh routes
   ```

### How to Check Logs

```bash
# Laravel logs
tail -f ~/applications/your-app/public_html/storage/logs/laravel.log

# Nginx logs
tail -f ~/applications/your-app/nginx_logs/error.log
```

### Backup Before Major Changes

1. In Cloudways, go to Backup
2. Click "Take Backup Now"
3. Wait for backup to complete
4. Then make your changes

---

## Quick Reference

| Task | Command/Location |
|------|-----------------|
| Clear all cache | `php artisan optimize:clear` |
| View Laravel logs | `storage/logs/laravel.log` |
| Application path | `/home/master/applications/your-app/public_html/` |
| Public files | `/home/master/applications/your-app/public_html/public/` |
| React build | Local: `npm run build` |
| Upload files | Use FileZilla with SFTP |

---

## Support

If you encounter issues not covered here:

1. Check Laravel documentation: [laravel.com/docs](https://laravel.com/docs)
2. Cloudways support: Use the chat in your dashboard
3. Check browser console for JavaScript errors (F12 > Console)

---

*Last updated: February 2026*
