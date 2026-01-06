# The Giving Grid

**Connecting needs, surplus, and volunteers across Tennessee communities.**

---

## What Is The Giving Grid?

The Giving Grid is a regional, web-based coordination platform designed to connect individuals, volunteers, and verified nonprofit organizations across Tennessee. The goal is simple: make it easier for communities to share resources, coordinate logistics, and support local causes through direct, practical action.

This isn't a social network. It's not a fundraising platform. It's a *coordination layer* — a shared system where surplus meets need and awareness turns into action.

---

## Features

### ✅ MVG Complete

| Feature | Description |
|---------|-------------|
| **Public Browsing** | Browse needs, offers, and volunteer opportunities without login |
| **Advanced Filtering** | Filter by type, county, category, urgency, and cause |
| **User Accounts** | Registration, login, session management |
| **Organization Profiles** | Verified nonprofit profiles with mission and contact info |
| **Listing Management** | Create, edit, and manage needs/offers/volunteer posts |
| **Response System** | Respond to listings with "I Can Help" / "I'm Interested" |
| **Messaging** | Threaded conversations between posters and responders |
| **Status Workflow** | Open → Accepted → Fulfilled lifecycle |
| **Reporting** | Users can report suspicious content |
| **Admin Dashboard** | Stats, org verification queue, report moderation |
| **Mobile Responsive** | Works on all devices |
| **Accessibility** | WCAG-compliant, semantic HTML |

### 🔮 Future Enhancements

- Organization self-registration
- Password reset via email
- Email notifications
- Enhanced search
- Static pages (About, Terms, Privacy)
- Cause-based subscriptions

---

## The Grid Loop

The core workflow is intentionally simple:

```
Organization posts a NEED
        ↓
Need appears publicly
        ↓
Individual sees it
        ↓
Individual responds ("I Can Help")
        ↓
Threaded messaging for coordination
        ↓
Listing marked FULFILLED
        ↓
Community strengthened 💪
```

If that loop works, the Grid works.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Markup | HTML5 (semantic) |
| Styling | CSS3 (mobile-first, custom properties) |
| Interactivity | JavaScript ES6+ (vanilla) |
| Backend | PHP 8.1+ |
| Database | MySQL 8+ |
| Architecture | MVC with front controller |

**No frameworks. No unnecessary dependencies.** Hand-coded for performance, accessibility, and long-term maintainability.

---

## Project Structure

```
giving-grid/
├── public/                 # Web root
│   ├── index.php           # Front controller
│   ├── .htaccess           # URL rewriting
│   └── assets/
│       ├── css/style.css   # All styles (~2000 lines)
│       └── js/main.js      # Interactivity
├── app/
│   ├── Controllers/        # 9 controllers
│   │   ├── HomeController.php
│   │   ├── BrowseController.php
│   │   ├── ListingController.php
│   │   ├── OrgController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ResponseController.php
│   │   ├── ReportController.php
│   │   └── AdminController.php
│   ├── Models/             # 7 models
│   │   ├── Listing.php
│   │   ├── Organization.php
│   │   ├── User.php
│   │   ├── Cause.php
│   │   ├── Response.php
│   │   ├── Message.php
│   │   └── Report.php
│   ├── Views/
│   │   ├── layouts/        # main.php, auth.php
│   │   ├── partials/       # nav, footer, cards, flash
│   │   └── pages/          # All page templates
│   │       ├── admin/      # Admin views
│   │       └── errors/     # 403, 404
│   ├── Services/           # 8 services
│   │   ├── Database.php
│   │   ├── AuthService.php
│   │   ├── ListingService.php
│   │   ├── OrgService.php
│   │   ├── CauseService.php
│   │   ├── ResponseService.php
│   │   ├── ReportService.php
│   │   └── AdminService.php
│   ├── Middleware/         # Auth, CSRF, Role checks
│   ├── Validation/         # User, Listing, Response validators
│   └── Helpers/            # url, view, flash, csrf, sanitize
├── config/
│   ├── app.php             # App settings
│   ├── database.php        # DB connection
│   ├── routes.php          # All routes (~40)
│   └── constants.php       # Types, categories, counties
├── database/
│   ├── schema.sql          # Full database schema
│   └── seeds/
│       ├── causes.sql      # Cause categories
│       └── demo.sql        # Test data
├── storage/
│   ├── logs/
│   ├── cache/
│   └── uploads/
├── .env.example            # Environment template
├── .gitignore
├── README.md               # This file
└── DEPLOYMENT.md           # Production deployment guide
```

---

## Quick Start

### Requirements

- PHP 8.1+
- MySQL 5.7+ / 8.0+
- Apache with mod_rewrite OR Nginx
- PHP extensions: pdo_mysql, mbstring, json, session

### Local Development Setup

```bash
# Clone or extract the project
cd giving-grid

# Copy environment template
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Create database
mysql -u root -p -e "CREATE DATABASE givinggrid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p givinggrid < database/schema.sql

# Import causes (required)
mysql -u root -p givinggrid < database/seeds/causes.sql

# Import demo data (optional)
mysql -u root -p givinggrid < database/seeds/demo.sql

# Start PHP development server
php -S localhost:8000 -t public
```

