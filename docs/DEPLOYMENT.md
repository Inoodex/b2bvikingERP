# CI/CD Deployment Guide — b2bvikingERP

## Overview
This repository uses an enterprise-grade **High-Speed Single Archive CI/CD Pipeline** built with GitHub Actions specifically engineered for **Namecheap cPanel Shared Hosting**.

Instead of uploading tens of thousands of individual files over slow FTP connections (which previously caused 6-hour timeouts), this pipeline:
1. Compiles frontend assets and dependencies in GitHub Actions.
2. Compresses the application into a single `release.zip` package.
3. Uploads the single archive over FTP in **under 15 seconds**.
4. Triggers `deploy.php` on the server to extract the release, auto-detect PHP 8.3, run database migrations, and optimize all Laravel caches.
5. Runs an automated health check to verify site availability.

**Total Deployment Time:** ~1 minute (100% hands-free).

---

## Required GitHub Secrets

Configure these secrets in **GitHub Repo $\rightarrow$ Settings $\rightarrow$ Secrets and variables $\rightarrow$ Actions**:

| Secret Name | Description | Example / Actual Value |
|---|---|---|
| `FTP_HOST` | Namecheap Direct Server Hostname | `server704.web-hosting.com` |
| `FTP_USERNAME` | Dedicated FTP Account Username | `deploy@test.b2bviking.com` |
| `FTP_PASSWORD` | Password created for the FTP account | *(Your cPanel FTP Password)* |
| `FTP_SERVER_DIR` | Target Directory on FTP server | `/` |
| `DEPLOY_SECRET` | Secret token authorizing the deploy script | `B2BViking@Deploy2026` |
| `DEPLOY_WEBHOOK_URL` | Webhook URL pointing to `public/deploy.php` | `https://test.b2bviking.com/public/deploy.php` |

---

## Critical Server Setup: PHP 8.3 in cPanel

Because Laravel dependencies (e.g., `spatie/laravel-backup`, `openspout`) require **PHP >= 8.3**, the cPanel web server must be set to PHP 8.3:

1. Log in to your **cPanel**.
2. Search for and open **MultiPHP Manager**.
3. Locate your domain: `test.b2bviking.com`.
4. From the PHP Version dropdown, select **PHP 8.3 (ea-php83)**.
5. Click **Apply**.

---

## How to Deploy

### Automatic Deployment
Simply push your code to the `main` branch from your local terminal:
```bash
git add .
git commit -m "feat: your new feature"
git push origin main
```
GitHub Actions will automatically trigger and complete the deployment.

### Manual Deployment
1. Go to **GitHub $\rightarrow$ Actions**.
2. Click on **Deploy to Production** in the left sidebar.
3. Click **Run workflow**.
4. *(Optional)* Check `Force re-bundle and upload vendor archive?` if you want to force-refresh the `vendor.zip` package.
5. Click **Run workflow**.

---

## How to Roll Back

If a bug or issue is discovered in production, you can instantly roll back to any previous commit or Git tag:

1. Go to **GitHub $\rightarrow$ Actions $\rightarrow$ Rollback Deployment**.
2. Click **Run workflow**.
3. In the `commit_ref` input, enter the commit SHA or tag (e.g. `b908207` or `v1.0.0`).
4. Click **Run workflow**.

The pipeline will checkout that exact version, package the release, deploy to the server, and clear caches automatically.

---

## Troubleshooting

### Webhook returns HTTP 403
Verify that `DEPLOY_SECRET` in GitHub Secrets matches the `DEPLOY_SECRET` constant inside `public/deploy.php`.

### Webhook returns HTTP 500
Ensure cPanel MultiPHP Manager has PHP 8.3 enabled for `test.b2bviking.com`. Check the log file on the server at:
`/home/hyggznzm/test.b2bviking.com/storage/logs/deploy.log`

### FTP Authentication Fails (530)
Verify in cPanel $\rightarrow$ FTP Accounts that `deploy@test.b2bviking.com` is active and the password matches `FTP_PASSWORD`.
