# The Giving Grid

**Connecting needs, surplus, and volunteers across Tennessee communities.**

---

## What Is The Giving Grid?

The Giving Grid is a regional, web-based coordination platform designed to connect individuals, volunteers, and verified nonprofit organizations across Tennessee. The goal is simple: make it easier for communities to share resources, coordinate logistics, and support local causes through direct, practical action.

This isn't a social network. It's not a fundraising platform. It's a *coordination layer* — a shared system where surplus meets need and awareness turns into action.

---

## The Problem

Communities across Tennessee experience recurring mismatches between **surplus resources** and **unmet needs**. Nonprofits, mutual aid groups, and individuals often operate in isolation, relying on informal communication channels that limit visibility, coordination, and efficiency.

Despite a strong culture of volunteering and mutual support, there is no centralized, trusted system that:

- Surfaces real-time needs and surplus
- Connects nearby communities
- Coordinates logistics
- Converts awareness into tangible action

**The Giving Grid exists to fill that gap.**

---

## The Solution

The Giving Grid provides a website-first platform where:

- **Organizations** post verified needs and volunteer opportunities
- **Individuals** browse and respond with offers, time, or transportation
- **Everyone** can discover what's happening in their county and take action

The core workflow — what we call the **Grid Loop** — is intentionally simple:

```
Organization posts a NEED
        ↓
Need appears publicly
        ↓
Individual sees it
        ↓
Individual responds (offer, time, transport)
        ↓
Organization coordinates
        ↓
Listing marked FULFILLED
```

If that loop works, the Grid works.

---

## Project Philosophy

The Giving Grid is guided by a few core beliefs:

- **Communities already have what they need — coordination is what's missing.**
- **Practical action over performative awareness.**
- **Cooperation over competition.**
- **Simplicity over complexity.**
- **Trust over scale.**

We're not trying to build the biggest platform. We're trying to build one that *actually works* for real people in real communities.

---

## Current Status

**🚧 In Active Development**

The Giving Grid is currently being built as a **Minimum Viable Grid (MVG)** — the smallest functional version that proves the concept works.

### MVG Scope Includes:

- Public browsing of needs, offers, and organizations (no login required)
- User registration and authentication
- Verified organization accounts
- Posting needs (orgs) and offers (anyone)
- Volunteer opportunity listings
- Basic logistics coordination (pickup/delivery)
- Cause tagging for discovery
- Trust & safety reporting
- Admin verification and moderation tools

### Explicitly Out of Scope (Phase 1):

- Monetary donations or payment processing
- Social feeds, comments, likes, or sharing
- Native mobile apps
- AI-powered matching
- Multi-state expansion

Restraint is a feature, not a limitation.

---

## Tech Stack

The Giving Grid is built with a straightforward, maintainable stack:

| Layer | Technology |
|-------|------------|
| Markup | HTML5 (semantic) |
| Styling | CSS3 (mobile-first, accessible) |
| Interactivity | JavaScript ES6+ (vanilla) |
| Backend | PHP 8+ |
| Database | MySQL 8+ |
| Architecture | MVC-lite with front controller |

No frameworks. No unnecessary dependencies. Hand-coded for performance, accessibility, and long-term maintainability.

---

## Geographic Scope

| Phase | Region |
|-------|--------|
| Phase 1 | Knoxville & East Tennessee counties |
| Phase 2 | Expanded East Tennessee |
| Phase 3 | Statewide Tennessee |

Starting small allows for faster validation, easier moderation, and stronger community relationships.

---

## Project Structure

```
giving-grid/
├── public/           # Web root (front controller, assets)
├── app/
│   ├── Controllers/  # Request handlers
│   ├── Models/       # Data structures
│   ├── Views/        # Templates (layouts, partials, pages)
│   ├── Services/     # Business logic (DB, auth, listings)
│   ├── Middleware/   # Auth, CSRF, role checks
│   ├── Validation/   # Input validation
│   └── Helpers/      # Utility functions
├── config/           # App, database, routes, constants
├── database/         # Schema, seeds, migrations
├── storage/          # Logs, cache, uploads
└── .env              # Environment variables (not committed)
```

---

## Getting Started

### Requirements

- PHP 8.0+
- MySQL 8.0+
- Apache with mod_rewrite (or nginx equivalent)
- Composer (optional, for autoloading)

### Local Setup

```bash
# Clone the repository
git clone https://github.com/hcweblabs/giving-grid.git
cd giving-grid

# Copy environment template
cp .env.example .env

# Configure your database credentials in .env

# Import the database schema
mysql -u your_user -p your_database < database/schema.sql

# Seed with demo data (optional)
mysql -u your_user -p your_database < database/seeds/demo.sql

# Point your local server's document root to /public
```

### First Run

Navigate to `http://localhost` (or your local dev URL). You should see the homepage.

---

## Success Metrics

The MVG will be considered successful if:

- Organizations actively post real needs
- At least one cross-community fulfillment occurs
- Volunteers participate more than once
- Listings are completed, not abandoned
- Users understand the system without explanation

If people use it naturally, we've won.

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

*Built with care for East Tennessee. People over profit.*
