# Mapper

The library provides a utility for converting objects to each other (and arrays to objects) and transferring data
between them by reusable map.

Basic example:

```php
class ArticleInput
{
    public $title;

    public $text;

    public $author;
}

class Article
{
    public $title;

    public $text;

    public $author;

    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }
}

$input = new ArticleInput();
$input->title = 'title';
$input->text = 'text';

$article1 = new Article();

$mapper = new cmath10\Mapper\Mapper();
$mapper->create(ArticleInput::class, Article::class);

$mapper->map($input, $article1);

// ($article1->title === 'title') === true
// ($article1->text === 'text') === true

$article2 = $mapper->map($input, Article::class)

// ($article2 instanceof Article) === true
// ($article2->title === 'title') === true
// ($article2->text === 'text') === true
```

## How it works

The main term of the library is a map. It is an object which is containing information about how to extract values from
the source and inject them into a destination object. Values can be processed before injection (to get an opportunity
for sanitization and nested mapping).

Map includes:

* field accessors &mdash; objects are responsible for value extraction; by default, it is used `symfony/property-access`
 for that purpose, but there are other options, like using callbacks or `symfony/expression-language` and presets (if
 the extracted value is null), also there is an interface for custom accessors;
* field filters &mdash; objects are responsible for value processing; by default, there are no filters for extracted
 value; you can use callbacks and mapping filters for nested mapping.

By default, a map creates a set of accessors based on a class properties list, so a field from a source will be mapped
on a field of a destination with the same name. You can change this behavior for specific fields if needed.

All maps must be registered in a mapper instance before using. When you call the mapper method `map` it
seeks the correct map for a given source and destination, then it uses accessors to extract values, filters to process
them, and, after that, injects values to a destination. If the destination is a class name, the mapper will try to
create an instance of the given class, if the class constructor is public and all possible arguments are optional.

## Installation

```bash
composer require cmath10/mapper
```

## API

`cmath10\Mapper\MapperInterface` &mdash; interface for mappers:

* `create(string $sourceType, string $destinationType)` &mdash; creates, registers and returns default map for two 
 classes;
* `register(MapInterface $map)` &mdash; registers a custom map in the mapper;
* `map($source, $destination)` &mdash; performs mapping from a source to a destination object or class.

`cmath10\mapper\MapInterface` &mdash; interface for maps:

* `getSourceType()` &mdash; returns name of source type; used to find the correct map;
* `getDestinationType()` &mdash; returns name of destination type; used to find the correct map;
* `getFieldAccessors()` &mdash; returns all fields accessors;
* `getFieldFilters()` &mdash; returns all fields filters;
* `getOverwriteIfSet()` &mdash; returns boolean flag; if true, destination member will be overwritten even if
 its value is not null;
* `getSkipNull()` &mdash; returns boolean flag; if true, the mapper will not push null source member's value;
* `route(string $destinationMember, string $sourceMember)` &mdash; sets mapping of destination route to
 source route; fluent;
* `forMember(string $destinationMember, AccessorInterface $fieldMapper)` &mdash; sets custom accessor for field; fluent;
* `filter(string $destinationMember, FilterInterface $fieldFilter)` &mdash; sets a custom filter for value
 processing; fluent;
* `ignoreMember(string $destinationMember)` &mdash; excludes members from accounting, so they will not be filled;
 fluent.

`cmath10\mapper\FieldAccessor\AccessorInterface` &mdash; interface for accessors:
* `getValue($source)` &mdash; extracts value from a source.

`cmath10\mapper\FieldFilter\FitlerInterface` &mdash; interface for filters:
* `filter($value)` &mdash; processes the value.

