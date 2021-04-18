<?php

namespace cmath10\Mapper\Tests;

use cmath10\Mapper\Exception\InvalidClassConstructorException;
use cmath10\Mapper\FieldAccessor\ClosureAccessor;
use cmath10\Mapper\FieldAccessor\ExpressionAccessor;
use cmath10\Mapper\FieldFilter\ClosureFilter;
use cmath10\Mapper\FieldFilter\IfNullFilter;
use cmath10\Mapper\FieldFilter\ObjectArrayMappingFilter;
use cmath10\Mapper\FieldFilter\ObjectMappingFilter;
use cmath10\Mapper\Mapper;
use cmath10\Mapper\TypeFilterInterface;
use PHPUnit\Framework\TestCase;

final class MapperTest extends TestCase
{
    public function testMapByDefaultMap(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Default map usage';

        $article = new Fixtures\Article();

        $mapper = new Mapper();
        $mapper->create(Fixtures\ArticleInput::class, Fixtures\Article::class);

        $mapper->map($input, $article);

        self::assertEquals('Default map usage', $article->title);
    }

    public function testMapByRoute(): void
    {
        $article = new Fixtures\Article();
        $article->title = 'Using route feature';
        $article->text = '::route';

        $output = new Fixtures\ArticleOutput();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\Article::class, Fixtures\ArticleOutput::class)
            ->route('textNotMappedByDefault', 'text')
        ;

        $mapper->map($article, $output);

