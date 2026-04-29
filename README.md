**Breeze**, https://missallsunday.com

The software is being license under [MPL 2.0 license](https://www.mozilla.org/MPL/).

###### What is Breeze:

Breeze is a social mod for your users. It enables a dynamic wall where they can post status and comments.
Comes with the following features:

###### Requirements:

- SMF 2.1.x
- PHP 8.3

###### Features:

- No file edits, works with all themes.
- Users individual settings, each user defines her/his own settings.
- Mentions for status and comments.
- Notifications for several features.
- General wall page for displaying your buddies latest activity.

The mod uses the following scripts:

- [React](https://reactjs.org)
- [React Hot Toast](https://react-hot-toast.com)

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

##### Generate an optimized SMF package
```bash
chmod +x generate_zip.sh && ./generate_zip.sh
```

###### Documentation:
- [Architecture](docs/ARCHITECTURE.md)
- [Testing Guide](docs/TESTING.md)

###### Notes:

Feel free to fork this repository and make your desired changes.

Please see the [Developer's Certificate of Origin](https://github.com/MissAllSunday/Breeze/blob/master/DCO.txt) in the repository:
by signing off your contributions, you acknowledge that you can and do license your submissions under the license of the project.

###### Branches organization:
* ***develop*** - Main branch, releases are tagged from here