`cmath10\Mapper\TypeFilterInterface` &mdash; interface for type filters is used to process type names. Useful for
extracting class names from runtime generated classes like proxies in Doctrine. Used in default mapper which uses an
array of type filters as a constructor parameter (to instantiate `cmath10\mapper\TypeGuesser` which is used to
determine the correct map for supplied types in mapper's `map` method).

### Available maps

#### AbstractMap

Basic class is used to create custom map classes, implements `MapperInterface`;
provides following protected method:
* `setupDefaults` &mdash; creates a set of accessors based on a class properties list.

Usage example:
```php
use cmath10\Mapper\AbstractMap;

class ArticleOutputMap extends AbstractMap
{
    public function __construct()
    {
        $this
            ->setupDefaults() // create default accessors set
            ->route('textNotMappedByDefault', 'text') // customize
        ;
    }

    public function getSourceType(): string
    {
        return Article::class;
    }

    public function getDestinationType(): string
    {
        return ArticleOutput::class;
    }
}
```

#### DefaultMap

Simple descendant of `AbstractMap`, just calls `setupDefaults` in
its constructor; the mapper `create` method creates, registers and returns instance of this class.

### Available accessors

#### ClosureAccessor

Uses callback to extract a value:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldAccessor\ClosureAccessor;

$mapper = new Mapper();
$mapper
    ->create(Fixtures\Article::class, Fixtures\ArticleInput::class)
    ->forMember('author', new ClosureAccessor(fn (Fixtures\ArticleInput $a) => $a->author->name))
;
```

#### ExpressionAccessor

Uses `symfony/expression-language` to extract a value:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldAccessor\ExpressionAccessor;

$mapper = new Mapper();
$mapper
    ->create(Fixtures\MagazineWithPrivateProperties::class, Fixtures\MagazineOutput::class)
    ->forMember('articles', new ExpressionAccessor('getArticles()'))
;
```

#### PresetAccessor

Does not extract a value, just provides:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldAccessor\PresetAccessor;

$mapper = new Mapper();
$mapper
    ->create(Fixtures\MagazineWithPrivateProperties::class, Fixtures\MagazineOutput::class)
    ->forMember('articles', new PresetAccessor([]))
;
```

#### PropertyPathAccessor

Uses `symfony/property-access` to extract a value. This accessor is used by default. Calls like:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;

$mapper = new Mapper();
$mapper
    ->create(Fixtures\MagazineWithPrivateProperties::class, Fixtures\MagazineOutput::class)
    ->forMember('articles', new PropertyPathAccessor('someFieldWithArticles'))
;
```

and

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldAccessor\PropertyPathAccessor;

$mapper = new Mapper();
$mapper
    ->create(Fixtures\MagazineWithPrivateProperties::class, Fixtures\MagazineOutput::class)
    ->route('articles', 'someFieldWithArticles')
;
```

are equivalent. Also, if the fields in a source and destination have the same name, and you use
`setupDefaults`, you don't need to call `route` or `forMember` explicitly.

### Available filters

#### ClosureFilter

Uses callback to process value:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldFilter\ClosureFilter;

// For ArticleInput title='title' we will get Article title='[[title]]'
$mapper = new Mapper();
$mapper
    ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
    ->filter('title', new ClosureFilter(static fn ($title) => '[[' . $title . ']]'))
;
```

#### IfNullFilter

Replaces the value if it is null:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldFilter\IfNullFilter;

// For ArticleInput title=null we will get Article title='defaultTitle'
$mapper = new Mapper();
$mapper
    ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
    ->filter('title', new IfNullFilter('defaultTitle'))
;
```

#### AbstractMappingFilter

Basic class for filters that can use mapper. Requires class name in the constructor. Used for
nested mapping.

#### ObjectMappingFilter

Descendant of `AbstractMappingFilter`, uses class name and mapper to make an
object from a source member:

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldFilter\ObjectMappingFilter;

$mapper = new Mapper();
$mapper->create(Fixtures\AuthorInput::class, Fixtures\Author::class);
$mapper
    ->create(Fixtures\ArticleInput::class, Fixtures\Article::class)
    ->route('author', 'author')
    ->filter('author', new ObjectMappingFilter(Fixtures\Author::class))
;
```

#### ObjectArrayMappingFilter

Descendant of `AbstractMappingFilter`, uses class name and mapper to make an
array of objects from a source member, if the source member's value is array (returns
an empty array instead):

```php
use cmath10\Mapper\Mapper;
use cmath10\Mapper\FieldFilter\ObjectArrayMappingFilter;

$mapper = new Mapper();
$mapper->create(Fixtures\Article::class, Fixtures\ArticleOutput::class);
$mapper
    ->create(Fixtures\Magazine::class, Fixtures\MagazineOutput::class)
    ->route('articles', 'articles')
    ->filter('articles', new ObjectArrayMappingFilter(Fixtures\ArticleOutput::class))
;
```