        self::assertEquals('Using route feature', $output->title);
        self::assertEquals('::route', $output->textNotMappedByDefault);
    }

    public function testMapByDeepRoute(): void
    {
        $article = new Fixtures\Article();
        $article->author = new Fixtures\Author('Sid Fleming');

        $output = new Fixtures\ArticleOutput();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\Article::class, Fixtures\ArticleOutput::class)
            ->route('author', 'author.name')
        ;

        $mapper->map($article, $output);

        self::assertEquals('Sid Fleming', $output->author);
    }

    public function testMapWithClosureAccessor(): void
    {
        $article = new Fixtures\Article();
        $article->author = new Fixtures\Author('Sid Fleming');

        $output = new Fixtures\ArticleOutput();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\Article::class, Fixtures\ArticleOutput::class)
            ->forMember('author', new ClosureAccessor(fn (Fixtures\Article $a) => $a->author->name))
        ;

        $mapper->map($article, $output);

        self::assertEquals('Sid Fleming', $output->author);
    }

    public function testMapByRegisteredMap(): void
    {
        $article = new Fixtures\Article();
        $article->title = 'Using registered map';
        $article->text = '::register';

        $output = new Fixtures\ArticleOutput();

        $mapper = new Mapper();
        $mapper->register(new Fixtures\ArticleOutputMap());
        $mapper->map($article, $output);

        self::assertEquals('Using registered map', $output->title);
        self::assertEquals('::register', $output->textNotMappedByDefault);
    }

    public function testMapWithIfNullFilter(): void
    {
        $article = new Fixtures\Article();
        $article->title = 'Using IfNull filter';

        $output = new Fixtures\ArticleOutput();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\Article::class, Fixtures\ArticleOutput::class)
            ->filter('textNotMappedByDefault', new IfNullFilter('IfNullFilter'))
        ;
        $mapper->map($article, $output);

        self::assertEquals('IfNullFilter', $output->textNotMappedByDefault);
    }

    public function testMapWithClosureFilter(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Using closure filter';

        $article = new Fixtures\Article();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
            ->filter('title', new ClosureFilter(static fn ($title) => '[[' . $title . ']]'))
        ;

        $mapper->map($input, $article);

        self::assertEquals('[[Using closure filter]]', $article->title);
    }

    public function testMapFromArray(): void
    {
        $input = ['title' => 'Mapping from array'];

        $article = new Fixtures\Article();

        $mapper = new Mapper();
        $mapper->create('array', Fixtures\Article::class);

        $mapper->map($input, $article);

        self::assertEquals('Mapping from array', $article->title);
    }

    public function testMapWhenFieldMustBeIgnored(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Test field ignoring';
        $input->text = '::ignoreMember';

        $article = new Fixtures\ArticleWithPrivateProperties();

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\ArticleWithPrivateProperties::class)
            ->ignoreMember('id')
        ;

        $mapper->map($input, $article);

        self::assertNull($article->getId());
        self::assertEquals('Test field ignoring', $article->getTitle());
        self::assertEquals('::ignoreMember', $article->getText());
    }

    public function testMapWhenValueAlreadySet(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Value rewrite';

        $article = new Fixtures\Article();
        $article->title = 'Foo bar';

        $mapper = new Mapper();
        $mapper->create(Fixtures\ArticleInput::class, Fixtures\Article::class);

        $mapper->map($input, $article);

        self::assertEquals('Value rewrite', $article->title);
    }

    public function testMapWhenValueAlreadySetAndRewritingNotNeeded(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Value rewrite';

        $article = new Fixtures\Article();
        $article->title = 'Foo bar';

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
            ->setOverwriteIfSet(false)
        ;

        $mapper->map($input, $article);

        self::assertEquals('Foo bar', $article->title);
    }

    public function testSkipNull(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = null;

        $article = new Fixtures\Article();
        $article->title = 'Skip null';

        $mapper = new Mapper();
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
            ->setSkipNull(true)
        ;

        $mapper->map($input, $article);

        self::assertEquals('Skip null', $article->title);
    }

    public function testMapNestedObject(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Mapping with nested object';
        $input->author = new Fixtures\AuthorInput();
        $input->author->name = 'Sid Fleming';

        $mapper = new Mapper();
        $mapper->create(Fixtures\AuthorInput::class, Fixtures\Author::class);
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
            ->route('author', 'author')
            ->filter('author', new ObjectMappingFilter(Fixtures\Author::class))
        ;

        $mapper->map($input, $article = new Fixtures\Article());

        self::assertEquals('Mapping with nested object', $article->title);
        self::assertInstanceOf(Fixtures\Author::class, $article->author);
        self::assertEquals('Sid Fleming', $article->author->name);
    }

    public function testMapNestedArrayToObject(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Mapping nested array';
        $input->author = ['name' => 'Sid Fleming'];

        $mapper = new Mapper();
        $mapper->create('array', Fixtures\Author::class);
        $mapper
            ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
            ->route('author', 'author')
            ->filter('author', new ObjectMappingFilter(Fixtures\Author::class))
        ;

        $mapper->map($input, $article = new Fixtures\Article());

        self::assertInstanceOf(Fixtures\Author::class, $article->author);
        self::assertEquals('Sid Fleming', $article->author->name);
    }

    public function testMapNestedArrayOfObject(): void
    {
        $magazine = new Fixtures\Magazine([
            new Fixtures\Article('First'),
            new Fixtures\Article('Second'),
        ]);

        $mapper = new Mapper();
        $mapper->create(Fixtures\Article::class, Fixtures\ArticleOutput::class);
        $mapper
            ->create(Fixtures\Magazine::class, Fixtures\MagazineOutput::class)
            ->route('articles', 'articles')
            ->filter('articles', new ObjectArrayMappingFilter(Fixtures\ArticleOutput::class))
        ;

        $mapper->map($magazine, $output = new Fixtures\MagazineOutput());

        self::assertCount(2, $output->articles);
        self::assertInstanceOf(Fixtures\ArticleOutput::class, $output->articles[0]);
        self::assertInstanceOf(Fixtures\ArticleOutput::class, $output->articles[1]);
        self::assertEquals('First', $output->articles[0]->title);
        self::assertEquals('Second', $output->articles[1]->title);
    }

    public function testMapExpressionAccessor(): void
    {
        $magazine = new Fixtures\MagazineWithPrivateProperties([
            new Fixtures\Article('/First/'),
            new Fixtures\Article('/Second/'),
        ]);

        $mapper = new Mapper();
        $mapper->create(Fixtures\Article::class, Fixtures\ArticleOutput::class);
        $mapper
            ->create(Fixtures\MagazineWithPrivateProperties::class, Fixtures\MagazineOutput::class)
            ->forMember('articles', new ExpressionAccessor('getArticles()'))
            ->filter('articles', new ObjectArrayMappingFilter(Fixtures\ArticleOutput::class))
        ;

        $mapper->map($magazine, $output = new Fixtures\MagazineOutput());

        self::assertCount(2, $output->articles);
        self::assertInstanceOf(Fixtures\ArticleOutput::class, $output->articles[0]);
        self::assertInstanceOf(Fixtures\ArticleOutput::class, $output->articles[1]);
        self::assertEquals('/First/', $output->articles[0]->title);
        self::assertEquals('/Second/', $output->articles[1]->title);
    }

    public function testMapToClassName(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Mapping to class name';

        $mapper = new Mapper();
        $mapper->create(Fixtures\ArticleInput::class, Fixtures\Article::class);

        $article = $mapper->map($input, Fixtures\Article::class);

        self::assertInstanceOf(Fixtures\Article::class, $article);
        self::assertEquals('Mapping to class name', $article->title);
    }

    public function testMapToClassNameWhenConstructorRequiresAnArgument(): void
    {
        $this->expectException(InvalidClassConstructorException::class);

        $author = new Fixtures\Author('Sid Fleming');

        $mapper = new Mapper();
        $mapper->create(Fixtures\Author::class, Fixtures\AuthorButNameIsRequiredInConstructor::class);
        $mapper->map($author, Fixtures\AuthorButNameIsRequiredInConstructor::class);
    }

    public function testTypeFiltering(): void
    {
        $input = new Fixtures\ArticleInput();
        $input->title = 'Type name filtering';

        $mapper = new Mapper([
            new class() implements TypeFilterInterface {
                public function filter($typeName): string
                {
                    return str_replace('Proxy', '', $typeName);
                }
            }
        ]);
        $mapper->create(Fixtures\ArticleInput::class, Fixtures\Article::class);

        $article = $mapper->map($input, Fixtures\ArticleProxy::class);

        self::assertEquals('Type name filtering', $article->title);
    }
}
