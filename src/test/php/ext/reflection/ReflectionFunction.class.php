<?php namespace test\php\ext\reflection\ReflectionFunction;

// Fixtures
function fixture($param, Fixture $second) { 
}

// Helper: Returns a new ReflectionFunction for the fixture function
function newFixture() {
  return new \ReflectionFunction(__NAMESPACE__.'\fixture');
}

// Helper: Returns a new ReflectionFunction in the "standard" extension
function newInternal() {
  return new \ReflectionFunction('strstr');
}

// Helper: Returns ReflectionFunction for a function created dynamically via its declaration
function declaration($declaration) {
  static $uniq= 0;
  $name= 'fixture__'.$uniq++;
  eval(sprintf('namespace %s; '.$declaration, __NAMESPACE__, $name));
  return (new \ReflectionFunction(__NAMESPACE__.'\\'.$name));
}

// @see http://de3.php.net/manual/de/class.reflectionfunction.php
return new \behaviour\of\TheClass('ReflectionFunction', [

  'describe' => [
    it('raises warnings when without arguments', function() {
      shouldRaise('/expects exactly 1 parameter, 0 given/', function() {
        new \ReflectionFunction();
      });
    }),

    it('can be constructed with a function name', function() {
      shouldBe('ReflectionFunction', new \ReflectionFunction('strstr'));
    }),

    it('can be constructed with a closure', function() {
      shouldBe('ReflectionFunction', new \ReflectionFunction(function() { }));
    }),

    it('will have a public member "name"', function() {
      shouldEqual(__NAMESPACE__.'\fixture', newFixture()->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getname.php
    its('getName', [
      it('returns the function\'s name', function() {
        shouldEqual(__NAMESPACE__.'\fixture', newFixture()->getName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getnamespacename.php
    its('getNamespaceName', [
      it('returns the function\'s namespace name', function() {
        shouldEqual(__NAMESPACE__, newFixture()->getNamespaceName());
      }),

      it('returns an empty string for functions in the global namespace', function() {
        shouldEqual('', (new \ReflectionFunction('it'))->getNamespaceName());
      }),

      it('returns an empty string for an internal function', function() {
        shouldEqual('', newInternal()->getNamespaceName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.innamespace.php
    its('inNamespace', [
      it('returns true for function in namespace', function() {
        shouldEqual(true, newFixture()->inNamespace());
      }),

      it('returns false for functions in the global namespace', function() {
        shouldEqual(false, (new \ReflectionFunction('it'))->inNamespace());
      }),

      it('returns false for internal functions', function() {
        shouldEqual(false, newInternal()->inNamespace());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getshortname.php
    its('getShortName', [
      it('returns the function\'s name without the namespace', function() {
        shouldEqual('fixture', newFixture()->getShortName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getextension.php
    its('getExtension', [
      it('returns internal functions\' extension', function() {
        shouldEqual('standard', newInternal()->getExtension()->name);
      }),

      it('returns null for userland functions', function() {
        shouldEqual(null, newFixture()->getExtension());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getextensionname.php
    its('getExtensionName', [
      it('returns internal functions\' extension', function() {
        shouldEqual('standard', newInternal()->getExtensionName());
      }),

      it('returns false for userland functions', function() {
        shouldEqual(false, newFixture()->getExtensionName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getextension.php
    its('getFileName', [
      it('returns false for internal functions', function() {
        shouldEqual(false, newInternal()->getFileName());
      }),

      it('returns file the function is declared in for userland functions', function() {
        shouldEqual(__FILE__, newFixture()->getFileName());
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
        shouldBe(['ReflectionParameter'], declaration('function %s($a) { }')->getParameters());
      }),

      it('return a non-empty array of ReflectionParameter instances', function() {
        shouldBe(['ReflectionParameter', 'ReflectionParameter'], declaration('function %s($a, $b) { }')->getParameters());
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

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.isInternal.php
    its('isInternal', [
      it('returns true for internal functions', function() {
        shouldEqual(true, newInternal()->isInternal());
      }),

      it('returns false for userland functions', function() {
        shouldEqual(false, newFixture()->isInternal());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.isuserdefined.php
    its('isUserDefined', [
      it('returns false for internal functions', function() {
        shouldEqual(false, newInternal()->isUserDefined());
      }),

      it('returns true for userland functions', function() {
        shouldEqual(true, newFixture()->isUserDefined());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.isclosure.php
    its('isClosure', [
      it('returns false for internal functions', function() {
        shouldEqual(false, newInternal()->isClosure());
      }),

      it('returns false for userland functions', function() {
        shouldEqual(false, newFixture()->isClosure());
      }),

      it('returns true for closure', function() {
        shouldEqual(true, (new \ReflectionFunction(function($a) { return $a; }))->isClosure());
      }),

      it('returns false for functions created by create_function', function() {
        shouldEqual(false, (new \ReflectionFunction(create_function('$a', 'return $a;')))->isClosure());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.isdeprecated.php
    its('isDeprecated', [
      it('returns false for non-deprecated functions', [newFixture(), newInternal()], function($fixture) {
        shouldEqual(false, $fixture->isDeprecated());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.returnsreference.php
    its('returnsReference', [
      it('returns true for functions with reference', function() {
        shouldEqual(true, declaration('function &%s() { }')->returnsReference());
      }),

      it('returns false for fixture function', function() {
        shouldEqual(false, newFixture()->returnsReference());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getdoccomment.php
    its('getDocComment', [
      it('returns the entire doc comment including /** and */', function() {
        shouldEqual('/** Doc */', declaration('/** Doc */ function %s() { }')->getDocComment());
      }),

      it('returns false for declaration with regular multi-line comment', function() {
        shouldEqual(false, declaration('/* Non-Doc */ function %s() { }')->getDocComment());
      }),

      it('returns false for declaration with no comment', function() {
        shouldEqual(false, declaration('function %s() { }')->getDocComment());
      }),

      it('returns false for fixture function', function() {
        shouldEqual(false, newFixture()->getDocComment());
      }),

      it('returns the entire doc comment including /*** and */ for closures', function() {
        shouldEqual('/** Doc */', (new \ReflectionFunction(/** Doc */ function() { }))->getDocComment());
      }),

      it('returns false for closures with regular multi-line comment', function() {
        shouldEqual(false, (new \ReflectionFunction(/* Non-Doc */ function() { }))->getDocComment());
      }),

      it('returns false for closures with no comment', function() {
        shouldEqual(false, (new \ReflectionFunction(function() { }))->getDocComment());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getstartline.php
    its('getStartLine', [
      it('returns 4 for fixture function', function() {
        shouldEqual(4, newFixture()->getStartLine());
      }),

      it('returns false for internal function', function() {
        shouldEqual(false, newInternal()->getStartLine());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getendline.php
    its('getEndLine', [
      it('returns 5 for fixture function', function() {
        shouldEqual(5, newFixture()->getEndLine());
      }),

      it('returns false for internal function', function() {
        shouldEqual(false, newInternal()->getEndLine());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getstaticvariables.php
    its('getStaticVariables', [
      it('returns an empty array for fixture function', function() {
        shouldEqual([], newFixture()->getStaticVariables());
      }),

      it('returns variable and null for uninitialized static variable', function() {
        shouldEqual(['a' => null], declaration('function %s() { static $a; }')->getStaticVariables());
      }),

      it('returns variables and values as an associative array', function() {
        shouldEqual(['a' => 1, 'b' => 2], declaration('function %s() { static $a= 1, $b= 2; }')->getStaticVariables());
      }),
    ]),
  ]
]);