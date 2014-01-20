<?php namespace test\php\ext\reflection\ReflectionParameter;

// Fixtures
const CONSTANT = 2;

function fixture($param, Fixture $second) {
}

class Base {
}

class Fixture extends Base {
  const CONSTANT = 1;

  public function method($param, Fixture $second) { }
  public function newInstance(self $template) { }
  public function copyOf(parent $origin) { }
}

// Helper: Returns a new ReflectionParameter for the fixture function
function functionParameter($arg) {
  return new \ReflectionParameter(__NAMESPACE__.'\fixture', $arg);
}

// Helper: Returns a new ReflectionParameter for the Fixture's method
function methodParameter($method, $num) {
  return (new \ReflectionMethod(__NAMESPACE__.'\Fixture', $method))->getParameters()[$num];
}

// Helper: Returns parameters for a function created dynamically via its signature
function signature($signature) {
  static $uniq= 0;
  $name= 'fixture__'.$uniq++;
  eval(sprintf('namespace %s; function %s %s { }', __NAMESPACE__, $name, $signature));
  return (new \ReflectionFunction(__NAMESPACE__.'\\'.$name))->getParameters();
}

// Helper: Creates anonymous object
function newinstance($definitions) {
  static $uniq= 0;

  // Compile class body
  $body= '';
  foreach ($definitions as $member => $definition) {
    if ($definition instanceof \Closure) {
      $body.= 'function '.$member.'() { return forward_static_call_array(self::$f["'.$member.'"], func_get_args()); }';
    } else {
      $body.= 'public $'.$member.';';
    }
  }

  // Define class
  $name= 'Object__'.$uniq++;  
  eval(sprintf('class %s { static $f= []; %s }', $name, $body));
  $name::$f= $definitions;
  return new $name();
}

