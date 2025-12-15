<?php

declare(strict_types=1);

namespace Actions\ValueObjects;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(RenderBuffer::class)]
class RenderBufferTest extends TestCase
{
    public function test_render_returns_empty_string_when_no_chunks_added(): void
    {
        $buffer = new RenderBuffer();

        $this->assertSame('', $buffer->render());
    }

    public function test_add_and_render_with_string(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('Hello World');

        $this->assertSame('Hello World', $buffer->render());
    }

    public function test_add_and_render_with_multiple_strings(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('Hello ');
        $buffer->add('World');
        $buffer->add('!');

        $this->assertSame('Hello World!', $buffer->render());
    }

    public function test_add_and_render_with_null(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add(null);

        $this->assertSame('', $buffer->render());
    }

    public function test_add_and_render_with_multiple_nulls(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add(null);
        $buffer->add(null);

        $this->assertSame('', $buffer->render());
    }

    public function test_add_and_render_with_stringable_object(): void
    {
        $buffer     = new RenderBuffer();
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable Content';
            }
        };

        $buffer->add($stringable);

        $this->assertSame('Stringable Content', $buffer->render());
    }

    public function test_add_and_render_with_htmlable_object(): void
    {
        $buffer   = new RenderBuffer();
        $htmlable = $this->createMock(Htmlable::class);
        $htmlable->method('toHtml')->willReturn('<div>HTML Content</div>');

        $buffer->add($htmlable);

        $this->assertSame('<div>HTML Content</div>', $buffer->render());
    }

    public function test_add_and_render_with_view_object(): void
    {
        $buffer = new RenderBuffer();
        $view   = $this->createMock(View::class);
        $view->method('render')->willReturn('<p>View Content</p>');

        $buffer->add($view);

        $this->assertSame('<p>View Content</p>', $buffer->render());
    }

    public function test_add_and_render_with_mixed_types(): void
    {
        $buffer = new RenderBuffer();

        $htmlable = $this->createMock(Htmlable::class);
        $htmlable->method('toHtml')->willReturn('<span>HTML</span>');

        $view = $this->createMock(View::class);
        $view->method('render')->willReturn('<div>View</div>');

        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable';
            }
        };

        $buffer->add('String ');
        $buffer->add($htmlable);
        $buffer->add(' ');
        $buffer->add($view);
        $buffer->add(' ');
        $buffer->add($stringable);
        $buffer->add(null);
        $buffer->add(' End');

        $this->assertSame(
            'String <span>HTML</span> <div>View</div> Stringable End',
            $buffer->render(),
        );
    }

    public function test_render_can_be_called_multiple_times(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('Content');

        $firstRender  = $buffer->render();
        $secondRender = $buffer->render();

        $this->assertSame('Content', $firstRender);
        $this->assertSame('Content', $secondRender);
        $this->assertSame($firstRender, $secondRender);
    }

    public function test_add_and_render_with_empty_string(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('');

        $this->assertSame('', $buffer->render());
    }

    public function test_add_and_render_with_numeric_string(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('123');
        $buffer->add('456');

        $this->assertSame('123456', $buffer->render());
    }

    public function test_add_and_render_with_special_characters(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add('<div>Special & "quoted"</div>');

        $this->assertSame('<div>Special & "quoted"</div>', $buffer->render());
    }

    public function test_add_and_render_with_newlines_and_whitespace(): void
    {
        $buffer = new RenderBuffer();
        $buffer->add("Line 1\n");
        $buffer->add("Line 2\t");
        $buffer->add('Line 3');

        $this->assertSame("Line 1\nLine 2\tLine 3", $buffer->render());
    }
}
