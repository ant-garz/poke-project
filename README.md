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

The Pokémon import system is fully asynchronous and queue-driven.

### Step 1 — CSV Upload

An administrator uploads a Pokémon CSV file through the administrative interface.

A new import batch record is created to track progress and processing statistics.

### Step 2 — Import Job Dispatch

The system dispatches `ImportPokemonCsvJob`.

This job:

* Reads the CSV file
* Calculates total row count
* Marks the batch as processing
* Dispatches a `ParsePokemonCsvRowJob` for each row
* Schedules a batch finalization job

### Step 3 — Row Processing

Each `ParsePokemonCsvRowJob` processes a single CSV row independently.

The job:

* Validates required CSV fields
* Creates or updates the Pokémon record
* Normalizes Pokémon types
* Assigns primary and secondary type relationships
* Stores canonical species data
* Updates import tracking counters
* Dispatches enrichment jobs

### Step 4 — External Enrichment

After a Pokémon record is created or updated, the system dispatches `EnrichPokemonFromExternalApisJob`.

This orchestrator job triggers external data synchronization from multiple sources:

#### PokeAPI

Used to enrich Pokémon with:

* Species metadata
* Descriptions
* Artwork
* Sprites
* Cries
* Physical measurements

#### TCGDex

Used to enrich Pokémon with:

* Trading card records
* Card artwork
* Card sets
* Card attacks
* Market pricing data
* Additional card metadata

### Step 5 — TCGDex Card Processing

`FetchTcgdexDataJob` retrieves matching card references from TCGDex.

Card identifiers are chunked into smaller batches and processed asynchronously through `ProcessTcgdexCardBatchJob`.

Each batch job:

* Retrieves complete card payloads
* Creates or updates card sets
* Creates or updates cards
* Rebuilds card attacks
* Stores pricing information
* Preserves raw TCGDex payloads

This approach prevents long-running jobs and allows large card collections to be processed safely.

### Step 6 — Import Finalization

`FinalizePokemonImportBatchJob` evaluates import progress using tracked counters.

A batch is considered complete when:

```text
(processed_rows + failed_rows) >= total_rows
```

Import completion only reflects CSV ingestion status.

External enrichment jobs may continue running after the import batch itself has been finalized.

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
