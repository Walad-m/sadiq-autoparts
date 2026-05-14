# Sadiq Auto Parts - Railway Deployment Checklist

## Pre-Deployment Preparation ✓
- [x] npm build works successfully
- [x] Created `.railway/nixpacks.toml` for Railway build configuration
- [x] Created `RAILWAY_DEPLOYMENT.md` with complete setup guide
- [x] Created `.env.railway` template with all required variables

## Next: Railway Dashboard Setup
1. Go to https://railway.app
2. Sign up or log in
3. Create new project → Deploy from GitHub
4. Connect GitHub and select this repository
5. Complete setup below

## Railway Project Configuration (DO THESE IN ORDER)

### Step 1: Add MySQL Database Service
- [ ] Click "+ Add Service" in Railway project
- [ ] Select MySQL 8.0
- [ ] Railway auto-generates credentials (${{mysql.MYSQL_USER}}, etc.)
- [ ] Take note of these - you'll reference them in env vars

### Step 2: Configure Web Service Environment Variables
Railway will auto-detect this is a Laravel app. Click the web service and add these variables:

**Required - Replace YOUR VALUES:**
```
APP_KEY=base64:YOUR_APP_KEY_HERE
  └─ Get this by running: php artisan key:generate --show
```

**Auto-populated from MySQL service (use exactly as shown):**
```
DB_HOST=${{mysql.RAILWAY_PRIVATE_URL}}
DB_DATABASE=sadiq
DB_USERNAME=${{mysql.MYSQL_USER}}
DB_PASSWORD=${{mysql.MYSQL_PASSWORD}}
```

**Fixed Values (copy exactly):**
```
APP_ENV=production
APP_DEBUG=false
APP_NAME=SadiqAutoParts
DB_CONNECTION=mysql
CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
MAIL_MAILER=log
LOG_CHANNEL=errorlog
```

### Step 3: Deploy
- [ ] Push code to GitHub (if not already pushed)
- [ ] Railway auto-deploys when you connect the repo
- [ ] Check Railway Deployments tab to see build progress
- [ ] Wait for "Deployment Successful" message

## Post-Deployment Validation

### Check 1: Application Loads
- [ ] Visit Railway-provided URL: https://YOUR_SERVICE.railway.app
- [ ] Should see login page or dashboard
- [ ] No 500 errors

### Check 2: Database Connected
- [ ] Visit /api/health endpoint (if available)
- [ ] Or create a product through the UI (tests database)

### Check 3: All 4 Fixes Work
- [ ] Try creating a sale (Stock Validation)
- [ ] Try creating a second sale (Activity Logging)
- [ ] Try returning a product (Refund/Exchange System)
- [ ] Check Activity Log shows entries

### Check 4: Get Railway URL
- [ ] Click web service in Railway dashboard
- [ ] Go to "View Domains" section
- [ ] Copy the URL: https://xxx.railway.app

## Troubleshooting Guide

If build fails, check Railway logs:
- Look for "Build failed" message
- Common issues:
  - Missing PHP extensions → Handled by nixpacks.toml
  - Composer errors → Run `composer install --no-dev`
  - npm errors → Rare, build succeeded locally

If app crashes on startup:
- Check "Logs" tab in Railway
- Common issues:
  - APP_KEY not set → Must be base64 format
  - Database not connected → Check DB_* variables
  - Missing migration → Handled by nixpacks.toml

If can't access database:
- Verify all DB_* variables match MySQL service
- Try restarting MySQL service in Railway
- Check Railway MySQL service logs

## Final Steps: Share with Sadiq

Once all checks pass:
1. Copy the Railway URL from "View Domains"
2. Share with Sadiq: "Your system is ready to test at: https://xxx.railway.app"
3. Provide login credentials (if database seed ran)
4. Ask Sadiq to report any issues

## Rollback Plan

If something goes wrong after deployment:
1. Go to Deployments tab in Railway
2. Find last successful deployment
3. Click "Redeploy" to restore previous version
4. Your live system returns to working state
5. Investigate issue and try again

## Session ID: sadiq-autopart-production-jan-2025
Railway Project URL: https://railway.app/project/YOUR_PROJECT_ID (save this!)
