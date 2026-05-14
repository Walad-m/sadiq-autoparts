# Railway Deployment Guide for Sadiq Auto Parts

## Quick Start

### Step 1: Push to GitHub
```bash
git init
git add .
git commit -m "Ready for Railway deployment"
git remote add origin https://github.com/YOUR_USERNAME/sadiq-autopart.git
git push -u origin main
```

### Step 2: Create Railway Project
1. Go to https://railway.app
2. Click "New Project"
3. Select "Deploy from GitHub"
4. Connect your GitHub account
5. Select this repository

### Step 3: Add MySQL Database
1. In Railway dashboard, click "+ Add Service"
2. Select "MySQL"
3. Configure:
   - Name: `mysql`
   - Version: 8.0

### Step 4: Configure Environment Variables
In Railway project settings, add these variables:

```
APP_NAME=SadiqAutoParts
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://your-railway-url.railway.app

DB_CONNECTION=mysql
DB_HOST=${{mysql.RAILWAY_PRIVATE_URL}}
DB_PORT=3306
DB_DATABASE=sadiq
DB_USERNAME=${{mysql.MYSQL_USER}}
DB_PASSWORD=${{mysql.MYSQL_PASSWORD}}

CACHE_DRIVER=database
SESSION_DRIVER=database
QUEUE_CONNECTION=sync

MAIL_MAILER=log
```

**To get your APP_KEY:**
```bash
php artisan key:generate --show
# Copy the base64:... value to APP_KEY
```

### Step 5: Deploy
1. Railway will automatically deploy when you push to GitHub
2. Monitor deployment in Railway dashboard
3. Check logs for any errors

## Post-Deployment Checklist

- [ ] Database migrations ran successfully
- [ ] Application loads at Railway URL
- [ ] Can create products (tests POST)
- [ ] Can view dashboard
- [ ] Can make a sale (POS)
- [ ] Activity logs are working
- [ ] No error logs in Railway

## Troubleshooting

### Build Failed
Check Railway logs: Look for missing dependencies or build errors

**Common fixes:**
```bash
# If Composer dependencies fail
composer install --no-dev --optimize-autoloader

# If npm packages fail
npm ci --prefer-offline --no-audit
```

### Database Connection Error
- Verify DB variables in Railway settings
- Check DB credentials match
- Ensure MySQL service is running

### Application won't start
```bash
# SSH into Railway and run:
php artisan config:cache
php artisan route:cache
```

### 500 Error on First Load
- Check logs in Railway dashboard
- Run migrations manually in Railway terminal
- Verify .env variables are set

## Environment Variables Reference

| Variable | Purpose | Example |
|----------|---------|---------|
| `APP_KEY` | Laravel encryption key | `base64:...` |
| `APP_ENV` | Environment (production) | `production` |
| `DB_HOST` | MySQL host from Railway | `${{mysql.RAILWAY_PRIVATE_URL}}` |
| `DB_DATABASE` | Database name | `sadiq` |
| `MAIL_MAILER` | Notification method | `log` (for testing) |

## Rolling Back

If deployment breaks:
1. Go to Railway Deployments tab
2. Click "Redeploy" on previous working version
3. Check logs for what went wrong

## Monitoring

- View real-time logs in Railway dashboard
- Set up error notifications
- Monitor database size
- Check memory/CPU usage

## Performance Tips

1. Enable query caching:
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'database'),
```

2. Use Railway's auto-scaling (Pro plan)

3. Enable compression in nginx (handled by Railway)

## Security Checklist

- ✅ `APP_DEBUG=false` (set in Railway env)
- ✅ `APP_ENV=production` (set in Railway env)
- ✅ HTTPS enabled by default (Railway provides SSL)
- ✅ Environment variables not in code
- ✅ Database credentials secure (Railway manages these)

## Next Steps After Deployment

1. Share Railway URL with Sadiq for testing
2. Monitor logs for issues
3. Collect feedback
4. Make adjustments and redeploy (automatic with git push)

## Database Backups

Railway includes automatic daily backups. To restore:
1. Go to MySQL service in Railway
2. Click Backups tab
3. Select backup to restore
4. Confirm

## Getting Railway URL

After deployment:
1. Go to Railway project dashboard
2. Click your web service (Laravel app)
3. Copy URL from "View Domains" section
4. Share with Sadiq: `https://your-domain.railway.app`
