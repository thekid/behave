<?php namespace test\php\ext\reflection\ReflectionMethod;

// Fixtures
class Fixture {
  function method() { 
  }
}

// Helper: Returns a new ReflectionMethod for the fixture's method()
function newFixture() {
  return new \ReflectionMethod(__NAMESPACE__.'\Fixture::method');
}

// @see http://de3.php.net/manual/de/class.reflectionmethod.php
return new \behaviour\of\TheClass('ReflectionMethod', [

  'describe' => [
    it('raises warnings when constructed without arguments', function() {
      shouldRaise('/expects exactly 1 parameter, 0 given/', function() {
        new \ReflectionMethod();
      });
    }),

    it('can be constructed with a class and a method name', function() {
      shouldBe(\ReflectionMethod::class, new \ReflectionMethod(Fixture::class, 'method'));
    }),

    it('can be constructed with a class and a method name separated by double colon', function() {
      shouldBe(\ReflectionMethod::class, new \ReflectionMethod(Fixture::class.'::method'));
    }),

    it('will have a public member "name"', function() {
      shouldEqual('method', newFixture()->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getname.php
    its('getName', [
      it('returns the methods\'s name', function() {
        shouldEqual('method', newFixture()->getName());
      }),
    ]),

  ]
]);