Visit `http://localhost:8000`

### Demo Accounts

If you imported `demo.sql`:

| Email | Password | Role |
|-------|----------|------|
| admin@givinggrid.org | password123 | Admin |
| john.doe@email.com | password123 | Individual |
| sarah@knoxfoodbank.org | password123 | Org (Verified) |
| james@andersonshelter.org | password123 | Org (Verified) |

---

## Routes

### Public Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Homepage |
| GET | `/browse` | Browse listings with filters |
| GET | `/listing/{id}` | Listing detail |
| GET | `/organizations` | Organizations directory |
| GET | `/organization/{id}` | Organization profile |
| GET | `/login` | Login form |
| GET | `/register` | Registration form |

### Protected Routes (Auth Required)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/dashboard` | User dashboard |
| GET | `/post` | Post type selection |
| POST | `/post` | Create listing |
| GET | `/listing/{id}/edit` | Edit listing |
| GET | `/listing/{id}/respond` | Respond form |
| GET | `/responses` | Messages inbox |
| GET | `/responses/{id}` | Conversation thread |
| GET | `/report/listing/{id}` | Report form |

### Admin Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/admin` | Admin dashboard |
| GET | `/admin/verify` | Verification queue |
| GET | `/admin/reports` | Reports queue |

---

## Database Schema

### Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `users` | User accounts | email, password_hash, role, org_id |
| `organizations` | Nonprofit profiles | name, mission, is_verified |
| `listings` | Needs/offers/volunteer | type, title, status, user_id, org_id |
| `causes` | Category tags | name, slug |
| `listing_causes` | Many-to-many | listing_id, cause_id |
| `responses` | Response to listings | listing_id, responder_id, status |
| `response_messages` | Thread messages | response_id, sender_id, content |
| `reports` | User reports | type, target_id, reason, status |

### User Roles

| Role | Permissions |
|------|-------------|
| `individual` | Post offers, respond to listings |
| `org_member` | + Post needs/volunteer (if org verified) |
| `admin` | + Verify orgs, moderate reports |

### Listing Types

| Type | Who Can Post | Icon |
|------|--------------|------|
| `need` | Verified orgs only | 🟥 |
| `offer` | Anyone | 🟩 |
| `volunteer` | Verified orgs only | 🟦 |

---

## Configuration

### Environment Variables (.env)

```env
# Application
APP_ENV=development        # development | production
APP_DEBUG=true             # true | false
APP_URL=http://localhost:8000
APP_NAME="The Giving Grid"

# Database
DB_HOST=localhost
DB_NAME=givinggrid
DB_USER=root
DB_PASS=
```

### Key Constants (config/constants.php)

- `COUNTIES` - East Tennessee counties
- `CATEGORIES` - Listing categories
- `LISTING_TYPES` - Need, Offer, Volunteer
- `URGENCY_LEVELS` - Critical, High, Medium, Low
- `LOGISTICS_OPTIONS` - Pickup, Delivery, Either, N/A
- `REPORT_REASONS` - Spam, Inappropriate, Scam, etc.

---

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for complete production deployment instructions including:

- Shared hosting setup (cPanel)
- VPS setup (Ubuntu/Nginx)
- SSL/HTTPS configuration
- Database migration
- Backup scripts
- Troubleshooting

---

## Success Metrics

The MVG will be considered successful if:

- ✅ Organizations actively post real needs
- ✅ At least one cross-community fulfillment occurs
- ✅ Volunteers participate more than once
- ✅ Listings are completed, not abandoned
- ✅ Users understand the system without explanation

If people use it naturally, we've won.

---

## Geographic Scope

| Phase | Region | Status |
|-------|--------|--------|
| Phase 1 | Knoxville & East Tennessee | 🚧 Current |
| Phase 2 | Expanded East Tennessee | Planned |
| Phase 3 | Statewide Tennessee | Future |

Starting small allows for faster validation, easier moderation, and stronger community relationships.

---

## Project Philosophy

- **Communities already have what they need — coordination is what's missing.**
- **Practical action over performative awareness.**
- **Cooperation over competition.**
- **Simplicity over complexity.**
- **Trust over scale.**

We're not trying to build the biggest platform. We're trying to build one that *actually works* for real people in real communities.

---

## About

**The Giving Grid** is a project of **HC Web Labs**, a freelance web development studio based in East Tennessee specializing in hand-coded, accessible, performance-optimized websites for small businesses, nonprofits, and community organizations.

- **Developer:** Heather Cooper
- **Website:** [hcweblabs.com](https://hcweblabs.com)
- **Location:** Rocky Top, Tennessee

---

## License

This project is proprietary software. All rights reserved.

Unauthorized copying, modification, distribution, or use of this software is strictly prohibited without prior written permission from HC Web Labs.

---

## Questions?

If you're interested in learning more about The Giving Grid, partnering as an organization, or supporting the project, reach out via [hcweblabs.com](https://hcweblabs.com).

---

*Built with care for East Tennessee. People over profit.* ❤️
