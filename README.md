# Geoprofs Project

Dit project is ontwikkeld in het kader van de Geoprofs-casus.  
Het betreft een webapplicatie met een Laravel backend en een React frontend via Inertia.js.

## Doel van het project

Het doel van dit project is het ontwikkelen van een full-stack webapplicatie waarbij moderne webtechnologieën worden toegepast. De focus ligt op een duidelijke scheiding tussen backend en frontend, goede ontwikkelstructuur en testbaarheid.

---

## Tech Stack

### Backend
- Laravel 12
- PHP 8.2
- Laravel Sanctum (authenticatie)
- L5-Swagger (API documentatie)

### Frontend
- React 18
- Inertia.js
- Vite
- Tailwind CSS

### Testing
- Cypress (end-to-end tests)
- Playwright

---

## Projectstructuur
Geoprofs/

├── app/                # Laravel applicatielogica

├── resources/
│   ├── js/             # React frontend (Inertia)
│   └── views/          # Blade templates

├── routes/             # Web- en API-routes

├── database/           # Migrations en database configuratie

├── public/             # Publieke assets

├── tests/              # Backend tests

├── cypress/            # Frontend E2E tests

├── docker-compose.yml  # Docker configuratie

├── package.json        # Frontend dependencies

├── composer.json       # Backend dependencies

└── vite.config.js      # Vite configuratie

---

## Installatie

### Vereisten
- PHP ^8.2
- Composer
- Node.js & npm
- (Optioneel) Docker

### Installatiestappen

1. Clone de repository
   Bash: git clone [https://github.com/loekvdloo/Geoprofs.git]
   cd Geoprofs

### Installeer backend dependencies
    composer install

### Installeer frontend dependencies
    npm install

### Omgevingsvariabelen instellen
    cp .env.example .env
    php artisan key:generate

### Database migreren
    php artisan migrate

## Development

### Backend en frontend tegelijk starten:
    composer run dev

    afzonderlijk:
    php artisan serve
    npm run dev

## Testing

### Backend tests
    php artisan test

### Frontend tests
    npx cypress open
    npx playwright test

## API Documentatie

### De API documentatie is beschikbaar via Swagger:
    /api/documentation

## Docker

### Voor lokale ontwikkeling kan Docker worden gebruikt.
    docker-compose up -d

