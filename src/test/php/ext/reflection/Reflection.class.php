<?php namespace test\php\ext\reflection\Reflection;

// @see http://de3.php.net/manual/de/class.reflection.php
return new \behaviour\of\TheClass('Reflection', [

  'describe' => [
    its('export', [
      it('return whatever the given reflector\'s export returns', function() {
        shouldEqual(
          \ReflectionClass::export('stdClass', true),
          \Reflection::export(new \ReflectionClass('stdClass'), true)
        );
      })
    ]),

    its('getModifierNames', [
      it('should return public', function() {
        shouldEqual(['public'], \Reflection::getModifierNames(\ReflectionMethod::IS_PUBLIC));
      }),

      it('should return private', function() {
        shouldEqual(['private'], \Reflection::getModifierNames(\ReflectionMethod::IS_PRIVATE));
      }),

      it('should return protected', function() {
        shouldEqual(['protected'], \Reflection::getModifierNames(\ReflectionMethod::IS_PROTECTED));
      }),

      it('should return static', function() {
        shouldEqual(['static'], \Reflection::getModifierNames(\ReflectionMethod::IS_STATIC));
      }),

      it('should return public static', function() {
        shouldEqual(['public', 'static'], \Reflection::getModifierNames(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC));
      }),

      it('should return final', function() {
        shouldEqual(['final'], \Reflection::getModifierNames(\ReflectionMethod::IS_FINAL));
      }),

      it('should return final public', function() {
        shouldEqual(['final', 'public'], \Reflection::getModifierNames(\ReflectionMethod::IS_FINAL | \ReflectionMethod::IS_PUBLIC));
      }),

      it('should return abstract', function() {
        shouldEqual(['abstract'], \Reflection::getModifierNames(\ReflectionMethod::IS_ABSTRACT));
      }),

      it('should return abstract public', function() {
        shouldEqual(['abstract', 'public'], \Reflection::getModifierNames(\ReflectionMethod::IS_ABSTRACT | \ReflectionMethod::IS_PUBLIC));
      }),
    ]),
  ]
]);