// @see http://de3.php.net/manual/de/class.reflectionparameter.php
return new \behaviour\of\TheClass('ReflectionParameter', [

  'describe' => [
    it('raises warnings when without arguments', function() {
      shouldRaise('/Undefined variable: param/', function() { 
        new \ReflectionParameter();
      });
    }),

    it('raises warnings when constructed with just one argument ', function() {
      shouldThrow('ReflectionException', '/Function __irrelevant__ does not exist/', function() { 
        new \ReflectionParameter('__irrelevant__');
      });
    }),

    it('can be constructed with a function name and an integer', function() {
      shouldBe('ReflectionParameter', functionParameter(0));
    }),

    it('can be constructed with a function and  string', ['param', 'second'], function($name) {
      shouldBe('ReflectionParameter', functionParameter($name));
    }),

    it('can be constructed with a function and a string-castable object', function() {
      shouldBe('ReflectionParameter', functionParameter(newinstance([
        '__toString' => function() { return 'param'; }
      ])));
    }),

    // see https://github.com/facebook/hhvm/issues/1358
    it('can be constructed with a closure and a string', ['a', 'b'], function($name) {
      shouldBe('ReflectionParameter', new \ReflectionParameter(function($a, $b) { }, $name));
    }),

    it('can be constructed with a closure and an integer', [0, 1], function($name) {
      shouldBe('ReflectionParameter', new \ReflectionParameter(function($a, $b) { }, $name));
    }),

    it('will have a public member "name"', [0, 'param'], function($arg) {
      shouldEqual('param', functionParameter($arg)->name);
    }),

    it('raises an exception for unknown offsets', [-1, 2], function($offset) {
      shouldThrow('ReflectionException', '/The parameter specified by its offset could not be found/', function() use($offset) {
        functionParameter($offset);
      });
    }),

    it('raises an exception for unknown names', [null, '', '__non-existant__'], function($name) {
      shouldThrow('ReflectionException', '/The parameter specified by its name could not be found/', function() use($name) {
        functionParameter($name);
      });
    }),

    it('is not case-insensitive', function() {
      shouldThrow('ReflectionException', '/The parameter specified by its name could not be found/', function() {
        functionParameter('PARAM');
      });
    }),

    it('handles all other types as names', [true, false, -0.5, [], [1, 2, 3], ['hello' => 'world']], function($name) {
      shouldThrow('ReflectionException', '/The parameter specified by its name could not be found/', function() use($name) {
        functionParameter($name);
      });
    }),

    it('can be retrieved via ReflectionFunction\'s getParameters()', function() {
      $param= (new \ReflectionFunction(__NAMESPACE__.'\fixture'))->getParameters()[0];
      shouldEqual('param', $param->name);
    }),

    it('can be retrieved via ReflectionMethod\'s getParameters()', function() {
      $param= (new \ReflectionMethod(__NAMESPACE__.'\Fixture', 'method'))->getParameters()[0];
      shouldEqual('param', $param->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionparameter.getname.php
    its('getName', [
      it('returns the parameters name', function() {
        shouldEqual('param', functionParameter(0)->getName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionparameter.getposition.php
    its('getPosition', [
      it('returns the parameter\'s postion', [0, 1], function($offset) {
        shouldEqual($offset, functionParameter($offset)->getPosition());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionparameter.getdeclaringclass.php
    its('getDeclaringClass', [
      it('returns the declaring class', function() {
        shouldEqual(__NAMESPACE__.'\Fixture', methodParameter('method', 0)->getDeclaringClass()->name);
      }),

      it('returns null when the parameter belongs to a function', function() {
        shouldEqual(null, functionParameter(0)->getDeclaringClass());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionparameter.getdeclaringfunction.php
    its('getDeclaringFunction', [
      it('returns the declaring method', function() {
        shouldEqual('method', methodParameter('method', 0)->getDeclaringFunction()->name);
      }),

      it('returns the declaring function', function() {
        shouldEqual(__NAMESPACE__.'\fixture', functionParameter(0)->getDeclaringFunction()->name);
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionparameter.getclass.php
    given(signature('($a, array $b, Fixture $c)'), its('getClass', [

      it('returns null for parameters without typehint', function($params) {
        shouldEqual(null, $params[0]->getClass());
      }),

      it('returns null for parameters with array typehint', function($params) {
        shouldEqual(null, $params[1]->getClass());
      }),

      it('returns ReflectionClass instance for typehinted parameters', function($params) {
        shouldEqual(__NAMESPACE__.'\Fixture', $params[2]->getClass()->name);
      })
    ])),
    its('getClass', [

      it('returns ReflectionClass instance for parameters typehinted with "self"', function() {
        shouldEqual(__NAMESPACE__.'\Fixture', methodParameter('newInstance', 0)->getClass()->name);
      }),

      it('returns ReflectionClass instance for parameters typehinted with "parent"', function() {
        shouldEqual(__NAMESPACE__.'\Base', methodParameter('copyOf', 0)->getClass()->name);
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionparameter.allowsnull.php
    given(signature('($a, array $b, Fixture $c, Fixture $d= null, $e= 1)'), its('allowsNull', [

      it('returns true for parameters without typehints', function($params) {
        shouldEqual(true, $params[0]->allowsNull());
      }),

      it('returns false for array type hint', function($params) {
        shouldEqual(false, $params[1]->allowsNull());
      }),

      it('returns false for parameters typehints', function($params) {
        shouldEqual(false, $params[2]->allowsNull());
      }),

      it('returns true for parameters typehints with = null', function($params) {
        shouldEqual(true, $params[3]->allowsNull());
      }),

      it('returns true for optional parameters without typehints', function($params) {
        shouldEqual(true, $params[4]->allowsNull());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.isarray.php
    given(signature('($a, array $b, callable $c, Fixture $d)'), its('isArray', [

      it('returns false for parameters without typehints', function($params) {
        shouldEqual(false, $params[0]->isArray());
      }),

      it('returns true for array type hint', function($params) {
        shouldEqual(true, $params[1]->isArray());
      }),

      it('returns false for callable typehints', function($params) {
        shouldEqual(false, $params[2]->isArray());
      }),

      it('returns false for calls typehints', function($params) {
        shouldEqual(false, $params[3]->isArray());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.iscallable.php
    given(signature('($a, array $b, callable $c, Fixture $d)'), its('isCallable', [

      it('returns false for parameters without typehints', function($params) {
        shouldEqual(false, $params[0]->isCallable());
      }),

      it('returns false for array type hint', function($params) {
        shouldEqual(false, $params[1]->isCallable());
      }),

      it('returns true for callable typehints', function($params) {
        shouldEqual(true, $params[2]->isCallable());
      }),

      it('returns false for calls typehints', function($params) {
        shouldEqual(false, $params[3]->isCallable());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.isoptional.php
    given(signature('($a, $b= null, Fixture $c= null, array $d= [])'), its('isOptional', [

      it('returns false for required parameters', function($params) {
        shouldEqual(false, $params[0]->isOptional());
      }),

      it('returns true for optional parameters', function($params) {
        shouldEqual(true, $params[1]->isOptional());
      }),

      it('returns true for typehinted parameters with = null', function($params) {
        shouldEqual(true, $params[2]->isOptional());
      }),

      it('returns true for array parameters with array default', function($params) {
        shouldEqual(true, $params[3]->isOptional());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.isdefaultvalueavailable.php
    given(signature('($a, $b= null, Fixture $c= null, array $d= [])'), its('isDefaultValueAvailable', [

      it('returns false for required parameters', function($params) {
        shouldEqual(false, $params[0]->isDefaultValueAvailable());
      }),

      it('returns true for optional parameters', function($params) {
        shouldEqual(true, $params[1]->isDefaultValueAvailable());
      }),

      it('returns true for typehinted parameters with = null', function($params) {
        shouldEqual(true, $params[2]->isDefaultValueAvailable());
      }),

      it('returns true for array parameters with array default', function($params) {
        shouldEqual(true, $params[3]->isDefaultValueAvailable());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.ispassedbyreference.php
    given(signature('($a, &$b)'), its('isPassedByReference', [

      it('returns false for by-value parameters', function($params) {
        shouldEqual(false, $params[0]->isPassedByReference());
      }),

      it('returns true for by-ref parameters', function($params) {
        shouldEqual(true, $params[1]->isPassedByReference());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.canbepassedbyvalue.php
    given(signature('($a, &$b, $c= 1)'), its('canBePassedByValue', [

      it('returns true for by-value parameters', function($params) {
        shouldEqual(true, $params[0]->canBePassedByValue());
      }),

      it('returns false for by-ref parameters', function($params) {
        shouldEqual(false, $params[1]->canBePassedByValue());
      }),

      it('returns true for optional by-value parameters', function($params) {
        shouldEqual(true, $params[2]->canBePassedByValue());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.isdefaultvalueconstant.php
    given(signature('($a, $b= null, $c= \E_ERROR, $d= Fixture::CONSTANT, $d= CONSTANT, $e= [\E_ERROR])'), its('isDefaultValueConstant', [

      it('raises an exception when called for required parameters', function($params) {
        shouldThrow('ReflectionException', '/Parameter is not optional/', function() use($params) {
          $params[0]->isDefaultValueConstant();
        });
      }),

      it('returns false for null default', function($params) {
        shouldEqual(false, $params[1]->isDefaultValueConstant());
      }),

      it('returns true for global constants', function($params) {
        shouldEqual(true, $params[2]->isDefaultValueConstant());
      }),

      it('returns true for class constants', function($params) {
        shouldEqual(true, $params[3]->isDefaultValueConstant());
      }),

      it('returns true for namespace constants', function($params) {
        shouldEqual(true, $params[4]->isDefaultValueConstant());
      }),

      it('returns false for array default', function($params) {
        shouldEqual(false, $params[5]->isDefaultValueConstant());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.getdefaultvalue.php
    given(signature('($a, $b= null, $c= Fixture::CONSTANT, $d= [1, 2, 3])'), its('getDefaultValue', [

      it('raises an exception when called for required parameters', function($params) {
        shouldThrow('ReflectionException', '/Parameter is not optional/', function() use($params) {
          $params[0]->getDefaultValue();
        });
      }),

      it('returns null for null default', function($params) {
        shouldEqual(null, $params[1]->getDefaultValue());
      }),

      it('returns value of class constants', function($params) {
        shouldEqual(Fixture::CONSTANT, $params[2]->getDefaultValue());
      }),

      it('returns value of arrays', function($params) {
        shouldEqual([1, 2, 3], $params[3]->getDefaultValue());
      }),
    ])),

    // @see http://de3.php.net/manual/de/reflectionparameter.getdefaultvalueconstantname.php
    given(signature('($a, $b= null, $c= \E_ERROR, $c= Fixture::CONSTANT, $d= CONSTANT, $e= [\E_ERROR])'), its('getDefaultValueConstantName', [

      it('raises an exception when called for required parameters', function($params) {
        shouldThrow('ReflectionException', '/Parameter is not optional/', function() use($params) {
          $params[0]->getDefaultValueConstantName();
        });
      }),

      it('returns null for null default', function($params) {
        shouldEqual(null, $params[1]->getDefaultValueConstantName());
      }),

      it('returns name for global constants', function($params) {
        shouldEqual('E_ERROR', $params[2]->getDefaultValueConstantName());
      }),

      it('returns name of class constants', function($params) {
        shouldEqual(__NAMESPACE__.'\Fixture'.'::CONSTANT', $params[3]->getDefaultValueConstantName());
      }),

      it('returns name for namespace constant', function($params) {
        shouldEqual(__NAMESPACE__.'\\CONSTANT', $params[4]->getDefaultValueConstantName());
      }),

      it('returns null for array default', function($params) {
        shouldEqual(null, $params[5]->getDefaultValueConstantName());
      }),

    ])),

    // @see https://github.com/facebook/hhvm/issues/1572
    it('Has a cloning implementation', function() {
      shouldEqual(true, method_exists('ReflectionParameter', '__clone'));
    }),

    // @see https://github.com/facebook/hhvm/issues/1571
    given(signature('($a, &$b, array $c, callable $d, Fixture $e, $f= null)'), its('string casting', [
      it('will yield indicator for required parameters', function($params) {
        shouldEqual('Parameter #0 [ <required> $a ]', (string)$params[0]);
      }),

      it('will yield indicator for required by-ref parameters', function($params) {
        shouldEqual('Parameter #1 [ <required> &$b ]', (string)$params[1]);
      }),

      it('will yield array type hint', function($params) {
        shouldEqual('Parameter #2 [ <required> array $c ]', (string)$params[2]);
      }),

      it('will yield callable type hint', function($params) {
        shouldEqual('Parameter #3 [ <required> callable $d ]', (string)$params[3]);
      }),

      it('will yield userland class type hint', function($params) {
        shouldEqual('Parameter #4 [ <required> '.__NAMESPACE__.'\Fixture $e ]', (string)$params[4]);
      }),

      it('will yield indicator for default value for optional parameters', function($params) {
        shouldEqual('Parameter #5 [ <optional> $f = NULL ]', (string)$params[5]);
      }),
    ])),
  ]
]);
