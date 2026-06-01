# Family Tree - Firebase Deployment Guide

This project is now ready to be deployed to Firebase Hosting with Cloud Functions backend.

## Project Structure

```
familytree/
├── functions/              # Cloud Functions backend (Node.js)
│   ├── index.js           # Express API
│   └── package.json       # Dependencies
├── public/                # Static frontend (Firebase Hosting)
│   ├── index.html
│   ├── add-person.html
│   ├── edit-person.html
│   ├── lineage.html
│   ├── tree.html
│   ├── app.js
│   └── style.css
├── firebase.json          # Firebase configuration
├── .firebaserc            # Firebase project config
└── [legacy PHP files]     # No longer used
```

## Deployment Steps

### 1. Install Firebase CLI

```bash
npm install -g firebase-tools
```

### 2. Install Cloud Functions Dependencies

```bash
cd functions
npm install
cd ..
```

### 3. Deploy to Firebase

```bash
firebase deploy
```

This will deploy:
- Cloud Functions API endpoints to handle backend logic
- Static frontend files to Firebase Hosting
- Firestore rules and security settings

## What Changed

### Backend (Cloud Functions)
- **Old:** PHP files (`index.php`, `add_person.php`, etc.)
- **New:** `functions/index.js` - Express API with endpoints:
  - `GET /api/persons` - Fetch all persons
  - `GET /api/persons/:id` - Fetch single person
  - `POST /api/persons` - Create person
  - `PATCH /api/persons/:id` - Update person
  - `DELETE /api/persons/:id` - Delete person
  - `GET /api/search?q=name` - Search persons
  - `GET /api/lineage/:id` - Get ancestors

### Frontend (Static HTML/CSS/JS)
- **Old:** PHP templates (`index.php`, `add_person.php`, etc.)
- **New:** Static HTML + `app.js` that calls the API

### Security
- Cloud Functions have built-in security with Firestore
- Frontend communicates via API endpoints
- No sensitive logic exposed to client

## Access Your Site

After deployment, visit:
```
https://pp2fmly3.web.app
```

## Troubleshooting

### Functions not deploying?
```bash
firebase login
firebase projects:list
firebase use pp2fmly3
firebase deploy
```

### CORS errors?
The `functions/index.js` includes CORS setup. If you get CORS errors, check:
1. Cloud Functions are deployed
2. API endpoints are accessible

### Data not showing?
1. Verify Firestore has data
2. Check browser console for API errors
3. Ensure Firestore security rules allow reads

## Notes

- The gender assignment data has been applied to Firestore
- Pagination is fixed in the backend for loading all persons
- All data is stored in Firestore and synced across users
