# Security Policy

## Supported Versions

We release patches for security vulnerabilities in the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| < 2.0   | :x:                |

**Important:** Version 1.x has known security vulnerabilities and is no longer supported. Please upgrade to 2.0+ immediately.

## Known Security Improvements in v2.0

Version 2.0 addresses several critical security issues present in earlier versions:

### Fixed Vulnerabilities
- **Arbitrary Class Instantiation** - Now uses plugin whitelist to prevent attackers from instantiating arbitrary PHP classes
- **Missing Input Validation** - All user inputs are now validated and sanitized
- **Insecure HTTP Connections** - All API calls now use HTTPS with SSL verification
- **Missing SSL Verification** - HTTPS connections now properly verify SSL certificates
- **Configuration Exposure** - Sensitive configuration moved to `.env` file with proper `.gitignore`

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability, please follow these steps:

### 1. **DO NOT** open a public GitHub issue

Security vulnerabilities should not be disclosed publicly until a fix is available.

### 2. Report via Private Channel

Please report security vulnerabilities via one of these methods:

- **Email**: Create a private report via GitHub Security Advisories
- **Direct Contact**: [@dustinvannatter](https://twitter.com/dustinvannatter)

### 3. Include in Your Report

Please include as much information as possible:

- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if you have one)
- Your name/handle (for credit, if desired)

### 4. Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Varies by severity
  - Critical: 1-7 days
  - High: 7-30 days
  - Medium: 30-90 days
  - Low: Next release cycle

## Security Best Practices for Deployment

### Required Security Measures

1. **Use HTTPS Only**
   - Never deploy without TLS/SSL
   - Redirect HTTP to HTTPS

2. **Protect Sensitive Files**
   - Ensure `.env` is not in your git repository
   - Verify web server blocks access to:
     - `.env` and `.env.example`
     - `config/` directory
     - `vendor/` directory
     - `.git/` directory
     - All `.php` files except `index.php`

3. **Configure Web Server**
   - Use provided `.htaccess` (Apache) or `nginx.conf.example` (Nginx)
   - Enable security headers
   - Disable directory listing
   - Set proper file permissions (644 for files, 755 for directories)

4. **Environment Configuration**
   - Use strong, unique tokens
   - Never commit `.env` to version control
   - Rotate tokens regularly
   - Use environment-specific tokens (dev/staging/prod)

5. **PHP Configuration**
   - Use PHP 8.0 or higher (required)
   - Disable `display_errors` in production
   - Enable `error_logging` to files (not output)
   - Set `expose_php = Off`

### Recommended Security Measures

1. **Network Security**
   - Use firewall rules to restrict access
   - Consider IP whitelisting for Slack's IP ranges
   - Implement rate limiting

2. **Monitoring**
   - Enable access logging
   - Monitor for suspicious activity
   - Set up alerts for failed authentication

3. **Regular Maintenance**
   - Keep PHP updated
   - Run `composer update` regularly
   - Monitor security advisories with `composer audit`
   - Review and rotate API keys/tokens

4. **Additional Hardening**
   - Run PHP-FPM with minimal permissions
   - Use separate user account for the application
   - Enable SELinux or AppArmor if available
   - Consider running in a container

## Security Checklist for Deployment

Before deploying to production, verify:

- [ ] PHP version is 8.0 or higher
- [ ] `composer install --no-dev` has been run
- [ ] `.env` file exists and contains valid credentials
- [ ] `.env` is in `.gitignore` and not committed to git
- [ ] Web server configuration blocks access to sensitive files
- [ ] HTTPS is configured and enforced
- [ ] Security headers are enabled
- [ ] File permissions are correct (644/755)
- [ ] `display_errors` is disabled
- [ ] Error logging is enabled and monitored
- [ ] All API tokens are production tokens (not test/dev)
- [ ] Slack webhook URL is correct
- [ ] Plugin whitelist in `index.php` matches your enabled plugins

## Security Headers

Your web server should send these security headers (configured in `.htaccess` / `nginx.conf.example`):

```
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; ...
```

## Common Security Mistakes

### ❌ DON'T DO THIS:

1. **Committing `.env` to git**
   ```bash
   # Wrong - exposes secrets
   git add .env
   ```

2. **Using HTTP instead of HTTPS**
   ```php
   // Wrong - insecure
   define('SLACK_WEBHOOK_URL', 'http://...');
   ```

3. **Exposing config directory**
   ```nginx
   # Wrong - allows access to config/
   location / {
       try_files $uri $uri/ =404;
   }
   ```

4. **Disabling SSL verification**
   ```php
   // Wrong - man-in-the-middle attacks
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   ```

### ✅ DO THIS INSTEAD:

1. **Keep `.env` out of git**
   ```bash
   # Correct - use example file
   cp .env.example .env
   # Edit .env with your secrets
   # .gitignore already blocks .env
   ```

2. **Always use HTTPS**
   ```php
   // Correct - secure
   define('SLACK_WEBHOOK_URL', 'https://...');
   ```

3. **Block sensitive directories**
   ```nginx
   # Correct - denies access
   location ^~ /config/ {
       deny all;
   }
   ```

4. **Always verify SSL**
   ```php
   // Correct - secure connections
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
   ```

## Acknowledgments

We appreciate responsible disclosure and will credit security researchers who report vulnerabilities (with permission).

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Slack API Security](https://api.slack.com/authentication/best-practices)

## Questions?

If you have questions about security that don't involve a vulnerability, feel free to open a GitHub discussion or issue.
