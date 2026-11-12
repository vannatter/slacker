# Security Policy

## Supported versions

Only the latest release on `master` (3.x) is supported.

## Reporting a vulnerability

Please **do not** open a public issue. Use GitHub's private
[security advisory form](https://github.com/vannatter/slacker/security/advisories/new)
instead, and include steps to reproduce and the impact you expect.

## How Slacker protects itself

- **Request signing.** Every request must carry a valid Slack `X-Slack-Signature`
  (HMAC-SHA256 over the raw body with your signing secret), and its timestamp must be
  within 5 minutes. If `SLACK_SIGNING_SECRET` is unset, every request is rejected.
- **Explicit plugin registry.** Only plugins wired up in `App::create()` can run.
  User input is never used to pick a class or file.
- **Output escaping.** User-supplied text is escaped before it goes back to Slack,
  so it can't inject `<!channel>` pings or fake links.
- **`push` allow-list.** Deploy hooks only fire for Slack user IDs listed in
  `PUSH_ALLOWED_USERS`. If that list is empty, nobody can deploy.
- **Docroot isolation.** Only `public/` is served; `.env`, source and `vendor/` sit
  outside the web root.
- **Outbound HTTP.** TLS verification is on, redirects are not followed, timeouts
  are short, and API keys are stripped from error logs.
- **Dependencies.** CI runs `composer audit`.

## Deployment checklist

- [ ] Web server document root points at `public/`
- [ ] HTTPS only
- [ ] `SLACK_SIGNING_SECRET` set; `.env` readable only by the PHP user (`chmod 600`)
- [ ] `SLACKER_DEBUG=false`
- [ ] `PUSH_ALLOWED_USERS` set, or `push` listed in `SLACKER_DISABLED_PLUGINS`
