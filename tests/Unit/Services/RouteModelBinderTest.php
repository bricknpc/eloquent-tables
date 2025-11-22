<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Services;

use Mockery\Mock;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use BrickNPC\EloquentTables\Console\Commands\MakeTableCommand;

/**
 * @internal
 */
#[CoversClass(RouteModelBinder::class)]
#[UsesClass(MakeTableCommand::class)]
class RouteModelBinderTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_skips_unnamed_parameters(): void
    {
        /** @var Container|Mock $container */
        $container = $this->mock(Container::class);

        /** @var RouteModelBinder $service */
        $service = $this->app->make(RouteModelBinder::class, [
            'container' => $container,
        ]);

        $object = new class($this) {
            public function __construct(private readonly TestCase $test) {}

            public function method($unnamed)
            {
                return 'result';
            }
        };

        $container->shouldReceive('make')->never();
        $container->shouldReceive('call')->with([$object, 'method'], [])->andReturn('result');

        $result = $service->call($object, 'method');

        $this->assertSame('result', $result);
    }

    public function test_it_skips_non_model_parameters(): void
    {
        /** @var Container|Mock $container */
        $container = $this->mock(Container::class);

        /** @var RouteModelBinder $service */
        $service = $this->app->make(RouteModelBinder::class, [
            'container' => $container,
        ]);

        $object = new class($this) {
            public function __construct(private readonly TestCase $test) {}

            public function method(RouteModelBinder $notModel): string
            {
                return 'result';
            }
        };

        $container->shouldReceive('make')->never();
        $container->shouldReceive('call')->with([$object, 'method'], [])->andReturn('result');

        $result = $service->call($object, 'method');

        $this->assertSame('result', $result);
    }

    public function test_it_skips_model_parameters_if_there_are_no_matching_route_parameters(): void
    {
        /** @var Container|Mock $container */
        $container = $this->mock(Container::class);

        /** @var Mock|Request $request */
        $request = $this->mock(Request::class);

        /** @var RouteModelBinder $service */
        $service = $this->app->make(RouteModelBinder::class, [
            'container' => $container,
            'request'   => $request,
        ]);

        $object = new class($this) {
            public function __construct(private readonly TestCase $test) {}

            public function method(TestModel $model): string
            {
                return 'result';
            }
        };

        $request->shouldReceive('route')->with('model')->andReturn(null);

        $container->shouldReceive('make')->never();
        $container->shouldReceive('call')->with([$object, 'method'], [])->andReturn('result');

        $result = $service->call($object, 'method');

        $this->assertSame('result', $result);
    }

    public function test_it_injects_model_parameters_if_there_are_matching_route_parameters(): void
    {
        /** @var Container|Mock $container */
        $container = $this->mock(Container::class);

        /** @var Mock|Request $request */
        $request = $this->mock(Request::class);

        $model        = new TestModel();
        $model->name  = 'Test Model';
        $model->email = 'test@email.com';
        $model->save();

        /** @var RouteModelBinder $service */
        $service = $this->app->make(RouteModelBinder::class, [
            'container' => $container,
            'request'   => $request,
        ]);

        $object = new class($this) {
            public function __construct(private readonly TestCase $test) {}

            public function method(TestModel $model): string
            {
                return 'result';
            }
        };

        /** @var Mock|Route $route */
        $route = $this->mock(Route::class);

        $route->shouldReceive('bindingFields')->andReturn([]);

        $request->shouldReceive('route')->twice()->with('model')->andReturn(1);
        $request->shouldReceive('route')->with()->once()->andReturn($route);

        $container->shouldReceive('make')->once()->with(TestModel::class)->andReturn($model);
        $container->shouldReceive('call')->withArgs(function (array $callable, array $parameters) {
            $this->assertIsArray($parameters);
            $this->assertArrayHasKey('model', $parameters);
            $this->assertInstanceOf(TestModel::class, $parameters['model']);
            $this->assertTrue($parameters['model']->exists);
            $this->assertSame(1, $parameters['model']->getKey());

            return true;
        })->andReturn('result');

        $result = $service->call($object, 'method');

        $this->assertSame('result', $result);
    }

    public function test_it_injects_model_parameters_if_there_are_matching_route_parameters_by_name(): void
    {
        /** @var Container|Mock $container */
        $container = $this->mock(Container::class);

        /** @var Mock|Request $request */
        $request = $this->mock(Request::class);

        $model        = new TestModel();
        $model->name  = 'Test Model';
        $model->email = 'test@email.com';
        $model->save();

        /** @var RouteModelBinder $service */
        $service = $this->app->make(RouteModelBinder::class, [
            'container' => $container,
            'request'   => $request,
        ]);

        $object = new class($this) {
            public function __construct(private readonly TestCase $test) {}

            public function method(TestModel $model): string
            {
                return 'result';
            }
        };

        /** @var Mock|Route $route */
        $route = $this->mock(Route::class);

        $route->shouldReceive('bindingFields')->andReturn(['model' => 'email']);

        $request->shouldReceive('route')->twice()->with('model')->andReturn('test@email.com');
        $request->shouldReceive('route')->with()->once()->andReturn($route);

        $container->shouldReceive('make')->once()->with(TestModel::class)->andReturn($model);
        $container->shouldReceive('call')->withArgs(function (array $callable, array $parameters) {
            $this->assertIsArray($parameters);
            $this->assertArrayHasKey('model', $parameters);
            $this->assertInstanceOf(TestModel::class, $parameters['model']);
            $this->assertTrue($parameters['model']->exists);
            $this->assertSame(1, $parameters['model']->getKey());

            return true;
        })->andReturn('result');

        $result = $service->call($object, 'method');

        $this->assertSame('result', $result);
    }
}
