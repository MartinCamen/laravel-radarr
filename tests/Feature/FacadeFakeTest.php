<?php

use MartinCamen\ArrCore\Domain\Download\DownloadItemCollection;
use MartinCamen\ArrCore\Domain\Media\Movie;
use MartinCamen\ArrCore\Domain\System\SystemSummary;
use MartinCamen\LaravelRadarr\Facades\Radarr;
use MartinCamen\Radarr\Testing\Factories\DownloadFactory;
use MartinCamen\Radarr\Testing\Factories\MovieFactory;
use MartinCamen\Radarr\Testing\Factories\SystemStatusFactory;
use MartinCamen\Radarr\Testing\RadarrFake;

describe('Facade Fake', function (): void {
    it('can swap the facade for a fake', function (): void {
        $fake = Radarr::fake();

        expect($fake)->toBeInstanceOf(RadarrFake::class)
            ->and(Radarr::getFacadeRoot())->toBeInstanceOf(RadarrFake::class);
    });

    it('can assert nothing was called', function (): void {
        Radarr::fake();

        Radarr::getFacadeRoot()->assertNothingCalled();
    });
});

describe('Downloads', function (): void {
    it('can get downloads', function (): void {
        Radarr::fake();

        $downloads = Radarr::downloads();

        expect($downloads)->toBeInstanceOf(DownloadItemCollection::class);
        Radarr::getFacadeRoot()->assertCalled('downloads');
    });

    it('can provide custom response for downloads', function (): void {
        Radarr::fake([
            'downloads' => [
                'page'         => 1,
                'pageSize'     => 10,
                'totalRecords' => 2,
                'records'      => DownloadFactory::makeMany(2),
            ],
        ]);

        $downloads = Radarr::downloads();

        expect($downloads)->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->count())->toBe(2);
    });

    it('download collection provides filter methods', function (): void {
        Radarr::fake();

        $downloads = Radarr::downloads();

        expect($downloads->active())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->completed())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->failed())->toBeInstanceOf(DownloadItemCollection::class)
            ->and($downloads->sortByPriority())->toBeInstanceOf(DownloadItemCollection::class);
    });
});

describe('Movies', function (): void {
    it('can get all movies', function (): void {
        Radarr::fake();

        $movies = Radarr::movies();

        expect($movies)->toBeArray()
            ->not->toBeEmpty()
            ->each->toBeInstanceOf(Movie::class);
        Radarr::getFacadeRoot()->assertCalled('movies');
    });

    it('can get movie by id', function (): void {
        Radarr::fake();

        $movie = Radarr::movie(1);

        expect($movie)->toBeInstanceOf(Movie::class);
        Radarr::getFacadeRoot()->assertCalled('movie');
        Radarr::getFacadeRoot()->assertCalledWith('movie', ['id' => 1]);
    });

    it('can provide custom response for movies', function (): void {
        Radarr::fake([
            'movies' => [
                MovieFactory::make(1, ['title' => 'The Matrix', 'year' => 1999]),
                MovieFactory::make(2, ['title' => 'Inception', 'year' => 2010]),
            ],
        ]);

        $movies = Radarr::movies();

        expect($movies)->toHaveCount(2)
            ->and($movies[0]->title)->toBe('The Matrix')
            ->and($movies[0]->year)->toBe(1999)
            ->and($movies[1]->title)->toBe('Inception')
            ->and($movies[1]->year)->toBe(2010);
    });

    it('can provide custom response for single movie', function (): void {
        Radarr::fake([
            'movie' => MovieFactory::make(5, [
                'title' => 'Interstellar',
                'year'  => 2014,
            ]),
        ]);

        $movie = Radarr::movie(5);

        expect($movie->title)->toBe('Interstellar')
            ->and($movie->year)->toBe(2014);
    });

    it('can provide custom response for specific movie id', function (): void {
        Radarr::fake([
            'movie/10' => MovieFactory::make(10, ['title' => 'Movie Ten']),
            'movie/20' => MovieFactory::make(20, ['title' => 'Movie Twenty']),
        ]);

        $movie10 = Radarr::movie(10);
        $movie20 = Radarr::movie(20);

        expect($movie10->title)->toBe('Movie Ten')
            ->and($movie20->title)->toBe('Movie Twenty');
    });
});

describe('System Status', function (): void {
    it('can get system status', function (): void {
        Radarr::fake();

        $systemSummary = Radarr::systemSummary();

        expect($systemSummary)->toBeInstanceOf(SystemSummary::class);
        Radarr::getFacadeRoot()->assertCalled('systemSummary');
    });

    it('can provide custom response for system status', function (): void {
        Radarr::fake([
            'systemSummary' => SystemStatusFactory::make([
                'version'  => '5.0.0.0',
                'branch'   => 'develop',
                'isDocker' => true,
            ]),
        ]);

        $systemSummary = Radarr::systemSummary();

        expect($systemSummary->version)->toBe('5.0.0.0')
            ->and($systemSummary->branch)->toBe('develop');
    });
});

describe('Fake Assertions', function (): void {
    it('can assert method was called', function (): void {
        Radarr::fake();

        Radarr::systemSummary();

        Radarr::getFacadeRoot()->assertCalled('systemSummary');
    });

    it('can assert method was not called', function (): void {
        Radarr::fake();

        Radarr::systemSummary();

        Radarr::getFacadeRoot()->assertNotCalled('movies');
    });

    it('can assert method was called specific times', function (): void {
        Radarr::fake();

        Radarr::systemSummary();
        Radarr::systemSummary();
        Radarr::systemSummary();

        Radarr::getFacadeRoot()->assertCalledTimes('systemSummary', 3);
    });

    it('can get all recorded calls', function (): void {
        Radarr::fake();

        Radarr::systemSummary();
        Radarr::movies();

        $calls = Radarr::getFacadeRoot()->getCalls();

        expect($calls)->toHaveKey('systemSummary')
            ->toHaveKey('movies')
            ->and($calls['systemSummary'])->toHaveCount(1)
            ->and($calls['movies'])->toHaveCount(1);
    });

    it('can set response after creation', function (): void {
        $fake = Radarr::fake();

        $fake->setResponse('movies', [
            MovieFactory::make(1, ['title' => 'Custom Movie']),
        ]);

        $movies = Radarr::movies();

        expect($movies)->toHaveCount(1)
            ->and($movies[0]->title)->toBe('Custom Movie');
    });
});
