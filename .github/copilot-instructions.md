# Symfony Restaurant App - AI Coding Guidelines

## Project Overview
This is a Symfony 5.4 application for a restaurant management system. Currently minimal, with a single home controller serving a welcome message.

## Architecture
- **Framework**: Symfony 5.4 with Flex for dependency management
- **Structure**: Standard Symfony directory layout (src/ for code, config/ for configuration, public/ for web assets, var/ for cache/logs)
- **Routing**: Annotation-based routes in controllers (e.g., `#[Route('/')]` in HomeController)
- **Services**: Autowired and autoconfigured via `config/services.yaml` - classes in `src/` are automatically registered
- **No database or entities yet** - pure MVC pattern without data persistence

## Key Workflows
- **Development server**: `symfony server:start -d` (starts in background)
- **Cache management**: `bin/console cache:clear` (clears dev cache in var/cache/dev/)
- **Dependencies**: `composer install` (installs to vendor/)

## Conventions
- **Controllers**: Extend `AbstractController`, place in `src/Controller/`, use route annotations
- **Responses**: Return `Response` objects directly (import `Symfony\Component\HttpFoundation\Response`)
- **Language**: French for user-facing content (e.g., "Bienvenue sur votre accueil !")
- **Configuration**: YAML files in `config/` for routes, services, packages
- **Autoload**: PSR-4 with `App\` namespace mapped to `src/`

## Examples
- Route definition: `#[Route('/')]` above a public method in a controller
- Response creation: `return new Response('content');`
- Service injection: Automatic via constructor parameters in autowired classes

## Integration Points
- None currently - standalone web application
- Future: Likely database integration with Doctrine for restaurant data (menus, orders, etc.)