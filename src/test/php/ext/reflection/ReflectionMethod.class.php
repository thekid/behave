<?php namespace test\php\ext\reflection\ReflectionMethod;

// Fixtures
abstract class Base {
  protected function inherited() { }
  public static function valueOf($in) { return ['in' => $in]; }
  private final function values() { }
  protected abstract function value();
}
class Fixture extends Base {
  public function method() { return true; }
  private function internal() { }
  protected function value() { }
}

// Helper: Returns a new ReflectionMethod for the fixture's method()
function newFixture($name) {
  return new \ReflectionMethod(Fixture::class, $name);
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
      shouldEqual('method', newFixture('method')->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getname.php
    its('getName', [
      it('returns the methods\'s name', function() {
        shouldEqual('method', newFixture('method')->getName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getnumberofparameters.php
    its('getNumberOfParameters', [
      it('returns 0 for empty parameter list', function() {
        shouldEqual(0, declaration('function %s() { }')->getNumberOfParameters());
      }),

      it('returns 1 for one parameter', function() {
        shouldEqual(1, declaration('function %s($a) { }')->getNumberOfParameters());
      }),

      it('returns 2 for two parameters', function() {
        shouldEqual(2, declaration('function %s($a, $b) { }')->getNumberOfParameters());
      }),

      it('returns 3 for two required parameters and one optional', function() {
        shouldEqual(3, declaration('function %s($a, $b, $c= null) { }')->getNumberOfParameters());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getParameters.php
    its('getParameters', [
      it('returns an empty array for an empty parameter list', function() {
        shouldEqual([], declaration('function %s() { }')->getParameters());
      }),

      it('returns a ReflectionParameter instance for a single parameter', function() {
        shouldBe([\ReflectionParameter::class], declaration('function %s($a) { }')->getParameters());
      }),

      it('returns a non-empty array of ReflectionParameter instances', function() {
        shouldBe([\ReflectionParameter::class, \ReflectionParameter::class], declaration('function %s($a, $b) { }')->getParameters());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getnumberofrequiredparameters.php
    its('getNumberOfRequiredParameters', [
      it('returns 0 for empty parameter list', function() {
        shouldEqual(0, declaration('function %s() { }')->getNumberOfRequiredParameters());
      }),

      it('returns 1 for one parameter', function() {
        shouldEqual(1, declaration('function %s($a) { }')->getNumberOfRequiredParameters());
      }),

      it('returns 0 for one optional parameter', function() {
        shouldEqual(0, declaration('function %s($a= null) { }')->getNumberOfRequiredParameters());
      }),

      it('returns 2 for two parameters', function() {
        shouldEqual(2, declaration('function %s($a, $b) { }')->getNumberOfRequiredParameters());
      }),

      it('returns 2 for two required parameters and one optional', function() {
        shouldEqual(2, declaration('function %s($a, $b, $c= null) { }')->getNumberOfRequiredParameters());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.ispublic.php
    its('isPublic', [
      it('returns true for public methods', function() {
        shouldEqual(true, newFixture('method')->isPublic());
      }),

      it('returns true for public static methods', function() {
        shouldEqual(true, newFixture('valueOf')->isPublic());
      }),

      it('returns false for protected methods', function() {
        shouldEqual(false, newFixture('inherited')->isPublic());
      }),

      it('returns false for private methods', function() {
        shouldEqual(false, newFixture('internal')->isPublic());
      }),

      it('returns false for private final methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'values'))->isPublic());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'value'))->isPublic());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.isprotected.php
    its('isProtected', [
      it('returns false for public methods', function() {
        shouldEqual(false, newFixture('method')->isProtected());
      }),

      it('returns false for public static methods', function() {
        shouldEqual(false, newFixture('valueOf')->isProtected());
      }),

      it('returns true for protected methods', function() {
        shouldEqual(true, newFixture('inherited')->isProtected());
      }),

      it('returns false for private methods', function() {
        shouldEqual(false, newFixture('internal')->isProtected());
      }),

      it('returns false for private final methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'values'))->isProtected());
      }),

      it('returns true for protected abstract methods', function() {
        shouldEqual(true, (new \ReflectionMethod(Base::class, 'value'))->isProtected());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.isprivate.php
    its('isPrivate', [
      it('returns false for public methods', function() {
        shouldEqual(false, newFixture('method')->isPrivate());
      }),

      it('returns false for public static methods', function() {
        shouldEqual(false, newFixture('valueOf')->isPrivate());
      }),

      it('returns false for protected methods', function() {
        shouldEqual(false, newFixture('inherited')->isPrivate());
      }),

      it('returns true for private methods', function() {
        shouldEqual(true, newFixture('internal')->isPrivate());
      }),

      it('returns true for private final methods', function() {
        shouldEqual(true, (new \ReflectionMethod(Base::class, 'values'))->isPrivate());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'value'))->isPrivate());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.isstatic.php
    its('isStatic', [
      it('returns false for public methods', function() {
        shouldEqual(false, newFixture('method')->isStatic());
      }),

      it('returns true for public static methods', function() {
        shouldEqual(true, newFixture('valueOf')->isStatic());
      }),

      it('returns false for protected methods', function() {
        shouldEqual(false, newFixture('inherited')->isStatic());
      }),

      it('returns false for private methods', function() {
        shouldEqual(false, newFixture('internal')->isStatic());
      }),

      it('returns false for private final methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'values'))->isStatic());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'value'))->isStatic());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.isfinal.php
    its('isFinal', [
      it('returns false for public methods', function() {
        shouldEqual(false, newFixture('method')->isFinal());
      }),

      it('returns false for public static methods', function() {
        shouldEqual(false, newFixture('valueOf')->isFinal());
      }),

      it('returns false for protected methods', function() {
        shouldEqual(false, newFixture('inherited')->isFinal());
      }),

      it('returns false for private methods', function() {
        shouldEqual(false, newFixture('internal')->isFinal());
      }),

      it('returns true for private final methods', function() {
        shouldEqual(true, (new \ReflectionMethod(Base::class, 'values'))->isFinal());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'value'))->isFinal());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.isabstract.php
    its('isAbstract', [
      it('returns false for public methods', function() {
        shouldEqual(false, newFixture('method')->isAbstract());
      }),

      it('returns false for public static methods', function() {
        shouldEqual(false, newFixture('valueOf')->isAbstract());
      }),

      it('returns false for protected methods', function() {
        shouldEqual(false, newFixture('inherited')->isAbstract());
      }),

      it('returns false for private methods', function() {
        shouldEqual(false, newFixture('internal')->isAbstract());
      }),

      it('returns false for private final methods', function() {
        shouldEqual(false, (new \ReflectionMethod(Base::class, 'values'))->isAbstract());
      }),

      it('returns true for protected abstract methods', function() {
        shouldEqual(true, (new \ReflectionMethod(Base::class, 'value'))->isAbstract());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.getclosure.php
    its('getClosure', [

      it('raises a warning when invoked without arguments', function() {
        shouldRaise('/expects exactly 1 parameter, 0 given/', function() {
          newFixture('method')->getClosure();
        });
      }),

      it('returns a closure', function() {
        shouldBe(\Closure::class, newFixture('method')->getClosure(new Fixture()));
      }),

      it('returns a closure for static methods', function() {
        shouldBe(\Closure::class, newFixture('valueOf')->getClosure(null));
      }),

      it('throws an exception when an incorrect object is passed', function() {
        shouldThrow(\ReflectionException::class, '/Given object is not an instance of the class/', function() {
          newFixture('method')->getClosure(new \stdClass());
        });
      }),

      it('will be invokeable and return whatever the method returns', function() {
        $instance= new Fixture();
        $f= newFixture('method')->getClosure($instance);
        shouldEqual($instance->method(), $f());
      }),

      it('will be invokeable and return whatever the static method returns', function() {
        $f= newFixture('valueOf')->getClosure(null);
        shouldEqual(Fixture::valueOf('test'), $f('test'));
      }),
    ]),
  ]
]);
