<?php

namespace MartinCamen\LaravelRadarr\Facades;

use Illuminate\Support\Facades\Facade;
use MartinCamen\ArrCore\Actions\SystemActions;
use MartinCamen\ArrCore\Actions\WantedActions;
use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;
use MartinCamen\ArrCore\Domain\Media\Movie;
use MartinCamen\ArrCore\Domain\System\SystemSummary;
use MartinCamen\Radarr\Actions\CalendarActions;
use MartinCamen\Radarr\Actions\CommandActions;
use MartinCamen\Radarr\Actions\HistoryActions;
use MartinCamen\Radarr\Radarr as CoreRadarr;
use MartinCamen\Radarr\Testing\RadarrFake;

/**
 * @method static DownloadItemCollection downloads()
 * @method static array<int, Movie> movies()
 * @method static Movie movie(int $id)
 * @method static SystemActions system()
 * @method static SystemSummary systemSummary()
 * @method static CalendarActions calendar()
 * @method static HistoryActions history()
 * @method static WantedActions wanted()
 * @method static CommandActions command()
 *
 * @see CoreRadarr
 */
class Radarr extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param array<string, mixed> $responses
     */
    public static function fake(array $responses = []): RadarrFake
    {
        static::swap($radarrFake = new RadarrFake($responses));

        return $radarrFake;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'radarr';
    }
}
