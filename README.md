<div align="center">

# leafmark
### Your book collection. Always know what you've read.

[![Wiki](https://img.shields.io/badge/Docs-Wiki-4A7C59)](../../wiki)
[![License](https://img.shields.io/badge/License-AGPL--3.0-blue)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20)](composer.json)

<img src=".github/social-preview.png" alt="Leafmark: search for books, track your reading progress and see your library statistics" width="800"/>

</div>

---

Leafmark is a self-hosted, multi-user book tracking application. Add books by scanning ISBNs or searching across six external sources, track reading status and progress, organise your library with tags and series, and follow your reading with yearly challenges and statistics. Every family member keeps their own collection. Sign-in works without a password.

## Installation & quick start

Leafmark ships as a ready-made Docker image, built by GitHub Actions and pushed to the GitHub Container Registry, with a PostgreSQL database. You need a small server, a domain with HTTPS and an SMTP account for sign-in links.

1. Create a **PostgreSQL** resource in [Coolify](https://coolify.io) (any Docker Compose host works too).
2. Deploy `deploy/docker-compose.coolify.yml` as a "Docker Compose Empty" resource and set the environment variables ([Installation und Betrieb](handbuch/Installation-und-Betrieb.md)).
3. Open the site, **register the first account** — it becomes the admin automatically.
4. Search for a book, add it, and start tracking.

Updates are a new image tag; the database is migrated automatically on start. Make a backup of the PostgreSQL resource and the `app-storage` volume first.

## How it works

Leafmark searches multiple sources in one query and merges the results by ISBN. Add a book from the results, or fill in the details by hand. Reading progress, covers, tags and series are stored per book; statistics and challenges aggregate across the whole library.

| Part | What for |
|---|---|
| [Bücher & Import](handbuch/Buecher-und-Import.md) | Add, edit and organise books; import from Goodreads CSV, ZIP backup, Amazon URLs or six book APIs |
| [Lesen & Statistik](handbuch/Lesen-und-Statistik.md) | Reading status, page progress, ratings, series, yearly challenges, charts |
| [Anmeldung & Sicherheit](handbuch/Anmeldung-und-Sicherheit.md) | Passkeys, magic links, TOTP 2FA with recovery codes, family accounts |
| [Administration](handbuch/Administration.md) | User management, registration control, SMTP and API key settings |
| [Installation & Betrieb](handbuch/Installation-und-Betrieb.md) | Coolify setup, environment variables, updates, backups |

Everything else — every setting, every import option — is in the **[wiki](../../wiki)**.

## Privacy & security

Leafmark runs on your own server; your data goes to nobody else, and there is no telemetry. The only outside calls are searches to the book APIs you configure and cover image downloads, both of which you control. There are no passwords to leak by default: sign-in uses passkeys or single-use email links; a traditional password always requires a second factor.

Every change runs through the full test suite against PostgreSQL before an image is built. Please report security issues privately to the maintainer instead of opening a public issue.

## Contributing

Ideas, bug reports and wishes are welcome as an [issue](../../issues/new).

**For developers:** PHP 8.5, Laravel 13, PostgreSQL (see `phpunit.xml` for test database). The repository opens ready to use in the dev container or GitHub Codespaces; `docker compose up -d db` starts the database, `php artisan serve` and `npm run dev` start the app, and `php artisan test` runs the tests. Architecture and conventions are in [CLAUDE.md](CLAUDE.md); the [handbook](handbuch/Home.md), from which the wiki is generated, describes every feature.

## License

Leafmark is free software under the [GNU Affero General Public License v3.0](LICENSE). Anyone may use, adapt and share it at no cost. If you offer a modified version to others over a network, you must make its source code available under the AGPL-3.0 as well.
