<?php namespace test\php\ext\reflection\ReflectionMethod;

// Fixtures
abstract class Base {
  protected function inherited() { }
  public static function valueOf($in) { return ['in' => $in]; }
  private final function values() { }
  protected abstract function value($offset);
}
class Fixture extends Base {
  public function method() { return true; }
  private function internal() { }
  protected function value($index) { }
}

// Helper: Returns a new ReflectionMethod for the fixture's method()
function newFixture($name) {
  return new \ReflectionMethod(__NAMESPACE__.'\Fixture', $name);
}

// Helper: Returns ReflectionMethod for a method created dynamically via its declaration
function declaration($declaration, $modifiers= '') {
  static $uniq= 0;
  $uniq++;
  eval(sprintf('namespace %s; %s class Fixture__%d {'.$declaration.'}', __NAMESPACE__, $modifiers, $uniq, 'fixture'));
  return (new \ReflectionMethod(__NAMESPACE__.'\\Fixture__'.$uniq, 'fixture'));
}

// @see http://de3.php.net/manual/de/class.reflectionmethod.php
return new \behaviour\of\TheClass('ReflectionMethod', [

  'describe' => [
    it('raises warnings when constructed without arguments', function() {
      shouldThrow('ReflectionException', '/Method :: does not exist/', function() {
        new \ReflectionMethod();
      });
    }),

    it('can be constructed with a class and a method name', function() {
      shouldBe('ReflectionMethod', new \ReflectionMethod(__NAMESPACE__.'\Fixture', 'method'));
    }),

    it('can be constructed with a class and a method name separated by double colon', function() {
      shouldBe('ReflectionMethod', new \ReflectionMethod(__NAMESPACE__.'\Fixture'.'::method'));
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

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getparameters.php
    its('getParameters', [
      it('returns an empty array for an empty parameter list', function() {
        shouldEqual([], declaration('function %s() { }')->getParameters());
      }),

      it('returns a ReflectionParameter instance for a single parameter', function() {
        shouldBe(['ReflectionParameter'], declaration('function %s($a) { }')->getParameters());
      }),

      it('returns a non-empty array of ReflectionParameter instances', function() {
        shouldBe(['ReflectionParameter', 'ReflectionParameter'], declaration('function %s($a, $b) { }')->getParameters());
      }),

      it('parameter\'s name from base class', function() {
        shouldEqual('offset', (new \ReflectionMethod(__NAMESPACE__.'\\Base', 'value'))->getParameters()[0]->name);
      }),

      it('parameter\'s name matches declaration when overwritten from base class', function() {
        shouldEqual('index', (new \ReflectionMethod(__NAMESPACE__.'\\Fixture', 'value'))->getParameters()[0]->name);
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
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isPublic());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isPublic());
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
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isProtected());
      }),

      it('returns true for protected abstract methods', function() {
        shouldEqual(true, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isProtected());
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
        shouldEqual(true, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isPrivate());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isPrivate());
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
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isStatic());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isStatic());
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
        shouldEqual(true, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isFinal());
      }),

      it('returns false for protected abstract methods', function() {
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isFinal());
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
        shouldEqual(false, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'values'))->isAbstract());
      }),

      it('returns true for protected abstract methods', function() {
        shouldEqual(true, (new \ReflectionMethod(__NAMESPACE__.'\Base', 'value'))->isAbstract());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.getclosure.php
    its('getClosure', [

      it('raises a warning when invoked without arguments', function() {
        shouldThrow('ReflectionException', '/Given object is not an instance of the class/', function() {
          newFixture('method')->getClosure();
        });
      }),

      it('returns a closure', function() {
        shouldBe('Closure', newFixture('method')->getClosure(new Fixture()));
      }),

      it('returns a closure for static methods', function() {
        shouldBe('Closure', newFixture('valueOf')->getClosure(null));
      }),

      it('throws an exception when an incorrect object is passed', function() {
        shouldThrow('ReflectionException', '/Given object is not an instance of the class/', function() {
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

    // @see http://de3.php.net/manual/de/reflectionmethod.invoke.php
    its('invoke', [
      it('will invoke no-arg methods', function() {
        $decl= declaration('public function %s() { return "Test"; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual('Test', $decl->invoke($instance));
      }),

      it('will pass a given argument to the method', function() {
        $decl= declaration('public function %s($arg) { return $arg; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual('Test', $decl->invoke($instance, 'Test'));
      }),

      it('will pass given arguments to the method', function() {
        $decl= declaration('public function %s($a, $b) { return [$a, $b]; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual([1, 2], $decl->invoke($instance, 1, 2));
      }),

      it('will raise an exception when trying to invoke non-public methods', ['private', 'protected'], function($modifier) {
        shouldThrow(\ReflectionException::class, '/Trying to invoke '.$modifier.' method/', function() use($modifier) {
          $decl= declaration($modifier.' function %s() { }');
          $instance= $decl->getDeclaringClass()->newInstance();
          $decl->invoke($instance);
        });
      }),

      it('will raise an exception when trying to invoke abstract methods', function() {
        shouldThrow(\ReflectionException::class, '/Trying to invoke abstract method/', function() {
          declaration('abstract function %s();', 'abstract')->invoke(new \stdClass());
        });
      }),

      it('will raise an exception when trying to invoke instance methods without an instance', function() {
        shouldThrow(\ReflectionException::class, '/Non-object passed/', function() {
          declaration('public function %s() { }')->invoke(null);
        });
      }),

      it('will raise an exception when trying to invoke instance methods with non-compatible instance', function() {
        shouldThrow(\ReflectionException::class, '/Given object is not an instance/', function() {
          declaration('public function %s() { }')->invoke(new \stdClass());
        });
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionmethod.invokeargs.php
    its('invokeArgs', [
      it('will invoke no-arg methods', function() {
        $decl= declaration('public function %s() { return "Test"; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual('Test', $decl->invokeArgs($instance, []));
      }),

      it('will pass a given argument to the method', function() {
        $decl= declaration('public function %s($arg) { return $arg; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual('Test', $decl->invokeArgs($instance, ['Test']));
      }),

      it('will pass given arguments to the method', function() {
        $decl= declaration('public function %s($a, $b) { return [$a, $b]; }');
        $instance= $decl->getDeclaringClass()->newInstance();
        shouldEqual([1, 2], $decl->invokeArgs($instance, [1, 2]));
      }),

      it('will raise an exception when trying to invoke non-public methods', ['private', 'protected'], function($modifier) {
        shouldThrow(\ReflectionException::class, '/Trying to invoke '.$modifier.' method/', function() use($modifier) {
          $decl= declaration($modifier.' function %s() { }');
          $instance= $decl->getDeclaringClass()->newInstance();
          $decl->invokeArgs($instance, []);
        });
      }),

      it('will raise an exception when trying to invoke abstract methods', function() {
        shouldThrow(\ReflectionException::class, '/Trying to invoke abstract method/', function() {
          declaration('abstract function %s();', 'abstract')->invokeArgs(new \stdClass(), []);
        });
      }),

      it('will raise an exception when trying to invoke instance methods without an instance', function() {
        shouldThrow(\ReflectionException::class, '/Trying to invoke non static method .+ without an object/', function() {
          declaration('public function %s() { }')->invokeArgs(null, []);
        });
      }),

      it('will raise an exception when trying to invoke instance methods with non-compatible instance', function() {
        shouldThrow(\ReflectionException::class, '/Given object is not an instance/', function() {
          declaration('public function %s() { }')->invokeArgs(new \stdClass(), []);
        });
      }),
    ]),

    // @see https://github.com/facebook/hhvm/issues/1357
    given(declaration('function %s($a, $b = null, $c) { }'), its('getNumberOfRequiredParameters', [
      it('regard all three as required parameters', function($decl) {
        shouldEqual(3, $decl->getNumberOfRequiredParameters());
      }),
    ])),

    its('string casting', [
      it('simplest form', function() {
        shouldEqual(
          "Method [ <user> public method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration('function %s() { }')
        );
      }),

      it('abstract form', ['abstract public', 'abstract protected'], function($modifiers) {
        shouldEqual(
          "Method [ <user> {$modifiers} method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration($modifiers.' function %s();', 'abstract')
        );
      }),

      it('final form', ['final public', 'final protected', 'final private'], function($modifiers) {
        shouldEqual(
          "Method [ <user> {$modifiers} method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration($modifiers.' function %s() { }')
        );
      }),

      it('will include modifiers', ['private', 'protected', 'public'], function($modifiers) {
        shouldEqual(
          "Method [ <user> {$modifiers} method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration($modifiers.' function %s() { }')
        );
      }),

      it('will include return by reference', function() {
        shouldEqual(
          "Method [ <user> public method &fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration('function &%s() { }')
        );
      }),

      it('will include api documentation', function() {
        shouldEqual(
          "/** Documented */\n".
          "Method [ <user> public method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "}\n",
          (string)declaration('/** Documented */ function %s() { }')
        );
      }),

      it('will include parameters', function() {
        $decl= declaration('function %s($a) { }');
        shouldEqual(
          "Method [ <user> public method fixture ] {\n".
          "  @@ ".__FILE__."(25) : eval()'d code 1 - 1\n".
          "\n".
          "  - Parameters [1] {\n".
          "    ".$decl->getParameters()[0]."\n".
          "  }\n".
          "}\n",
          (string)$decl
        );
      }),
    ])
  ]
]);
