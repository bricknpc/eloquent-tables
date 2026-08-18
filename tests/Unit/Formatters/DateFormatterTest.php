<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Formatters;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Exceptions\InvalidValueException;

/**
 * @internal
 */
#[CoversClass(DateFormatter::class)]
#[UsesClass(InvalidValueException::class)]
class DateFormatterTest extends TestCase
{
    private readonly DateFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = $this->app->make(DateFormatter::class);
    }

    #[DataProvider('carbonDateProvider')]
    public function test_it_can_format_carbon_dates(Carbon $date, string $expected): void
    {
        $model = new class extends Model {};

        $result = $this->formatter->format($date, $model);

        $this->assertSame($expected, $result->__toString());
    }

    public static function carbonDateProvider(): \Generator
    {
        Carbon::setTestNow('2025-11-13 11:00:00');

        yield [
            Carbon::now(),
            'Thursday, November 13, 2025',
        ];

        yield [
            Carbon::now()->addDays(5),
            'Tuesday, November 18, 2025',
        ];

        yield [
            Carbon::now()->subDays(5),
            'Saturday, November 8, 2025',
        ];

        yield [
            Carbon::now()->addMonths(5),
            'Monday, April 13, 2026',
        ];

        yield [
            Carbon::now()->subMonths(5),
            'Friday, June 13, 2025',
        ];

        yield [
            Carbon::now()->addYears(5),
            'Wednesday, November 13, 2030',
        ];

        yield [
            Carbon::now()->subYears(5),
            'Friday, November 13, 2020',
        ];
    }

    #[DataProvider('dateTimeDateProvider')]
    public function test_it_can_format_datetime_dates(\DateTime $date, string $expected): void
    {
        $model = new class extends Model {};

        $result = $this->formatter->format($date, $model);

        $this->assertSame($expected, $result->__toString());
    }

    public static function dateTimeDateProvider(): \Generator
    {
        yield [
            new \DateTime('2025-11-13 11:00:00'),
            'Thursday, November 13, 2025',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->add(new \DateInterval('P5D')),
            'Tuesday, November 18, 2025',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->sub(new \DateInterval('P5D')),
            'Saturday, November 8, 2025',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->add(new \DateInterval('P5M')),
            'Monday, April 13, 2026',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->sub(new \DateInterval('P5M')),
            'Friday, June 13, 2025',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->add(new \DateInterval('P5Y')),
            'Wednesday, November 13, 2030',
        ];

        yield [
            new \DateTime('2025-11-13 11:00:00')->sub(new \DateInterval('P5Y')),
            'Friday, November 13, 2020',
        ];
    }

    #[DataProvider('timestampDateProvider')]
    public function test_it_can_format_timestamp_dates(int $date, string $expected): void
    {
        $model = new class extends Model {};

        $result = $this->formatter->format($date, $model);

        $this->assertSame($expected, $result->__toString());
    }

    public static function timestampDateProvider(): \Generator
    {
        $now = mktime(0, 0, 0, 11, 13, 2025);

        yield [
            -1,
            'Wednesday, December 31, 1969',
        ];

        yield [
            $now,
            'Thursday, November 13, 2025',
        ];

        yield [
            $now + (5 * 24 * 60 * 60),
            'Tuesday, November 18, 2025',
        ];

        yield [
            $now - (5 * 24 * 60 * 60),
            'Saturday, November 8, 2025',
        ];

        yield [
            $now + (5 * 30 * 24 * 60 * 60),
            'Sunday, April 12, 2026',
        ];

        yield [
            $now - (5 * 30 * 24 * 60 * 60),
            'Monday, June 16, 2025',
        ];

        yield [
            $now + (4 * 365 * 24 * 60 * 60) + (366 * 24 * 60 * 60),
            'Wednesday, November 13, 2030',
        ];

        yield [
            $now - (4 * 365 * 24 * 60 * 60) - (366 * 24 * 60 * 60),
            'Friday, November 13, 2020',
        ];
    }

    #[DataProvider('invalidDateProvider')]
    public function test_invalid_data_results_in_exception(mixed $invalidDate): void
    {
        $model = new class extends Model {};

        $this->expectException(InvalidValueException::class);

        $this->formatter->format($invalidDate, $model);
    }

    public static function invalidDateProvider(): \Generator
    {
        yield [
            'now',
        ];

        yield [
            'tomorrow',
        ];

        yield [
            'yesterday',
        ];

        yield [
            'November 13, 2025',
        ];

        yield [
            true,
        ];

        yield [
            false,
        ];
    }

    public function test_it_accepts_a_string_timezone(): void
    {
        $formatter = new DateFormatter('en_US', 'Asia/Tokyo');

        $model = new class extends Model {};

        $date = new \DateTimeImmutable('2026-01-01 23:00:00', new \DateTimeZone('UTC'));

        // 23:00 UTC is already the 2nd in Tokyo, so the date itself proves the zone was applied.
        $this->assertStringContainsString('January 2, 2026', $formatter->format($date, $model)->__toString());
    }

    public function test_it_accepts_a_date_time_zone_object(): void
    {
        $formatter = new DateFormatter('en_US', new \DateTimeZone('Asia/Tokyo'));

        $model = new class extends Model {};

        $date = new \DateTimeImmutable('2026-01-01 23:00:00', new \DateTimeZone('UTC'));

        $this->assertStringContainsString('January 2, 2026', $formatter->format($date, $model)->__toString());
    }

    public function test_an_unknown_string_timezone_throws(): void
    {
        $formatter = new DateFormatter('en_US', 'Not/AZone');

        $model = new class extends Model {};

        $this->expectException(InvalidValueException::class);

        $formatter->format(new \DateTimeImmutable(), $model);
    }

    public function test_it_formats_the_same_value_with_and_without_a_model(): void
    {
        $formatter = new DateFormatter('en_US', 'UTC');

        $this->assertSame(
            (string) $formatter->format(Carbon::parse('2026-08-17 12:00:00'), new TestModel()),
            (string) $formatter->format(Carbon::parse('2026-08-17 12:00:00')),
        );
    }

    public function test_it_formats_a_value_with_no_model_at_all(): void
    {
        $this->assertNotSame(
            '',
            (string) new DateFormatter('en_US', 'UTC')->format(Carbon::parse('2026-08-17 12:00:00')),
        );
    }
}
