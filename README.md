# Laravel Radarr

Laravel integration for the [Radarr PHP SDK](https://github.com/martincamen/radarr-php), providing a seamless experience for interacting with Radarr using unified domain models from [php-arr-core](https://github.com/martincamen/php-arr-core).

## Features

- Unified API using canonical domain models from `php-arr-core`
- Type-safe interactions with Radarr
- Laravel facade with full IDE autocompletion
- Testing utilities for mocking responses
- Automatic service discovery via Laravel's package auto-discovery

## Requirements

- PHP 8.3+
- Laravel 10.0+, 11.0+ or 12.0+

## Installation

```bash
composer require martincamen/laravel-radarr
```

The package will auto-register its service provider in Laravel.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="MartinCamen\LaravelRadarr\RadarrServiceProvider"
```

Add the following environment variables to your `.env` file:

```env
RADARR_HOST=localhost
RADARR_PORT=7878
RADARR_API_KEY=your-api-key
RADARR_USE_HTTPS=false
RADARR_TIMEOUT=30
RADARR_URL_BASE=
```

### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `RADARR_HOST` | Hostname or IP address of your Radarr server | `localhost` |
| `RADARR_PORT` | Port number for your Radarr server | `7878` |
| `RADARR_API_KEY` | Your Radarr API key (Settings > General > Security) | - |
| `RADARR_USE_HTTPS` | Use HTTPS for connections | `false` |
| `RADARR_TIMEOUT` | Request timeout in seconds | `30` |
| `RADARR_URL_BASE` | URL base for reverse proxy subpaths (e.g., `/radarr`) | - |

## Usage

### Using the Facade

The `Radarr` facade provides access to the SDK client, returning canonical domain models from `php-arr-core`:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

// Get all active downloads
$downloads = Radarr::downloads();

// Get all movies
$movies = Radarr::movies();

// Get a specific movie by ID
$movie = Radarr::movie(1);

// Get system information
$status = Radarr::systemSummary();
```

### Dependency Injection

You can also inject `Radarr` directly:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

class MovieController
{
    public function __construct(private Radarr $radarr) {}

    public function index()
    {
        return view('movies.index', ['movies' => $this->radarr->movies()]);
    }
}
```

## Working with Downloads

The `downloads()` method returns a `DownloadItemCollection` containing all active downloads:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;

/** @var DownloadItemCollection $downloads */
$downloads = Radarr::downloads();

// Check if there are any downloads
if ($downloads->isEmpty()) {
    echo 'No active downloads';
}

// Get the count
echo "Active downloads: {$downloads->count()}";

// Filter by status
$activeDownloads = $downloads->active();
$completedDownloads = $downloads->completed();
$failedDownloads = $downloads->failed();

// Get downloads with errors
$withErrors = $downloads->withErrors();

// Sort by priority (errors first, then active, then waiting)
$sorted = $downloads->sortByPriority();

// Get total size and progress
$totalSize = $downloads->totalSize();
$remaining = $downloads->totalRemaining();
$progress = $downloads->totalProgress();

echo "Overall progress: {$progress->percentage()}%";
```

## Working with Movies

The `movies()` method returns an array of `Movie` domain objects:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\ArrCore\Domain\Media\Movie;

// Get all movies
/** @var Movie[] $movies */
$movies = Radarr::movies();

foreach ($movies as $movie) {
    echo "{$movie->title} ({$movie->year})";

    // Check movie status
    if ($movie->isReleased()) {
        echo ' - Released';
    }

    // Check if downloadable
    if ($movie->isDownloadable()) {
        echo ' - Missing, ready to download';
    }

    // Access metadata
    echo "Size on disk: {$movie->sizeOnDisk?->formatted()}";
    echo "IMDb: {$movie->imdbUrl()}";
    echo "TMDb: {$movie->tmdbUrl()}";
}

// Get a specific movie
$movie = Radarr::movie(1);

echo $movie->title;
```


## System information

```php
// Get system status information
$system = Radarr::system()->status();

echo $system->version;

// Get system health information
$health = Radarr::system()->health();

foreach ($health->warnings() as $warning) {
    echo $warning->type . ': ' . $warning->message;
}

// Get disk space information
$diskSpace = Radarr::system()->diskSpace();

echo $diskSpace->totalFreeSpace();

// Get system tasks information
Radarr::system()->tasks();
Radarr::system()->task(id: 1);

// Get available backups
Radarr::system()->backups();
```

## System summary

The `systemSummary()` method returns a `SystemStatus` object with combined status information and health information:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

$status = Radarr::systemSummary();

echo "Radarr Version: {$status->version}";
echo "Branch: {$status->branch}";
echo "Runtime: {$status->runtimeVersion}";
echo "OS: {$status->osName}";

// Check system health
if ($status->isHealthy) {
    echo 'System is healthy';
} else {
    echo "System has {$status->issueCount()} issues:";

    foreach ($status->healthIssues as $issue) {
        echo "- [{$issue->type->value}] {$issue->message}";
    }
}

// Get uptime
echo "Uptime: {$status->uptime()}";
```

## Domain Models

All responses use canonical domain models from `php-arr-core`, providing a unified interface across all *arr services:

| Model | Description |
|-------|-------------|
| `DownloadItemCollection` | Collection of active downloads |
| `DownloadItem` | Individual download with status, progress, size |
| `Movie` | Movie with metadata, status, and file information |
| `SystemStatus` | System status with health issues |
| `HealthIssue` | Individual health check issue |

### Value Objects

The domain models use strongly-typed value objects:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

$downloads = Radarr::downloads();

foreach ($downloads as $download) {
    // FileSize value object
    $size = $download->size;
    echo $size->bytes();        // Raw bytes
    echo $size->formatted();    // "1.5 GB"

    // Progress value object
    $progress = $download->progress;
    echo $progress->value();       // 0.75
    echo $progress->percentage();  // 75.0
    echo $progress->formatted();   // "75%"

    // Duration value object
    $eta = $download->estimatedTime;
    echo $eta?->formatted();  // "2h 15m"
}
```

## Testing

### Using the Fake

The package provides `RadarrFake` for testing:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

class MovieTest extends TestCase
{
    public function testDisplaysDownloads(): void
    {
        // Create a fake instance
        $fake = Radarr::fake();

        // Make request
        $response = $this->get('/downloads');

        // Assert the method was called
        $fake->assertCalled('downloads');
        $response->assertOk();
    }

    public function testGetsSpecificMovie(): void
    {
        $fake = Radarr::fake();

        // Make request that calls movie(5)
        $this->get('/movies/5');

        // Assert called with specific parameters
        $fake->assertCalledWith('movie', ['id' => 5]);
    }

    public function testNothingWasCalled(): void
    {
        $fake = Radarr::fake();

        // No API calls made
        $this->get('/about');

        $fake->assertNothingCalled();
    }
}
```

### Custom Responses

You can provide custom responses to the fake:

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\Radarr\Testing\Factories\DownloadFactory;
use MartinCamen\Radarr\Testing\Factories\MovieFactory;
use MartinCamen\Radarr\Testing\Factories\SystemStatusFactory;

public function testWithCustomMovies(): void
{
    Radarr::fake([
        'movies' => MovieFactory::makeMany(10),
    ]);

    $response = $this->get('/movies');

    $response->assertOk();
    $response->assertViewHas('movies');
}

public function testWithCustomDownloads(): void
{
    Radarr::fake([
        'downloads' => [
            'page'         => 1,
            'pageSize'     => 10,
            'totalRecords' => 2,
            'records'      => DownloadFactory::makeMany(2),
        ],
    ]);

    $response = $this->get('/downloads');

    $response->assertOk();
}

public function testWithCustomSystemStatus(): void
{
    Radarr::fake([
        'systemStatus'     => SystemStatusFactory::make([
            'version'      => '5.0.0.0',
            'isProduction' => true,
        ]),
    ]);

    $response = $this->get('/system');

    $response->assertSee('5.0.0.0');
}
```

### Assertion Methods

The fake provides several assertion methods:

```php
$fake = Radarr::fake();

// Assert a method was called
$fake->assertCalled('downloads');

// Assert a method was not called
$fake->assertNotCalled('movies');

// Assert a method was called with specific parameters
$fake->assertCalledWith('movie', ['id' => 5]);

// Assert a method was called a specific number of times
$fake->assertCalledTimes('downloads', 3);

// Assert nothing was called
$fake->assertNothingCalled();

// Get all recorded calls
$calls = $fake->getCalls();
```

## Example: Building a Dashboard

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;

class DashboardController extends Controller
{
    public function index()
    {
        // Get system status
        $status = Radarr::systemStatus();

        // Get active downloads
        $downloads = Radarr::downloads();

        // Get all movies
        $movies = Radarr::movies();

        // Filter movies for display
        $missingMovies = array_filter(
            $movies,
            fn ($movie) => $movie->isDownloadable(),
        );

        return view('dashboard', [
            'status'        => $status,
            'downloads'     => $downloads->sortByPriority(),
            'downloadCount' => $downloads->count(),
            'movieCount'    => count($movies),
            'missingCount'  => count($missingMovies),
            'missingMovies' => array_slice($missingMovies, 0, 10),
        ]);
    }
}
```

## Error Handling

```php
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\Radarr\Exceptions\{
    AuthenticationException,
    ConnectionException,
    NotFoundException,
};

try {
    $movie = Radarr::movie(999);
} catch (AuthenticationException $e) {
    // Invalid API key
    return back()->with('error', 'Invalid Radarr API key');
} catch (NotFoundException $e) {
    // Movie not found
    abort(404, 'Movie not found');
} catch (ConnectionException $e) {
    // Connection error
    logger()->error('Could not connect to Radarr: ' . $e->getMessage());

    return back()->with('error', 'Radarr server unavailable');
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

Built on top of the [Radarr PHP SDK](https://github.com/martincamen/radarr-php) and [php-arr-core](https://github.com/martincamen/php-arr-core).
