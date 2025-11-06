# Render Environment Variables - Copy & Paste

## Your Generated APP_KEY
```
base64:OUxYtGDyHeWGryDi5ST8Iam93ThtiIg+vzkYAgav1Bs=
```

**Important:** Replace `YOUR_AIVEN_PASSWORD_HERE` with your actual Aiven password (get it from Aiven dashboard)

---

## Complete Environment Variables for Render

Copy these into Render Dashboard → Your Service → Environment Variables:

```env
APP_NAME="Mexo Seller API"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:OUxYtGDyHeWGryDi5ST8Iam93ThtiIg+vzkYAgav1Bs=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=mexodb-yasir-a5e0.e.aivencloud.com
DB_PORT=21771
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=YOUR_AIVEN_PASSWORD_HERE
DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt
DB_SSL_VERIFY=false

LOG_CHANNEL=stderr
LOG_LEVEL=error

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SANCTUM_STATEFUL_DOMAINS=
SESSION_DOMAIN=
```

**Note:** 
- `APP_URL` will be auto-set by Render after deployment
- `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` - Update these after you deploy your frontend

---

## How to Add in Render

1. Go to Render Dashboard
2. Click on your service
3. Go to "Environment" tab
4. Click "Add Environment Variable"
5. Add each variable one by one (or use bulk import if available)

---

## After Deployment

Once deployed, you'll get a URL like: `https://mexo-backend-xxxx.onrender.com`

Then update:
- `APP_URL=https://mexo-backend-xxxx.onrender.com`
- `SANCTUM_STATEFUL_DOMAINS=your-frontend-url.vercel.app`
- `SESSION_DOMAIN=your-frontend-url.vercel.app`

