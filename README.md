Behaviour tests for PHP functionality
=====================================
This tests PHP functionality using a fluent function-based DSL. In contrast to the *PHPT* tests, all tests are run in the same interpreter instance, speeding up functional verification of a PHP implemenentation drastically.

Prerequisites
-------------
This library requires **PHP 5.5**.

Running the tests
-----------------

```sh
$ find src/test -name '*.php' | xargs php src/behaves.php
# [...]
OK, XXX succeeded, 0 failed (time taken: X.XXX seconds)
```

Writing tests
-------------

A basic test looks resides in a namespace mapping to the directory and including the fixture's name itself.

### Functions

```php
namespace test\php\ext\date\date_create;

// @see http://de3.php.net/manual/de/datetime.construct.php
// @see http://de3.php.net/manual/de/datetime.formats.php
return new \behaviour\of\TheFunction('date_create', [
  'before' => function() {
    date_default_timezone_set('Europe/Berlin');
  },

  'describe' => [
    it('returns current date per default', function() {
      shouldEqual(time(), date_create()->getTimestamp());
    }),
  ]
]);
```

### Classes

```php
namespace test\php\ext\reflection\ReflectionClass;

class Fixture { }

// @see http://de3.php.net/manual/de/class.reflectionclass.php
return new \behaviour\of\TheClass('ReflectionClass', [
  it('can be constructed with a class name', function() {
    shouldBe(\ReflectionClass::class, new \ReflectionClass(Fixture::class));
  }),

  // @see http://de3.php.net/manual/de/reflectionclass.getname.php
  its('getName', [
    it('returns the class\' name', function() {
      shouldEqual(Fixture::class, (new \ReflectionClass(Fixture::class))->getName());
    }),
  ]),
]);
```