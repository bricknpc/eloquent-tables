<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeTableCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'et:make:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Table class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Table';

    protected function getStub(): string
    {
        if ($this->option('with-pagination')) {
            return __DIR__ . '/../../../stubs/table.pagination.stub';
        }

        return __DIR__ . '/../../../stubs/table.stub';
    }

    /**
     * @param string $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        /** @var string $path */
        $path = config('eloquent-tables.tables-location', 'Tables');

        return $rootNamespace . '\\' . str_replace('/', '\\', $path);
    }

    /**
     * @param string $name
     */
    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel->basePath('app') . '/' . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * @param string $name
     *
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $this->replaceModel($stub);

        return $stub;
    }

    protected function replaceModel(string &$stub): static
    {
        /** @var string $model */
        $model = $this->option('model');

        if (!$model) {
            $stub = str_replace('{{ modelImport }}', 'use App\Models\YourModel;', $stub);
            $stub = str_replace('{{ modelType }}', 'YourModel', $stub);

            return $this;
        }

        $modelClass = $model;

        if (!str($model)->startsWith('\\')) {
            $modelClass = 'App\Models\\' . $model;
        }

        $modelShortName = class_basename($modelClass);

        $stub = str_replace('{{ modelImport }}', "use {$modelClass};", $stub);
        $stub = str_replace('{{ modelType }}', $modelShortName, $stub);

        return $this;
    }

    /**
     * @return array<int, array{string, string, int, string}>
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the table is for'],
            ['with-pagination', 'p', InputOption::VALUE_NONE, 'Add pagination to the table'],
        ];
    }
}
