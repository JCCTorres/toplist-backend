# TopList Orlando Deployment Guide (Railway)

## Overview

This guide walks you through deploying the TopList Orlando vacation rental website using **Railway**, a modern cloud platform that makes deployment simple.

The application consists of two parts:

1. **React Frontend** — The user-facing website (what visitors see)
2. **Laravel Backend** — The API server (handles data, Bookerville integration, and email)

**Architecture:** Laravel serves both the API and the React frontend from the same domain. The React app is built into static files and placed in Laravel's `public/` directory. This "same-origin" setup avoids CORS issues.

**Why Railway:** Instant account creation, git-push deploys, built-in database, environment variable management, and free tier to start.

---

## Prerequisites Checklist

Before starting, ensure you have:

- [ ] A **GitHub account** (free — [github.com](https://github.com))
- [ ] The project code uploaded to a **GitHub repository** (or ready to upload)
- [ ] Your **Bookerville API credentials** (API key, account/client ID)
- [ ] Your **SMTP email credentials** (for contact forms — Gmail app password works)
- [ ] A **domain name** (optional but recommended)
- [ ] About 30–45 minutes for the initial setup

---

## Step 1: Create a Railway Account

1. Go to [railway.app](https://railway.app)
2. Click **"Login"** → **"Login with GitHub"**
3. Authorize Railway to access your GitHub account
4. You're in — no verification wait, no credit card required for the free tier

> **Free tier:** 500 hours/month, 512 MB RAM, 1 GB disk. Enough for a small site.
> **Pro plan:** $5/month — unlimited hours, more resources. Recommended for production.

---

## Step 2: Prepare the Code for Deployment

### 2.1 Push Laravel Backend to GitHub

If the `control_toplist-homolog` folder isn't already in a GitHub repository:

1. Create a new repository on GitHub (e.g., `toplist-backend`)
2. On your computer, open a terminal in the `control_toplist-homolog` folder
3. Run:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/toplist-backend.git
git push -u origin main
```

### 2.2 Build the React Frontend

On your computer, in the `toplist-main` folder:

```bash
npm install          # Install dependencies (first time only)
npm run build        # Creates the dist/ folder
```

### 2.3 Copy React Build into Laravel

Copy the contents of `toplist-main/dist/` into `control_toplist-homolog/public/`:

- Copy `dist/index.html` → `control_toplist-homolog/public/index.html`
- Copy `dist/assets/` → `control_toplist-homolog/public/assets/`

Then commit and push:

```bash
cd control_toplist-homolog
git add public/index.html public/assets/
git commit -m "Add React production build"
git push
```

---

## Step 3: Deploy Laravel on Railway

### 3.1 Create a New Project

1. In Railway dashboard, click **"New Project"**
2. Select **"Deploy from GitHub repo"**
3. Choose your `toplist-backend` repository
4. Railway will auto-detect it as a PHP/Laravel application

### 3.2 Add a MySQL Database

1. In your project, click **"New"** → **"Database"** → **"MySQL"**
2. Railway creates the database instantly
3. Click on the MySQL service to see connection details

### 3.3 Connect Laravel to the Database

1. Click on your Laravel service
2. Go to **"Variables"** tab
3. Click **"New Variable"** and add a **"Reference"** to the MySQL service:
   - Click **"Add Reference"** → select your MySQL service
   - This auto-creates `DATABASE_URL`, `MYSQLHOST`, `MYSQLPORT`, etc.

---

## Step 4: Configure Environment Variables

In your Laravel service → **"Variables"** tab, add these one by one:

### Required Variables

| Variable | Value | Where to Get It |
|----------|-------|-----------------|
| `APP_NAME` | `TopList Orlando` | Just type it |
| `APP_ENV` | `production` | Just type it |
| `APP_DEBUG` | `false` | Just type it |
| `APP_URL` | `https://your-railway-url.up.railway.app` | From Railway service URL (update later with custom domain) |
| `APP_KEY` | *(see below)* | Auto-generated |

### Database Variables

If Railway auto-created these via reference, skip them. Otherwise add manually:

| Variable | Value | Where to Get It |
|----------|-------|-----------------|
| `DB_CONNECTION` | `mysql` | Just type it |
| `DB_HOST` | *(from MySQL service)* | Railway MySQL → Connect tab |
| `DB_PORT` | *(from MySQL service)* | Railway MySQL → Connect tab |
| `DB_DATABASE` | *(from MySQL service)* | Railway MySQL → Connect tab |
| `DB_USERNAME` | *(from MySQL service)* | Railway MySQL → Connect tab |
| `DB_PASSWORD` | *(from MySQL service)* | Railway MySQL → Connect tab |

### Bookerville API

| Variable | Value | Where to Get It |
|----------|-------|-----------------|
| `BOOKERVILLE_API_KEY` | `your_api_key` | From your existing `.env` file or Bookerville dashboard |
| `BOOKERVILLE_API_URL` | `https://app.bookerville.com/api/v2` | Standard Bookerville URL |
| `BOOKERVILLE_CLIENT_ID` | `your_client_id` | From your existing `.env` file |
| `BOOKERVILLE_ACCOUNT_ID` | `your_account_id` | From your existing `.env` file |

### Email (for Contact/Management forms)

| Variable | Value | Where to Get It |
|----------|-------|-----------------|
| `MAIL_MAILER` | `smtp` | Just type it |
| `MAIL_HOST` | `smtp.gmail.com` | Or your email provider's SMTP |
| `MAIL_PORT` | `587` | Standard for TLS |
| `MAIL_USERNAME` | `your_email@gmail.com` | Your email address |
| `MAIL_PASSWORD` | `your_app_password` | Gmail: Settings → Security → App Passwords |
| `MAIL_ENCRYPTION` | `tls` | Just type it |
| `MAIL_FROM_ADDRESS` | `no-reply@your-domain.com` | Your preferred sender address |
| `MAIL_FROM_NAME` | `TopList Orlando` | Just type it |

### Generate APP_KEY

After adding all variables, you need to generate a Laravel application key:

**Option A (easiest):** In Railway, go to your service → **"Settings"** → scroll to **"Deploy"** section. Open the Railway CLI or use the built-in terminal. Run:

```bash
php artisan key:generate --show
```

Copy the output (starts with `base64:...`) and paste it as the `APP_KEY` variable.

**Option B:** On your local computer (in the Laravel folder):

```bash
php artisan key:generate --show
```

Copy the output and add it as `APP_KEY` in Railway.

---

## Step 5: Configure Laravel to Serve React

### 5.1 Add Catch-All Route

Edit `routes/web.php` in your repository. Add at the **end** of the file:

```php
// Serve React app for all non-API routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '(?!api).*');
```

Commit and push:

```bash
git add routes/web.php
git commit -m "Add catch-all route for React SPA"
git push
```

Railway will auto-deploy when you push.

### 5.2 Create Nixpacks Configuration (if needed)

If Railway doesn't auto-detect PHP correctly, create a `nixpacks.toml` in the project root:

```toml
[phases.setup]
nixPkgs = ["php83", "php83Extensions.pdo_mysql", "php83Extensions.mbstring", "php83Extensions.xml", "php83Extensions.curl"]

[phases.install]
cmds = ["composer install --no-dev --optimize-autoloader"]

[start]
cmd = "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:$PORT -t public"
```

Commit and push this file.

---

## Step 6: Run Database Migrations

In Railway, open the service terminal (or use Railway CLI):

```bash
php artisan migrate --force
```

This creates the necessary database tables.

---

## Step 7: Point Your Domain (Optional)

### 7.1 Get Railway URL

Your app is already live at a Railway-generated URL like:
`https://toplist-backend-production.up.railway.app`

Test it first before adding a custom domain.

### 7.2 Add Custom Domain

1. In Railway, click your service → **"Settings"** → **"Networking"**
2. Click **"Generate Domain"** (if you don't have one yet) or **"Custom Domain"**
3. Enter your domain (e.g., `toplistorlando.com`)
4. Railway shows you the DNS records to add

### 7.3 Update DNS

At your domain registrar (GoDaddy, Namecheap, etc.):

1. Go to DNS settings
2. Add a **CNAME record**:
   - **Name:** `@` or leave blank (for root domain)
   - **Value:** The Railway-provided CNAME target
   - **TTL:** 3600
3. For `www`:
   - **Name:** `www`
   - **Value:** Same Railway CNAME target

4. Wait for DNS propagation (usually 1–2 hours, can take up to 48h)

### 7.4 Enable SSL

Railway provides **free SSL automatically** for custom domains. No action needed — it activates once DNS propagates.

### 7.5 Update APP_URL

After your domain is working, update the `APP_URL` environment variable in Railway to `https://your-domain.com`.

---

## Step 8: Verify Deployment

### Quick Checklist

Test each of these on your live site:

- [ ] **Homepage loads:** Visit your domain, see the hero section with search bar
- [ ] **Properties page:** Navigate to /homes, see property cards with real data
- [ ] **Property details:** Click a property, see images, description, calendar
- [ ] **Search works:** Enter dates in search bar, results filter correctly
- [ ] **Date picker:** Select dates on property detail, unavailable dates are blocked
- [ ] **Book Now:** Click Book Now, redirects to Airbnb (or shows Contact fallback)
- [ ] **Contact form:** Submit a test message, check your email
- [ ] **Management form:** Submit a test inquiry
- [ ] **Mobile view:** Test on your phone or resize browser window
- [ ] **SSL working:** Check for padlock icon in browser address bar
- [ ] **No Resorts:** Confirm Resorts is not in navigation

### Quick Test URLs

```
https://your-domain.com                    # Homepage
https://your-domain.com/homes              # Properties listing
https://your-domain.com/property-details/1 # Property details
https://your-domain.com/services           # Services page
https://your-domain.com/contact            # Contact page
```

---

## Troubleshooting

### "500 Internal Server Error"

1. In Railway, click your service → **"Deployments"** → click latest → check **logs**
2. Common causes:
   - Missing `.env` variables (especially `APP_KEY`)
   - Database connection failed (check DB variables)
   - Missing PHP extensions
3. Fix: Add missing variables, redeploy

### "Page Not Found" for React routes

1. Ensure catch-all route is in `routes/web.php`
2. Verify `index.html` is in `public/` directory
3. Redeploy: push any commit to trigger rebuild

### "CORS Error" in browser console

1. Since we use same-origin serving, this shouldn't happen
2. If it does: verify `APP_URL` matches your actual domain
3. Check `config/cors.php` — set `allowed_origins` to your domain

### "Cannot connect to database"

1. Check MySQL service is running in Railway dashboard
2. Verify DB variables match what Railway provides
3. Try using the `DATABASE_URL` variable instead of individual DB_* vars

### "Bookerville API errors"

1. Check `BOOKERVILLE_API_KEY` is correct
2. Verify `BOOKERVILLE_API_URL` is `https://app.bookerville.com/api/v2`
3. Check Railway logs for specific error messages

### "Contact form not sending"

1. Verify all `MAIL_*` variables are set
2. For Gmail: ensure you're using an **App Password** (not your regular password)
   - Go to Google Account → Security → 2-Step Verification → App Passwords
3. Check Railway logs for mail errors

### Images not loading

1. Verify images are in `public/images/` directory
2. Check that image paths in React use relative paths
3. Rebuild React if image paths changed: `npm run build` → re-upload

---

## Security Reminders

1. **APP_DEBUG=false** — Never set to `true` in production (exposes sensitive info)
2. **Environment variables in Railway** — Never hardcode secrets in code
3. **SSL enabled** — Railway provides this free, always use HTTPS
4. **Strong passwords** — Use unique, strong passwords for all accounts
5. **Bookerville keys** — Keep API credentials only in Railway variables
6. **Regular updates** — Keep Laravel and npm packages updated

---

## Maintenance

### How to Update the React Frontend

1. Make your changes locally in the `toplist-main` folder
2. Run `npm run build`
3. Copy `dist/` contents to `control_toplist-homolog/public/`
4. Commit and push — Railway auto-deploys

```bash
npm run build
cp -r dist/* ../control_toplist-homolog/public/
cd ../control_toplist-homolog
git add public/
git commit -m "Update frontend build"
git push
```

### How to Update the Laravel Backend

1. Make changes to Laravel files
2. Commit and push — Railway auto-deploys

```bash
git add .
git commit -m "Description of changes"
git push
```

### How to Check Logs

1. Railway dashboard → your service → **"Deployments"**
2. Click on the latest deployment
3. View real-time logs

Or use Railway CLI:

```bash
railway logs
```

### How to Rollback

1. Railway dashboard → **"Deployments"**
2. Find the previous working deployment
3. Click **"Rollback"** — instant rollback to any previous deploy

### Database Backups

Railway Pro plan includes automatic database backups. For manual backup:

```bash
railway connect mysql
mysqldump your_database > backup.sql
```

---

## Cost Summary

| Plan | Monthly Cost | Includes |
|------|-------------|----------|
| **Free (Trial)** | $0 | 500 hours, 512 MB RAM, 1 GB disk |
| **Hobby** | $5 | 8 GB RAM, 100 GB disk, unlimited hours |
| **Pro** | $20 | Priority support, team features |

**Recommendation:** Start with Hobby ($5/month) for a production site. Includes enough resources for a small vacation rental site.

MySQL database is included in the plan — no extra cost.

---

## Quick Reference

| Task | How |
|------|-----|
| Deploy code | `git push` (auto-deploys) |
| View logs | Railway dashboard → Deployments → Logs |
| Add env variable | Railway dashboard → Variables → New Variable |
| Rollback | Railway dashboard → Deployments → Rollback |
| Run Laravel commands | Railway dashboard → service terminal |
| Check database | Railway dashboard → MySQL service |
| Update React | `npm run build` → copy to public/ → push |
| Custom domain | Railway → Settings → Networking → Custom Domain |

---

## Support

If you encounter issues not covered here:

1. **Railway docs:** [docs.railway.app](https://docs.railway.app)
2. **Railway Discord:** [discord.gg/railway](https://discord.gg/railway) — active community
3. **Laravel docs:** [laravel.com/docs](https://laravel.com/docs)
4. Check browser console for JavaScript errors (F12 → Console tab)

---

*Last updated: February 2026*
*Platform: Railway (railway.app)*
