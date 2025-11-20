<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Container\Container;

readonly class RouteModelBinder
{
    public function __construct(
        private Container $container,
        private Request $request,
    ) {}

    public function call(object $object, string $method): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);

        $parameters     = $reflection->getParameters();
        $callParameters = [];

        foreach ($parameters as $parameter) {
            /** @var null|\ReflectionNamedType $type */
            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType) {
                continue;
            }

            if (is_subclass_of($type->getName(), Model::class) && $this->request->route($parameter->getName()) !== null) {
                /** @var Model $instance */
                $instance = $this->container->make($type->getName());

                $model = $instance->newQuery()->findOrFail($this->request->route($parameter->getName()));

                $callParameters[$parameter->getName()] = $model;
            }
        }

        return $this->container->call([$object, $method], $callParameters); // @phpstan-ignore-line
    }
}
