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

// Helper: Returns ReflectionMethod for a method created dynamically via its declaration
function declaration($declaration) {
  static $uniq= 0;
  $uniq++;
  eval(sprintf('namespace %s; class Fixture__%d {'.$declaration.'}', __NAMESPACE__, $uniq, 'fixture'));
  return (new \ReflectionMethod(__NAMESPACE__.'\\Fixture__'.$uniq, 'fixture'));
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

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getnumberofparameters.php
    its('getNumberOfParameters', [
      it('return 0 for empty parameter list', function() {
        shouldEqual(0, declaration('function %s() { }')->getNumberOfParameters());
      }),

      it('return 1 for one parameter', function() {
        shouldEqual(1, declaration('function %s($a) { }')->getNumberOfParameters());
      }),

      it('return 2 for two parameters', function() {
        shouldEqual(2, declaration('function %s($a, $b) { }')->getNumberOfParameters());
      }),

      it('return 3 for two required parameters and one optional', function() {
        shouldEqual(3, declaration('function %s($a, $b, $c= null) { }')->getNumberOfParameters());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getParameters.php
    its('getParameters', [
      it('return an empty array for an empty parameter list', function() {
        shouldEqual([], declaration('function %s() { }')->getParameters());
      }),

      it('return a ReflectionParameter instance for a single parameter', function() {
        shouldBe([\ReflectionParameter::class], declaration('function %s($a) { }')->getParameters());
      }),

      it('return a non-empty array of ReflectionParameter instances', function() {
        shouldBe([\ReflectionParameter::class, \ReflectionParameter::class], declaration('function %s($a, $b) { }')->getParameters());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getnumberofrequiredparameters.php
    its('getNumberOfRequiredParameters', [
      it('return 0 for empty parameter list', function() {
        shouldEqual(0, declaration('function %s() { }')->getNumberOfRequiredParameters());
      }),

      it('return 1 for one parameter', function() {
        shouldEqual(1, declaration('function %s($a) { }')->getNumberOfRequiredParameters());
      }),

      it('return 0 for one optional parameter', function() {
        shouldEqual(0, declaration('function %s($a= null) { }')->getNumberOfRequiredParameters());
      }),

      it('return 2 for two parameters', function() {
        shouldEqual(2, declaration('function %s($a, $b) { }')->getNumberOfRequiredParameters());
      }),

      it('return 2 for two required parameters and one optional', function() {
        shouldEqual(2, declaration('function %s($a, $b, $c= null) { }')->getNumberOfRequiredParameters());
      }),
    ]),
  ]
]);
