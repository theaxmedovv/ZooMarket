<?php

namespace Tests;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    use DatabaseMigrations;

    private const PORT = 8010;

    private static ?Process $server = null;

    /**
     * Start ChromeDriver and the app's own web server for the browser to talk to.
     *
     * The server runs in the "local" environment (so CSRF protection is really on, unlike "testing")
     * but on the test database: DatabaseMigrations wipes it, so it must never be the real one.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }

        if (self::$server !== null) {
            return;
        }

        $php = (new PhpExecutableFinder)->find(false);
        $root = dirname(__DIR__);

        self::$server = new Process(
            [$php, '-S', '127.0.0.1:' . self::PORT, '-t', $root . '/public', $root . '/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'],
            $root . '/public', // server.php serves from its working directory
            [
                'APP_ENV' => 'local',
                'APP_DEBUG' => 'true',
                'APP_URL' => 'http://127.0.0.1:' . self::PORT,
                'DB_CONNECTION' => 'mysql',
                'DB_DATABASE' => 'zoobozor_test',
                'DB_HOST' => '127.0.0.1',
                'DB_USERNAME' => 'root',
                'DB_PASSWORD' => '',
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'file',
                'QUEUE_CONNECTION' => 'sync',
                'MAIL_MAILER' => 'array',
                'GROQ_API_KEY' => '',
                'BCRYPT_ROUNDS' => '4',
            ],
        );
        self::$server->start();

        register_shutdown_function(fn () => self::$server?->stop());

        // Wait until it answers
        for ($i = 0; $i < 50; $i++) {
            if (@fsockopen('127.0.0.1', self::PORT, $errno, $errstr, 0.2)) {
                return;
            }
            usleep(100_000);
        }
    }

    /**
     * DatabaseMigrations runs migrate:fresh and a rollback: refuse to do that to anything but the test database.
     * (Without .env.dusk.local the run would fall back to .env, i.e. the real data.)
     */
    protected function beforeRefreshingDatabase(): void
    {
        $database = config('database.connections.' . config('database.default') . '.database');

        if ($database !== 'zoobozor_test') {
            throw new \RuntimeException("Dusk would wipe the \"{$database}\" database. Browser tests only run on zoobozor_test; check .env.dusk.local.");
        }
    }

    /**
     * The browser always talks to the server started above, whatever APP_URL says.
     */
    protected function baseUrl(): string
    {
        return 'http://127.0.0.1:' . self::PORT;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // loginAs() builds its URL with route(): it has to point at the test server too
        config(['app.url' => $this->baseUrl()]);
        URL::forceRootUrl($this->baseUrl());

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // A fresh browser for the next test: no cookies, so nobody stays logged in from the previous one
        static::closeAll();
    }

    /**
     * A real 375x812 phone: Chrome will not shrink a window below ~500px, so emulate the screen instead.
     */
    protected function phone(Browser $browser): Browser
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Emulation.setDeviceMetricsOverride',
            'params' => ['width' => 375, 'height' => 812, 'deviceScaleFactor' => 2, 'mobile' => true],
        ]);

        return $browser;
    }

    protected function desktop(Browser $browser): Browser
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Emulation.clearDeviceMetricsOverride',
            'params' => new \stdClass,
        ]);

        return $browser;
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1440,900',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    // ---- data helpers shared by the browser tests ----

    protected function makeUser(string $role = 'user', array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    protected function makePost(User $seller, array $attributes = []): Post
    {
        return Post::create($attributes + [
            'user_id' => $seller->id,
            'category_id' => Category::firstOrCreate(['name' => 'Mushuk'])->id,
            'title' => 'Britan mushugi',
            'content' => 'Sog\'lom mushukcha',
            'breed' => 'Britan',
            'quantity' => 2,
            'gender' => 'mixed',
            'male_quantity' => 1,
            'female_quantity' => 1,
            'price' => 900000,
            'currency' => 'UZS',
            'region' => 'Toshkent shahri',
            'location' => 'Toshkent',
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);
    }
}
