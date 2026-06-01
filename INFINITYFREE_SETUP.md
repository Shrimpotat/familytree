# InfinityFree Setup Guide

## Step 1: Sign Up at InfinityFree
1. Go to https://infinityfree.net
2. Click "Sign Up Free"
3. Create account with email
4. Verify email

## Step 2: Create Website
1. After login, click "Create Website"
2. Choose a domain name (e.g., `familytree.infinityfree.net`)
3. Accept terms and create

## Step 3: Get FTP Credentials
1. Go to Account → Manage
2. Find your website
3. Look for "FTP Details" or "FTP Accounts"
4. Note down:
   - FTP Host (e.g., `ftpXX.infinityfree.net`)
   - FTP Username
   - FTP Password

## Step 4: Upload PHP Files via FTP

### Using Windows File Explorer (Easiest):
1. Open File Explorer
2. Type in address bar: `ftp://ftpXX.infinityfree.net`
3. Login with FTP credentials
4. Navigate to `htdocs/` folder
5. Create folder `familytree`
6. Upload these files:
   - `api.php`
   - `config.php`
   - `firebase_credentials.json`
   - `.htaccess`

### Using FileZilla (Alternative):
1. Download FileZilla from https://filezilla-project.org
2. File → Site Manager
3. New Site:
   - Host: ftpXX.infinityfree.net
   - User: [your FTP username]
   - Password: [your FTP password]
4. Connect
5. Drag files to remote `/htdocs/familytree/` folder

## Step 5: Verify Upload
- Go to https://yoursite.infinityfree.net/familytree/api.php
- Should see JSON response or similar

## Step 6: Update Frontend
Your backend URL will be: `https://yoursite.infinityfree.net/familytree`

Then I'll update `app.js` and re-deploy.

---

**What's your InfinityFree URL once you create it?**
(e.g., `familytree.infinityfree.net`)
