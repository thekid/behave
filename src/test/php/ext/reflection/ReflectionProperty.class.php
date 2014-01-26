<?php namespace test\php\ext\reflection\ReflectionProperty;

class Base {
  protected $inherited;
  public static $EMPTY = null;
}
class Fixture extends Base {
  public $field;
  private $internal;
  /** Documented */
  private static $documented;
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
      it('returns the declaring class', ['field', 'internal'], function($prop) {
        shouldEqual(Fixture::class, fixtureField($prop)->getDeclaringClass()->name);
      }),

      it('returns the declaring class for inherited properties', ['inherited', 'EMPTY'], function($prop) {
        shouldEqual(Base::class, fixtureField($prop)->getDeclaringClass()->name);
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

    // @see http://de3.php.net/manual/de/reflectionproperty.ispublic.php
    its('isPublic', [
      it('will return true for public', function() {
        shouldEqual(true, fixtureField('field')->isPublic());
      }),

      it('will return false for protected', function() {
        shouldEqual(false, fixtureField('inherited')->isPublic());
      }),

      it('will return false for private', function() {
        shouldEqual(false, fixtureField('internal')->isPublic());
      }),

      it('will return true for public static', function() {
        shouldEqual(true, fixtureField('EMPTY')->isPublic());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.isprotected.php
    its('isProtected', [
      it('will return false for public', function() {
        shouldEqual(false, fixtureField('field')->isProtected());
      }),

      it('will return true for protected', function() {
        shouldEqual(true, fixtureField('inherited')->isProtected());
      }),

      it('will return false for private', function() {
        shouldEqual(false, fixtureField('internal')->isProtected());
      }),

      it('will return false for public static', function() {
        shouldEqual(false, fixtureField('EMPTY')->isProtected());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.isprivate.php
    its('isPrivate', [
      it('will return false for public', function() {
        shouldEqual(false, fixtureField('field')->isPrivate());
      }),

      it('will return false for protected', function() {
        shouldEqual(false, fixtureField('inherited')->isPrivate());
      }),

      it('will return true for private', function() {
        shouldEqual(true, fixtureField('internal')->isPrivate());
      }),

      it('will return false for public static', function() {
        shouldEqual(false, fixtureField('EMPTY')->isPrivate());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.isstatic.php
    its('isStatic', [
      it('will return false for public', function() {
        shouldEqual(false, fixtureField('field')->isStatic());
      }),

      it('will return false for protected', function() {
        shouldEqual(false, fixtureField('inherited')->isStatic());
      }),

      it('will return false for private', function() {
        shouldEqual(false, fixtureField('internal')->isStatic());
      }),

      it('will return true for public static', function() {
        shouldEqual(true, fixtureField('EMPTY')->isStatic());
      }),
    ]),

    // @see http://de3.php.net/manual/de/reflectionproperty.getdoccomment.php
    its('getDocComment', [
      it('returns false for fields without doc comment', function() {
        shouldEqual(false, fixtureField('field')->getDocComment());
      }),

      it('returns the complete comment', function() {
        shouldEqual('/** Documented */', fixtureField('documented')->getDocComment());
      }),
    ]),

    // @see https://github.com/facebook/hhvm/issues/1572
    it('Has a cloning implementation', function() {
      shouldEqual(true, method_exists('ReflectionProperty', '__clone'));
    }),

    its('string casting', [
      it('public form', function() {
        shouldEqual(
          "Property [ <default> public \$field ]\n",
          (string)fixtureField('field')
        );
      }),

      it('protected form', function() {
        shouldEqual(
          "Property [ <default> protected \$inherited ]\n",
          (string)fixtureField('inherited')
        );
      }),

      it('private form', function() {
        shouldEqual(
          "Property [ <default> private \$internal ]\n",
          (string)fixtureField('internal')
        );
      }),

      it('public static form', function() {
        shouldEqual(
          "Property [ public static \$EMPTY ]\n",
          (string)fixtureField('EMPTY')
        );
      }),

      it('will NOT include api documentation', function() {
        shouldEqual(
          "Property [ private static \$documented ]\n",
          (string)fixtureField('documented')
        );
      }),
    ])
  ]
]);