# Pokemon Index

Pokemon Index is a personal, non-commercial project focused on building a Pokémon species database and discovery platform.

The application uses Pokémon species as its central domain model and enriches that data from multiple external sources.

## Project Goals

* Maintain a canonical Pokémon species dataset
* Import and manage Pokémon data through an administrative interface
* Enrich species with external API data
* Store trading card information separately from species data
* Support future features such as abilities, evolutions, encounters, and advanced search
* Explore scalable Laravel architecture patterns including queues, background processing, and data ingestion pipelines

## Non-Commercial Disclaimer

This project is a personal educational and portfolio project.

It is not affiliated with, endorsed by, or sponsored by Nintendo, Game Freak, Creatures Inc., The Pokémon Company, PokeAPI, Pokémon Database, or TCGDex.

All Pokémon-related names, artwork, trademarks, and intellectual property belong to their respective owners.

No commercial use is intended.

## Technology Stack

### Backend

* Laravel 13
* PHP 8.4
* MySQL
* Laravel Queues
* Spatie Laravel Permission

### Frontend

* Svelte 5
* TypeScript
* Inertia.js
* Tailwind CSS

### Development Environment

* Laravel Sail
* Docker
* WSL

## Core Architecture

### Pokémon as the Source of Truth

The Pokémon species table is the primary domain entity.

Everything else exists to enrich or relate back to a Pokémon species.

Examples include:

* Types
* Abilities
* Trading Cards
* External API mappings
* Import metadata

### Canonical Data Source

Initial Pokémon data is imported from a structured CSV export generated from Pokémon Database.

The CSV acts as the authoritative source for:

* Pokédex number
* Name
* Primary typing
* Secondary typing
* Base stats

External APIs do not overwrite canonical imported data.

### External Enrichment Sources

#### PokeAPI

Used for:

* Species metadata
* Descriptions
* Sprites
* Artwork
* Cries
* Dimensions
* Future evolution and move data

#### TCGDex

Used for:

* Trading card metadata
* Card artwork
* Card rarity
* Card sets
* Card attacks
* Additional Pokémon descriptions

## Data Model

### Pokémon

Stores:

* Species identity
* Base stats
* Description
* Artwork and sprite URLs
* Synchronization metadata

### Types

Normalized Pokémon type records.

Each Pokémon may have:

* Primary Type
* Secondary Type

### Cards

Trading card records associated with Pokémon.

Cards are treated as separate domain entities rather than extensions of Pokémon species.

### Card Sets

Represents Pokémon Trading Card Game expansions and releases.

### External Mappings

Stores relationships between internal Pokémon records and external systems such as:

* PokeAPI
* TCGDex

## Import Pipeline

### Step 1

Administrator uploads a CSV file.

### Step 2

A batch record is created and tracked.

### Step 3

The import job reads the CSV and dispatches individual row-processing jobs.

### Step 4

Each row job:

* Creates or updates a Pokémon
* Resolves types
* Updates canonical species data
* Dispatches enrichment jobs

### Step 5

External enrichment jobs:

* Fetch data from PokeAPI
* Fetch data from TCGDex
* Store raw API payloads
* Update enrichment fields

## Queue Processing

Imports are processed asynchronously using Laravel queues.

Run a queue worker locally:

```bash
./vendor/bin/sail artisan queue:work
```

For development environments:

```bash
./vendor/bin/sail artisan queue:work --tries=1
```

## Import Batch Tracking

Each import batch tracks:

* Upload status
* Total rows
* Processed rows
* Failed rows

Progress is calculated using:

```text
(processed_rows + failed_rows) / total_rows
```

This allows progress monitoring without storing a separate progress column.

## API Rate Limiting

External services are rate limited globally.

Current limits:

* PokeAPI: 20 requests per minute
* TCGDex: 20 requests per minute

Rate limiting is enforced within dedicated API client services.

## Raw Payload Storage

External API responses are preserved as JSON payloads.

Benefits include:

* Auditing
* Reprocessing
* Future schema expansion
* Easier debugging

## Current Status

Implemented:

* Authentication
* Role and permission management
* Administrative dashboard
* CSV Pokémon imports
* Import batch tracking
* Queue-based ingestion
* Type normalization
* PokeAPI enrichment
* TCGDex enrichment
* External source mapping foundation

Planned:

* Abilities
* Evolution chains
* Advanced filtering
* Search improvements
* Scheduled re-enrichment
* Card browsing experience
* Public Pokémon index pages

## Development

Install dependencies:

```bash
composer install
npm install
```

Start containers:

```bash
./vendor/bin/sail up -d
```

Run migrations:

```bash
./vendor/bin/sail artisan migrate
```

Start frontend:

```bash
npm run dev
```

Run queue worker:

```bash
./vendor/bin/sail artisan queue:work
```

## License

This repository is provided for educational and personal portfolio purposes.
