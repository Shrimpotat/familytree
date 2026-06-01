Family Tree - PHP + Firebase (Firestore) REST API

Installation:
1. Install XAMPP and ensure Apache + PHP are running.
2. Place this folder inside XAMPP htdocs: `familytree`.
3. Create a Firebase project, enable Firestore.
4. Create a service account and download the JSON credentials file.
5. Save the credentials file to the project root as `firebase_credentials.json`.
6. Update `config.php` and set `FIREBASE_PROJECT_ID` to your Firebase project id.

Usage:
- Open http://localhost/familytree/index.php

No Composer or additional extensions required!
This app uses the Firestore REST API with PHP's built-in cURL support.

Notes & Security:
- The `firebase_credentials.json` contains sensitive keys; keep it secure and never commit to version control.
- All user inputs are sanitized and HTML-escaped.
- For production, add authentication and secure the credentials file.

