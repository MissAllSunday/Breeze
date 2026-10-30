**Breeze** 2.0.0, https://missallsunday.com

The software is being license under [MPL 2.0 license](https://www.mozilla.org/MPL/).

###### What is Breeze:

Breeze is a social mod for your users. It enables a dynamic wall where they can post status and comments.
Comes with the following features:

###### Requirements:

- SMF 2.1.x
- PHP 8.3

###### Features:

- No file edits, works with all themes.
- Opt-in personal wall per member, falling back to the SMF summary page when disabled.
- Users individual settings, each user defines her/his own settings.
- Compact action bar with inline buttons for likes, comments, and delete on every status and comment.
- Likes for status and comments.
- Mentions for status and comments.
- Alerts integrated with the SMF alert system, with per-user preferences.
- General wall page for displaying your buddies latest activity.
- Single status permalink pages used by alerts and like lists.
- Per-user block list controlling wall visibility (symmetric blocking).
- Optional post confirmation dialog before submitting status or comments.
- About me and buddies profile tabs.
- Cursor based pagination with a configurable number of status per page.
- Admin flood control, its own permission group and maintenance tools.

###### Installing:

1. Upload the package from **Admin > Package Manager**.
2. Enable the master setting from **Admin > Configuration > Breeze Admin Panel > General settings**.
3. Assign the Breeze permissions to your membergroups.
4. Each member enables their own wall from **Profile > My wall settings**.

The mod uses the following scripts and libraries:

**Frontend (bundled in the package):**
- [React](https://reactjs.org) 19.3.1
- [React Hot Toast](https://react-hot-toast.com)

**PHP (bundled in the package):**
- [league/container](https://container.thephpleague.com)
- [league/event](https://event.thephpleague.com)

##### Development

```bash
# Install all dependencies (Composer + Node)
make install     # PHP dependencies
make ui-install  # Node dependencies
```

##### Tests

```bash
# Run all tests (PHP + UI + E2E)
make all

# Run individually
make test       # PHP (PHPUnit + PHPStan)
make lint       # Auto-fix PHP (PHP-CS-Fixer) + UI (Biome) code style
make coverage   # Generate HTML coverage report
make ui-test    # UI components (Vitest)
make e2e        # E2E (Playwright + Docker)
```

##### Generate an SMF package

```bash
make package    # Lints, builds, updates REACT_HASH and produces Breeze.zip
```

###### Documentation:
- [Architecture](docs/ARCHITECTURE.md)
- [Testing Guide](docs/TESTING.md)
- [Visibility Filtering](docs/VISIBILITY_FILTERING.md)
- [Wall Visibility Rules](docs/WALL_VISIBILITY_RULES.md)

###### Notes:

Feel free to fork this repository and make your desired changes.

Please see the [Developer's Certificate of Origin](https://github.com/MissAllSunday/Breeze/blob/master/DCO.txt) in the repository:
by signing off your contributions, you acknowledge that you can and do license your submissions under the license of the project.

###### Branches organization:
* ***develop*** - Main branch, releases are tagged from here
