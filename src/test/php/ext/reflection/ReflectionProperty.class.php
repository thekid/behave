<?php namespace test\php\ext\reflection\ReflectionProperty;

class Base {
  protected $inherited;
  public static $EMPTY = null;
}
class Fixture extends Base {
  public $field;
  private $internal;
}

function fixtureField($name) {
  return new \ReflectionProperty(Fixture::class, $name);
}

// @see http://de3.php.net/manual/de/class.reflectionproperty.php
return new \behaviour\of\TheClass('ReflectionProperty', [

  'describe' => [
    it('raises warnings when without arguments', function() {
      shouldRaise('/expects exactly 2 parameters, 0 given/', function() {
        new \ReflectionProperty();
      });
    }),

    it('raises warnings when constructed with just one argument ', function() {
      shouldRaise('/expects exactly 2 parameters, 1 given/', function() {
        new \ReflectionProperty('__irrelevant__');
      });
    }),

    it('can be constructed with a class name and property name', function() {
      shouldBe(\ReflectionProperty::class, fixtureField('field'));
    }),

    it('will have a public member "name"', function() {
      shouldEqual('field', fixtureField('field')->name);
    }),

    // @see http://de3.php.net/manual/de/reflectionproperty.getname.php
    its('getName', [
      it('returns the parameters name', function() {
        shouldEqual('field', fixtureField('field')->getName());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.getdeclaringclass.php
    its('getDeclaringClass', [
      it('returns the declaring class', function() {
        shouldEqual(Fixture::class, fixtureField('field')->getDeclaringClass()->name);
      }),

      it('returns the declaring class for inherited properties', function() {
        shouldEqual(Base::class, fixtureField('inherited')->getDeclaringClass()->name);
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.getmodifiers.php
    its('getModifiers', [
      it('return PUBLIC', function() {
        shouldEqual(\ReflectionProperty::IS_PUBLIC, fixtureField('field')->getModifiers());
      }),

      it('return PROTECTED', function() {
        shouldEqual(\ReflectionProperty::IS_PROTECTED, fixtureField('inherited')->getModifiers());
      }),

      it('return PRIVATE', function() {
        shouldEqual(\ReflectionProperty::IS_PRIVATE, fixtureField('internal')->getModifiers());
      }),

      it('return PUBLIC STATIC', function() {
        shouldEqual(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_STATIC, fixtureField('EMPTY')->getModifiers());
      }),
    ]),


    // @see https://github.com/facebook/hhvm/issues/1572
    it('Has a cloning implementation', function() {
      shouldEqual(true, method_exists('ReflectionProperty', '__clone'));
    }),
  ]
]);