<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Console\Commands;

use Illuminate\Support\Facades\File;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Console\Commands\MakeTableCommand;

/**
 * @internal
 */
#[CoversClass(MakeTableCommand::class)]
class MakeTableCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up created files
        $tablesPath = $this->app->basePath('app/Tables');

        if (File::exists($tablesPath)) {
            File::deleteDirectory($tablesPath);
        }

        parent::tearDown();
    }

    public function test_it_creates_a_basic_table_class(): void
    {
        $this->artisan('et:make:table', ['name' => 'UserTable'])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/UserTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('namespace App\Tables;', $content);
        $this->assertStringContainsString('class UserTable', $content);
        $this->assertStringNotContainsString('use BrickNPC\EloquentTables\Concerns\WithPagination;', $content);
        $this->assertStringNotContainsString('use WithPagination;', $content);
        $this->assertStringContainsString('public function query(): Builder', $content);
        $this->assertStringContainsString('public function columns(): array', $content);
    }

    public function test_it_creates_a_table_with_model_option(): void
    {
        $this->artisan('et:make:table', [
            'name'    => 'PostTable',
            '--model' => 'Post',
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/PostTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use App\Models\Post;', $content);
    }

    public function test_it_creates_a_table_with_fqn_model_option(): void
    {
        $this->artisan('et:make:table', [
            'name'    => 'ProductTable',
            '--model' => 'Sub\Folder\Product',
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/ProductTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use App\Models\Sub\Folder\Product;', $content);
    }

    public function test_it_creates_a_table_with_model_option_starting_with_backslash(): void
    {
        $this->artisan('et:make:table', [
            'name'    => 'OrderTable',
            '--model' => '\App\Models\Order',
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/OrderTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use \App\Models\Order;', $content);
    }

    public function test_it_creates_a_table_with_pagination_trait(): void
    {
        $this->artisan('et:make:table', [
            'name'              => 'CommentTable',
            '--with-pagination' => true,
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/CommentTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use BrickNPC\EloquentTables\Concerns\WithPagination;', $content);
        $this->assertStringContainsString('use WithPagination;', $content);
        $this->assertStringContainsString('class CommentTable', $content);
    }

    public function test_it_creates_a_table_with_model_and_pagination_options(): void
    {
        $this->artisan('et:make:table', [
            'name'              => 'CategoryTable',
            '--model'           => 'Category',
            '--with-pagination' => true,
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/CategoryTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use BrickNPC\EloquentTables\Concerns\WithPagination;', $content);
        $this->assertStringContainsString('use App\Models\Category;', $content);
        $this->assertStringContainsString('use WithPagination;', $content);
    }

    public function test_it_creates_table_in_nested_directory(): void
    {
        $this->artisan('et:make:table', ['name' => 'Admin/UserTable'])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/Admin/UserTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('namespace App\Tables\Admin;', $content);
        $this->assertStringContainsString('class UserTable', $content);
    }

    public function test_it_respects_custom_tables_path_from_config(): void
    {
        config()->set('eloquent-tables.tables-location', 'Custom/Tables');

        $this->artisan('et:make:table', ['name' => 'CustomTable'])->assertSuccessful();

        $filePath = $this->app->basePath('app/Custom/Tables/CustomTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('namespace App\Custom\Tables;', $content);
        $this->assertStringContainsString('class CustomTable', $content);

        // Clean up custom path
        if (File::exists($this->app->basePath('app/Custom'))) {
            File::deleteDirectory($this->app->basePath('app/Custom'));
        }
    }

    public function test_it_uses_short_option_for_model(): void
    {
        $this->artisan('et:make:table', [
            'name' => 'TagTable',
            '-m'   => 'Tag',
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/TagTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use App\Models\Tag;', $content);
    }

    public function test_it_uses_short_option_for_pagination(): void
    {
        $this->artisan('et:make:table', [
            'name' => 'ArticleTable',
            '-p'   => true,
        ])->assertSuccessful();

        $filePath = $this->app->basePath('app/Tables/ArticleTable.php');

        $this->assertFileExists($filePath);

        $content = File::get($filePath);

        $this->assertStringContainsString('use BrickNPC\EloquentTables\Concerns\WithPagination;', $content);
        $this->assertStringContainsString('use WithPagination;', $content);
    }
}
