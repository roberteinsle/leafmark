# Security Notice - API Key Rotation Required

## Issue
A Google Books API key was accidentally committed to the repository in `.env.docker` (commit 71c679db).

**Exposed Key:** `AIzaSyCUk4Wxb_GR4wF7m7XMckM3gMf_LMi3X1A`

## Required Actions

### 1. Revoke the Exposed Google API Key (URGENT)

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Navigate to **APIs & Services** > **Credentials**
3. Find the API key: `AIzaSyCUk4Wxb_GR4wF7m7XMckM3gMf_LMi3X1A`
4. Click on the key and select **Delete** or **Regenerate**
5. Create a new API key with appropriate restrictions:
   - **Application restrictions**: Set to HTTP referrers or IP addresses
   - **API restrictions**: Limit to "Books API" only
   - **Usage quota**: Set reasonable daily limits

### 2. Update Your Local Environment

1. Copy the example file:
   ```bash
   cp .env.docker.example .env.docker
   ```

2. Edit `.env.docker` and add your new API keys:
   - Replace `your_google_books_api_key_here` with your new Google Books API key
   - Replace `your_isbndb_api_key_here` with your ISBNdb API key (if you have one)
   - Replace `your_secure_password_here` with a secure database password
   - Replace `your_app_key_here` with output from: `php artisan key:generate --show`

3. **Never commit `.env.docker` to git** - it's now in `.gitignore`

### 3. Update GitHub Codespaces Secrets

For production/Codespaces environments, use GitHub Secrets:

1. Go to your repository **Settings** > **Secrets and variables** > **Codespaces**
2. Add the following secrets:
   - `GOOGLE_BOOKS_API_KEY`
   - `ISBNDB_API_KEY`
   - `MYSQL_ROOT_PASSWORD`
   - `APP_KEY`

### 4. Clean Git History (Optional but Recommended)

The exposed key is still in git history. To completely remove it:

```bash
# WARNING: This rewrites history and requires force push
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch .env.docker" \
  --prune-empty --tag-name-filter cat -- --all

git push origin --force --all
```

**Note:** Only do this if you haven't shared the repository with others, or coordinate with your team first.

## Prevention

- ✅ `.env.docker` is now in `.gitignore`
- ✅ `.env.docker.example` template created for reference
- 🔒 Always use environment-specific secret management
- 🔒 Never commit files containing API keys, passwords, or tokens
- 🔒 Use GitHub Secrets for CI/CD and Codespaces
- 🔒 Enable secret scanning alerts in repository settings

## Files Changed

- Added `.env.docker` to [.gitignore](.gitignore#L10)
- Created [.env.docker.example](.env.docker.example) template
- Removed `.env.docker` from git tracking

---

**This notice can be deleted after completing all required actions.**
