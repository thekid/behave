<?php namespace test\php\ext\reflection\ReflectionClass;

// Fixtures
class Base {
  public $base;
}

class Fixture extends Base {
  const CONSTANT = true;
  public $field;
  public function method() { }
}

// Helper: Returns a new ReflectionClass for the fixture function
function newFixture() {
  return new \ReflectionClass(__NAMESPACE__.'\Fixture');
}

// Helper: Returns a new ReflectionClass in the "standard" extension
function newInternal() {
  return new \ReflectionClass('Directory');
}

// Helper: Returns ReflectionClass for a function created dynamically via its declaration
function declaration($declaration) {
  static $uniq= 0;
  $name= 'Fixture__'.$uniq++;
  eval(sprintf('namespace %s; '.$declaration, __NAMESPACE__, $name));
  return (new \ReflectionClass(__NAMESPACE__.'\\'.$name));
}

// @see http://de3.php.net/manual/de/class.reflectionclass.php
return new \behaviour\of\TheClass('ReflectionClass', [

  'describe' => [
    it('raises warnings when without arguments', function() {
      shouldRaise('/expects exactly 1 parameter, 0 given/', function() {
        new \ReflectionClass();
      });
    }),

    it('can be constructed with a class name', function() {
      shouldBe(\ReflectionClass::class, new \ReflectionClass(Fixture::class));
    }),

    it('can be constructed with an object', function() {
      shouldBe(\ReflectionClass::class, new \ReflectionClass(new Fixture()));
    }),

    it('raises an exception when the given class does not exist', function() {
      shouldThrow(\ReflectionException::class, '/Class __non-existant__ does not exist/', function() {
        new \ReflectionClass('__non-existant__');
      });
    }),

    it('will have a public member "name"', function() {
      shouldEqual(Fixture::class, newFixture()->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionclass.getname.php
    its('getName', [
      it('returns the class\' name', function() {
        shouldEqual(Fixture::class, newFixture()->getName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getnamespacename.php
    its('getNamespaceName', [
      it('returns the class\' namespace name', function() {
        shouldEqual(__NAMESPACE__, newFixture()->getNamespaceName());
      }),

      it('returns an empty string for an internal class', function() {
        shouldEqual('', newInternal()->getNamespaceName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.innamespace.php
    its('inNamespace', [
      it('returns true for classes in a namespace', function() {
        shouldEqual(true, newFixture()->inNamespace());
      }),

      it('returns false for internal classes', function() {
        shouldEqual(false, newInternal()->inNamespace());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getshortname.php
    its('getShortName', [
      it('returns the class\' name without the namespace', function() {
        shouldEqual('Fixture', newFixture()->getShortName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getextension.php
    its('getExtension', [
      it('returns internal class\' extension', function() {
        shouldEqual('standard', newInternal()->getExtension()->name);
      }),

      it('returns null for userland classes', function() {
        shouldEqual(null, newFixture()->getExtension());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getextensionname.php
    its('getExtensionName', [
      it('returns internal class\' extension', function() {
        shouldEqual('standard', newInternal()->getExtensionName());
      }),

      it('returns false for userland classes', function() {
        shouldEqual(false, newFixture()->getExtensionName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getextension.php
    its('getFileName', [
      it('returns false for internal classes', function() {
        shouldEqual(false, newInternal()->getFileName());
      }),

      it('returns file the class is declared in for userland classes', function() {
        shouldEqual(__FILE__, newFixture()->getFileName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getparentclass.php
    its('getParentClass', [
      it('returns the parent class for child classes', function() {
        shouldEqual(Base::class, newFixture()->getParentClass()->name);
      }),

      it('returns false for the base class', function() {
        shouldEqual(false, (new \ReflectionClass(Base::class))->getParentClass());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.issubclassof.php
    its('isSubclassOf', [
      it('is subclass of base', [Base::class, new \ReflectionClass(Base::class)], function($variant) {
        shouldEqual(true, newFixture()->isSubclassOf($variant));
      }),

      it('is not subclass of self', [Fixture::class, new \ReflectionClass(Fixture::class)], function($variant) {
        shouldEqual(false, newFixture()->isSubclassOf($variant));
      }),

      it('is not subclass of built-in Exception class', ['Exception', new \ReflectionClass('Exception')], function($variant) {
        shouldEqual(false, newFixture()->isSubclassOf($variant));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isinstance.php
    its('isInstance', [
      it('a Fixture object is instance of itself', function() {
        shouldEqual(true, newFixture()->isInstance(new Fixture()));
      }),

      it('a Fixture object is instance of base', function() {
        shouldEqual(true, (new \ReflectionClass(Base::class))->isInstance(new Fixture()));
      }),

      it('a Fixture object is not an instance of the built-in Exception class', function() {
        shouldEqual(false, (new \ReflectionClass('Exception'))->isInstance(new Fixture()));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isuserdefined.php
    its('isUserDefined', [
      it('returns true for userland classes', function() {
        shouldEqual(true, newFixture()->isUserDefined());
      }),

      it('returns false for internal classes', function() {
        shouldEqual(false, newInternal()->isUserDefined());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isInternal.php
    its('isInternal', [
      it('returns false for userland classes', function() {
        shouldEqual(false, newFixture()->isInternal());
      }),

      it('returns true for internal classes', function() {
        shouldEqual(true, newInternal()->isInternal());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.istrait.php
    its('isTrait', [
      it('returns false for interfaces', function() {
        shouldEqual(false, declaration('interface %s { }')->isTrait());
      }),

      it('returns false for classes', function() {
        shouldEqual(false, declaration('class %s { }')->isTrait());
      }),

      it('returns true only for traits', function() {
        shouldEqual(true, declaration('trait %s { }')->isTrait());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isinterface.php
    its('isInterface', [
      it('returns false for traits', function() {
        shouldEqual(false, declaration('trait %s { }')->isInterface());
      }),

      it('returns false for classes', function() {
        shouldEqual(false, declaration('class %s { }')->isInterface());
      }),

      it('returns true only for interfaces', function() {
        shouldEqual(true, declaration('interface %s { }')->isInterface());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isinstantiable.php
    its('isInstantiable', [
      it('returns false for traits', function() {
        shouldEqual(false, declaration('trait %s { }')->isInstantiable());
      }),

      it('returns false for interfaces', function() {
        shouldEqual(false, declaration('interface %s { }')->isInstantiable());
      }),

      it('returns false for abstract classes', function() {
        shouldEqual(false, declaration('abstract class %s { }')->isInstantiable());
      }),

      it('returns true for non-abstract classes', function() {
        shouldEqual(true, declaration('class %s { }')->isInstantiable());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isabstract.php
    its('isAbstract', [
      it('returns false for interfaces', function() {
        shouldEqual(false, declaration('interface %s { }')->isAbstract());
      }),

      it('returns true for traits', function() {
        shouldEqual(true, declaration('trait %s { }')->isAbstract());
      }),

      it('returns true for abstract classes', function() {
        shouldEqual(true, declaration('abstract class %s { }')->isAbstract());
      }),

      it('returns false for non-abstract classes', function() {
        shouldEqual(false, declaration('class %s { }')->isAbstract());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.isfinal.php
    its('isFinal', [
      it('returns false for interfaces', function() {
        shouldEqual(false, declaration('interface %s { }')->isFinal());
      }),

      it('returns false for traits', function() {
        shouldEqual(false, declaration('trait %s { }')->isFinal());
      }),

      it('returns true for final classes', function() {
        shouldEqual(true, declaration('final class %s { }')->isFinal());
      }),

      it('returns false for non-final classes', function() {
        shouldEqual(false, declaration('class %s { }')->isFinal());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.iscloneable.php
    its('isCloneable', [
      it('returns false for interfaces', function() {
        shouldEqual(false, declaration('interface %s { }')->isCloneable());
      }),

      it('returns false for traits', function() {
        shouldEqual(false, declaration('trait %s { }')->isCloneable());
      }),

      it('returns true for final classes', function() {
        shouldEqual(true, declaration('final class %s { }')->isCloneable());
      }),

      it('returns false for abstract classes', function() {
        shouldEqual(false, declaration('abstract class %s { }')->isCloneable());
      }),

      it('returns true for other classes', function() {
        shouldEqual(true, declaration('class %s { }')->isCloneable());
      }),

      it('returns true for classes with public __clone method', function() {
        shouldEqual(true, declaration('class %s { public function __clone() { }}')->isCloneable());
      }),

      it('returns false for classes with private __clone method', function() {
        shouldEqual(false, declaration('class %s { private function __clone() { }}')->isCloneable());
      }),

      it('returns false for classes with protected __clone method', function() {
        shouldEqual(false, declaration('class %s { protected function __clone() { }}')->isCloneable());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getModifiers.php
    its('getModifiers', [
      it('returns final modifier', function() {
        shouldEqual(\ReflectionClass::IS_FINAL, declaration('final class %s { }')->getModifiers());
      }),

      it('returns abstract modifier', function() {
        shouldEqual(\ReflectionClass::IS_EXPLICIT_ABSTRACT, declaration('abstract class %s { }')->getModifiers());
      }),

      it('returns 0 for other classes', function() {
        shouldEqual(0, declaration('class %s { }')->getModifiers());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.implementsInterface.php
    its('implementsInterface', [
      it('class without interfaces does not implement anything', function() {
        shouldEqual(false, declaration('class %s { }')->implementsInterface('Serializable'));
      }),

      it('class implements implemented interface', function() {
        $class= declaration('class %s implements \IteratorAggregate {
          public function getIterator() { }
        }');
        shouldEqual(true, $class->implementsInterface('IteratorAggregate'));
      }),

      it('class implements all implemented interfaces', function() {
        $class= declaration('class %s implements \IteratorAggregate, \Countable {
          public function getIterator() { }
          public function count() { }
        }');
        shouldEqual([true, true], [
          $class->implementsInterface('IteratorAggregate'),
          $class->implementsInterface('Countable')
        ]);
      }),

      it('class implements implemented interface\'s parent interface', function() {
        $class= declaration('class %s implements \IteratorAggregate {
          public function getIterator() { }
        }');
        shouldEqual(true, $class->implementsInterface('Traversable'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getinterfacenames.php
    its('getInterfaceNames', [
      it('returns an empty array for classes not implementing any interfaces', function() {
        shouldEqual([], declaration('class %s { }')->getInterfaceNames());
      }),

      it('returns the interface implemented by a class', function() {
        $class= declaration('class %s implements \Serializable {
          public function serialize() { }
          public function unserialize($stream) { }
        }');
        shouldEqual(['Serializable'], $class->getInterfaceNames());
      }),

      it('returns implemented interfaces in the order they\'re declared', function() {
        $class= declaration('class %s implements \Serializable, \Countable {
          public function serialize() { }
          public function unserialize($stream) { }
          public function count() { }
        }');
        shouldEqual(['Serializable', 'Countable'], $class->getInterfaceNames());
      }),

      it('returns parent interfaces included in class\'  implemented list', function() {
        $class= declaration('class %s implements \IteratorAggregate {
          public function getIterator() { }
        }');
        shouldEqual(['IteratorAggregate', 'Traversable'], $class->getInterfaceNames());
      }),

      it('return an empty array for interfaces not extending any interfaces', function() {
        shouldEqual([], declaration('interface %s { }')->getInterfaceNames());
      }),

      it('returns the parent interface of an interface', function() {
        $iface= declaration('interface %s extends \Serializable { }');
        shouldEqual(['Serializable'], $iface->getInterfaceNames());
      }),

      it('returns parent interfaces included in interfaces\' parents list', function() {
        $iface= declaration('interface %s extends \IteratorAggregate { }');
        shouldEqual(['IteratorAggregate', 'Traversable'], $iface->getInterfaceNames());
      }),

      it('returns parent interfaces in the order they\'re declared', function() {
        $iface= declaration('interface %s extends \Serializable, \Countable { }');
        shouldEqual(['Serializable', 'Countable'], $iface->getInterfaceNames());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getinterfaces.php
    its('getInterfaces', [
      it('returns an empty array for classes not implementing any interfaces', function() {
        shouldEqual([], declaration('class %s { }')->getInterfaces());
      }),

      it('returns the interface implemented by a class as a hash', function() {
        $class= declaration('class %s implements \Serializable {
          public function serialize() { }
          public function unserialize($stream) { }
        }');
        shouldBe(['Serializable' => \ReflectionClass::class], $class->getInterfaces());
      }),
    ]),
  
    // @see http://de3.php.net/manual/de/reflectionclass.getconstructor.php
    its('getConstructor', [
      it('returns null for classes without __construct', function() {
        shouldEqual(null, declaration('class %s { }')->getConstructor());
      }),

      it('returns constructor method', function() {
        shouldBe(\ReflectionMethod::class, declaration('class %s { public function __construct() { }}')->getConstructor());
      }),
    ]),

     // @see http://de3.php.net/manual/de/reflectionclass.getconstants.php
    its('getConstants', [
      it('returns an empty array for a class without constants', function() {
        shouldEqual([], declaration('class %s { }')->getConstants());
      }),

      it('returns constants as hash', function() {
        shouldEqual(
          ['A' => 1, 'B' => 2],
          declaration('class %s { const A = 1; const B = 2; }')->getConstants()
        );
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.hasConstant.php
    its('hasConstant', [
      it('returns false for non-existant constants', function() {
        shouldEqual(false, newFixture()->hasConstant('__non-existant__'));
      }),

      it('returns true for existing constants', function() {
        shouldEqual(true, newFixture()->hasConstant('CONSTANT'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getconstant.php
    its('getConstant', [
      it('returns false when the property does non exist', function() {
        shouldEqual(false, newFixture()->getConstant('__non-existant__'));
      }),

      it('returns existing constant', function() {
        shouldEqual(true, newFixture()->getConstant('CONSTANT'));
      }),
    ]),
 
     // @see http://de3.php.net/manual/de/reflectionclass.getproperties.php
    its('getProperties', [
      it('returns an empty array for a class without properties', function() {
        shouldEqual([], declaration('class %s { }')->getProperties());
      }),

      it('returns properties in the order declared', function() {
        shouldEqual(['a', 'b'], array_map(
          function($e) { return shouldBe(\ReflectionProperty::class, $e)->name; },
          declaration('class %s { public $a; private $b; }')->getProperties()
        ));
      }),

      it('filter properties not included in filter', function() {
        shouldEqual(['a'], array_map(
          function($e) { return shouldBe(\ReflectionProperty::class, $e)->name; },
          declaration('class %s { public $a; private $b; }')->getProperties(\ReflectionMethod::IS_PUBLIC)
        ));
      }),

      it('includes inherited properties before own properties', function() {
        shouldEqual(['field', 'base'], array_map(
          function($e) { return shouldBe(\ReflectionProperty::class, $e)->name; },
          newFixture()->getProperties()
        ));
      })
    ]),

     // @see http://de3.php.net/manual/de/reflectionclass.getDefaultProperties.php
    its('getDefaultProperties', [
      it('returns an empty array for a class without properties', function() {
        shouldEqual([], declaration('class %s { }')->getDefaultProperties());
      }),

      it('includes public properties', function() {
        shouldEqual(['p' => 1], declaration('class %s { public $p= 1; }')->getDefaultProperties());
      }),

      it('includes protected properties', function() {
        shouldEqual(['p' => 1], declaration('class %s { protected $p= 1; }')->getDefaultProperties());
      }),

      it('includes private properties', function() {
        shouldEqual(['p' => 1], declaration('class %s { private $p= 1; }')->getDefaultProperties());
      }),

      it('includes static properties', function() {
        shouldEqual(['p' => 1], declaration('class %s { static $p= 1; }')->getDefaultProperties());
      }),

      it('includes unitialized properties', function() {
        shouldEqual(['p' => null], declaration('class %s { static $p; }')->getDefaultProperties());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.hasproperty.php
    its('hasProperty', [
      it('returns false for non-existant properties', function() {
        shouldEqual(false, newFixture()->hasProperty('__non-existant__'));
      }),

      it('returns true for existing properties', function() {
        shouldEqual(true, newFixture()->hasProperty('field'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getproperty.php
    its('getProperty', [
      it('raises an exception when the property does non exist', function() {
        shouldThrow(\ReflectionException::class, '/Property __non-existant__ does not exist/', function() {
          declaration('class %s { }')->getProperty('__non-existant__');
        });
      }),

      it('returns existing property', function() {
        shouldBe(\ReflectionProperty::class, newFixture()->getProperty('field'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.hasMethod.php
    its('hasMethod', [
      it('returns false for non-existant methods', function() {
        shouldEqual(false, newFixture()->hasMethod('__non-existant__'));
      }),

      it('returns true for existing methods', function() {
        shouldEqual(true, newFixture()->hasMethod('method'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getmethod.php
    its('getMethod', [
      it('raises an exception when the method does non exist', function() {
        shouldThrow(\ReflectionException::class, '/Method __non-existant__ does not exist/', function() {
          declaration('class %s { }')->getMethod('__non-existant__');
        });
      }),

      it('returns existing method', function() {
        shouldBe(\ReflectionMethod::class, newFixture()->getMethod('method'));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getmethods.php
    its('getMethods', [
      it('returns an empty array for a class without methods', function() {
        shouldEqual([], declaration('class %s { }')->getMethods());
      }),

      it('returns methods in the order declared', function() {
        shouldEqual(['a', 'b'], array_map(
          function($e) { return shouldBe(\ReflectionMethod::class, $e)->name; },
          declaration('class %s { public function a() { } private function b() { }}')->getMethods()
        ));
      }),

      it('filter methods not included in filter', function() {
        shouldEqual(['a'], array_map(
          function($e) { return shouldBe(\ReflectionMethod::class, $e)->name; },
          declaration('class %s { public function a() { } private function b() { }}')->getMethods(\ReflectionMethod::IS_PUBLIC)
        ));
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionfunctionabstract.getdoccomment.php
    its('getDocComment', [
      it('returns the entire doc comment including /** and */', function() {
        shouldEqual('/** Doc */', declaration('/** Doc */ class %s { }')->getDocComment());
      }),

      it('returns false for declaration with regular multi-line comment', function() {
        shouldEqual(false, declaration('/* Non-Doc */ class %s { }')->getDocComment());
      }),

      it('returns false for declaration with no comment', function() {
        shouldEqual(false, declaration('class %s { }')->getDocComment());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getstartline.php
    its('getStartLine', [
      it('returns 8 for fixture class', function() {
        shouldEqual(8, newFixture()->getStartLine());
      }),

      it('returns false for internal class', function() {
        shouldEqual(false, newInternal()->getStartLine());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionclass.getendline.php
    its('getEndLine', [
      it('returns 12 for fixture class', function() {
        shouldEqual(12, newFixture()->getEndLine());
      }),

      it('returns false for internal class', function() {
        shouldEqual(false, newInternal()->getEndLine());
      }),
    ]),


    // @see https://github.com/facebook/hhvm/issues/1572
    it('Has a cloning implementation', function() {
      shouldEqual(true, method_exists('ReflectionClass', '__clone'));
    }),
  ]
]);