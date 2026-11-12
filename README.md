# Slacker

A simple slash-command bot for Slack, written in PHP.

```
/bot weather 10001
☀️ Currently clear sky and 58°F (feels like 52°F) in New York, US
```

## Commands

| Command | What it does | Needs |
|---|---|---|
| `help` | Lists enabled commands (only you see it) | |
| `say [text]` | Echoes text into the channel | |
| `holiday` | Today's unofficial holiday | |
| `countdown` | Days/hours until a configured date | `COUNTDOWN_DATE` |
| `pug` / `corgi` | Random dog photo ([Dog CEO](https://dog.ceo/dog-api/)) | |
| `weather [zip or city]` | Current conditions ([Open-Meteo](https://open-meteo.com/)) | |
| `weatherext [zip or city]` | 5-day forecast | |
| `listen [last.fm user]` | What someone is listening to | `LASTFM_API_KEY` |
| `push [repo]` | Fires a deploy hook | `PUSH_REPOS`, `PUSH_ALLOWED_USERS` |

## Requirements

- PHP 8.2+ with `curl` and `json`
- Composer
- An HTTPS URL that Slack can reach

## Setup

1. **Install**

   ```bash
   git clone https://github.com/vannatter/slacker.git && cd slacker
   composer install --no-dev
   cp .env.example .env
   ```

2. **Create the Slack app.** Go to <https://api.slack.com/apps>, choose **Create New App → From a manifest**,
   paste [`slack-app-manifest.yml`](slack-app-manifest.yml), and set the slash command URL to your server.
   Install the app to your workspace.

3. **Configure.** Copy the app's **Signing Secret** (Basic Information → App Credentials) into
   `.env` as `SLACK_SIGNING_SECRET`. Fill in any other options you want; see [`.env.example`](.env.example).

4. **Serve `public/`.** Point your web server's document root at `public/` (see
   [`nginx.conf.example`](nginx.conf.example)). For local testing, run `composer serve` with a tunnel such as
   `cloudflared tunnel --url http://127.0.0.1:8080`.

Slacker answers in the slash command's HTTP response. You don't need incoming webhooks, bot tokens or extra
OAuth scopes beyond `commands`.

## Writing a plugin

Implement `Slacker\Plugin\Plugin` and register the plugin in `App::create()`:

```php
namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Reply;

final class Roll implements Plugin
{
    public function name(): string  { return 'roll'; }
    public function usage(): string { return 'roll [sides]'; }

    public function handle(Command $command): Reply
    {
        $sides = max(2, (int) $command->args ?: 6);
        return Reply::channel(":game_die: {$command->mention()} rolled " . random_int(1, $sides));
    }
}
```

- `Reply::channel()` posts to everyone. `Reply::ephemeral()` is shown only to the user who ran the command.
- Pass any user-supplied text through `Reply::escape()`.
- Inject `Slacker\Http\HttpClient` for API calls; the tests swap in `tests/FakeHttpClient.php`.
- Slack waits at most **3 seconds** for a response, so keep outbound calls fast.

## Development

```bash
composer test       # PHPUnit
composer analyse    # PHPStan
composer serve      # php -S 127.0.0.1:8080 -t public
```

## Upgrading from 2.x

3.0 is a rewrite, and existing installs need these changes:

- **Docroot** moves from the repo root to `public/`.
- **Auth**: `SLACK_TOKEN` (deprecated verification token) is replaced by `SLACK_SIGNING_SECRET`.
- **Replies** go back directly in the HTTP response. `SLACK_WEBHOOK_URL`, `SLACK_POSTBACK_TYPE`,
  `SLACK_API_KEY`, `SLACK_HOSTNAME` and `SLACK_CHANNEL` are gone.
- **Config**: the `config/*.php` files are replaced by environment variables in `.env`.
- **Plugins**:
  - `weather` and `weatherext` now use Open-Meteo, so you don't need an API key.
  - `pug` and `corgi` now use Dog CEO.
  - `starwars` is now the configurable `countdown`.
  - `push` requires `PUSH_ALLOWED_USERS`.
  - `spotify` and `armory` were removed. Their APIs now need registered OAuth apps.

## License

MIT
