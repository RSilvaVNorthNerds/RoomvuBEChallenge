# Roomvu Backend Challenge

A PHP-based backend service for Roomvu's online assessment

## Prerequisites

- Docker and Docker Compose
- PHP 8.1 or higher
- Composer

## Getting Started

1. Clone the repository:
```bash
git clone <repository-url>
cd roomvu-backend-challenge
```

2. Install dependencies:
```bash
composer install
```

3. Start the containerized development environment:
```bash
composer start:dev
```

The service will be available at `http://localhost:8000`

## Development Commands

- Start development environment: `composer start:dev`
- Stop development environment: `composer stop:dev`
- View logs: `composer logs:dev`
- Restart services: `composer restart:dev`
- Run tests: `composer test`
- Generate test coverage: `composer test:coverage`

## Project Structure

```
├── src/              # Source code
├── tests/            # Test files
├── public/           # Public assets
├── database/         # Database files
├── vendor/           # Composer dependencies
├── docker-compose.yml # Docker configuration
└── Dockerfile        # Application container definition
```

## Environment Configuration

The service uses the following environment variables:
- `DB_CONNECTION`: Database connection type (default: sqlite)
- `DB_DATABASE`: Database file path
- `REDIS_HOST`: Redis host (default: redis)
- `REDIS_PORT`: Redis port (default: 6379)

## Dependencies

- PHP 8.1+
- Symfony Console 6.0+
- Symfony Cache 6.0+
- FakerPHP 1.19+
- Symfony HTTP Foundation 7.2+
- PHPUnit 10.0+ (for testing)

## Testing

Run the test suite:
```bash
composer test
```

Generate test coverage report:
```bash
composer test:coverage
```

The coverage report will be available in the `coverage` directory.
