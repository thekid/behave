<?php namespace behaviour\of {

  abstract class Values {

    public static function areEqual($a, $b) {
      if (is_object($a)) {
        return $a == $b;
      } else if (is_array($a)) {
        if (!is_array($b) || sizeof($a) !== sizeof($b)) return false;
        foreach ($a as $key => $val) {
          if (!array_key_exists($key, $b) || !self::areEqual($val, $b[$key])) return false;
        }
        return true;
      } else {
        return $a === $b;
      }
    }

    public static function areInstances($types, $arg) {
      if (is_array($types)) {
        if (!is_array($arg) || sizeof($types) !== sizeof($arg)) return false;
        foreach ($types as $key => $val) {
          if (!array_key_exists($key, $arg) || !self::areInstances($val, $arg[$key])) return false;
        }
        return true;
      } else {
        return $arg instanceof $types;
      }
    }

    public static function toString($value, $indent= '') {
      if (false === $value) {
        return 'false';
      } else if (true === $value) {
        return 'true';
      } else if (null === $value) {
        return 'null';
      } else if ($value instanceof \Closure) {
        return '<function>';
      } else if (is_array($value)) {
        if (0 === key($value)) {
          $s= '';
          foreach ($value as $v) {
            $s.= ', '.self::toString($v);
          }
          return '['.substr($s, 2).']';
        } else {
          $s= '{';
          foreach ($value as $k => $v) {
            $s.= "\n$indent".self::toString($k, '').' => '.self::toString($v, $indent.'  ');
          }
          return $s.'}';
        }
      } else if (is_object($value)) {
        return get_class($value).'@'.self::toString(get_object_vars($value), '  ');
      } else if (is_string($value)) {
        return '"'.$value.'"';
      } else {
        return (string)$value;
      }
    }
  }

  /**
   * Skip any verification
   */
  class Skip {
    protected $reason, $assertions;

    public function __construct($reason) {
      $this->reason= $reason;
      $this->assertions= function() { return []; };
    }

    public function it() { return $this->assertions; }
    public function its() { return $this->assertions; }
    public function given() { return $this->assertions; }
  }

  /**
   * Assertion failures are reported as this specialized exception
   */
  class AssertionFailed extends \Exception {
    protected $cause;

    public function __construct($message, $cause= null) {
      parent::__construct($message);
      $this->cause= $cause;
    }

    public function __toString() {
      return $this->cause
        ? $this->getMessage().' caused by '.$this->cause
        : parent::__toString()
      ;
    }
  }

  class NotEqual {
    protected $expected, $actual;

    public function __construct($expected, $actual) {
      $this->expected= $expected;
      $this->actual= $actual;
    }

    public function __toString() {
      return (
        "NotEqual()".
        "\n  Expected: ".Values::toString($this->expected, '  ').
        "\n  Actual:   ".Values::toString($this->actual, '  ')
      );
    }
  }

  /**
   * The result wraps around the test and their statuses.
   */
  class Result {
    public $succeeded= [], $failed= [], $elapsed= 0.0;

    public function __toString() {
      $details= ''; 
      foreach ($this->failed as $test => $reason) {
        $details.= "\n\nF <$test>: $reason";
      }
      return sprintf(
        "%s, %d succeeded, %d failed (time taken: %.3f seconds)\033[0m%s",
        $this->failed ? "\033[41;1;37mFAIL" : "\033[42;1;37mOK",
        sizeof($this->succeeded),
        sizeof($this->failed),
        $this->elapsed,
        $details
      );
    }
  }

  /**
   * Abstract base class for behaviours
   */
  abstract class Behaviour {
    protected $name;

    public function __construct($name, $definitions) {
      $this->name= $name;
      $this->definitions= $definitions;
    }

    public function verify($result) {
      $start= microtime(true);
      foreach ($this->definitions['describe'] as $it) {
        isset($this->definitions['before']) && $this->definitions['before']();
        foreach ($it() as $assertion) {
          try {
            $assertion->verify();
            $result->succeeded[$assertion->description]= true;
            echo '.';
          } catch (AssertionFailed $e) {
            $result->failed[$assertion->description]= $e;
            echo 'F';
          } catch (\Exception $e) {
            $result->failed[$assertion->description]= $e;
            echo 'E';
          }
        }
        isset($this->definitions['after']) && $this->definitions['after']();
      }
      $result->elapsed+= microtime(true) - $start;
    }
  }

  /**
   * Function behaviour
   */
  class TheFunction extends Behaviour {

    public function __toString() {
      return $this->name.'()';
    }
  }

  /**
   * Class behaviour
   */
  class TheClass extends Behaviour {

    public function __toString() {
      return 'class '.$this->name;
    }
  }

  /**
   * A single assertion
   */
  class Assertion {
    public $description, $test;
    public $arguments= [];

    public function __construct($description, callable $test) {
      $this->description= $description;
      $this->test= $test;
    }

    public function verify() {
      call_user_func_array($this->test, $this->arguments);
    }
  }

  /**
   * An assertion variant with a given value
   */
  class Variant extends Assertion {
    public $value;

    public function __construct($description, callable $test, $value) {
      parent::__construct($description.'('.var_export($value, 1).')', $test);
      $this->arguments= [$value];
    }
  }
}

namespace {

  function it($description, $arg1= null, $arg2= null) {
    switch (func_num_args()) {
      case 3: return function() use($description, $arg1, $arg2) {
        foreach ($arg1 as $value) {
          yield new \behaviour\of\Variant($description, $arg2, $value);
        }
      };

      case 2: return function() use($description, $arg1) {
        yield new \behaviour\of\Assertion($description, $arg1);
      };

      default: throw new \InvalidArgumentException($description);
    }
  }

  function its($name, $sub) {
    return function() use($name, $sub) {
      foreach ($sub as $it) {
        foreach ($it() as $assertion) {
          $assertion->description= $name.'() '.$assertion->description;
          yield $assertion;
        }
      }
    };
  }

  function given($value, $it) {
    $string= print_r($value, 1);
    return function() use($value, $string, $it) {
      foreach ($it() as $assertion) {
        array_unshift($assertion->arguments, $value);
        $assertion->description= 'given '.$string.', '.$assertion->description;
        yield $assertion;
      }
    };
  }

  function skip($reason) {
    return new \behaviour\of\Skip($reason);
  }

  function shouldEqual($expected, $actual) {
    if (\behaviour\of\Values::areEqual($expected, $actual)) {
      return $actual;
    } else {
      throw new \behaviour\of\AssertionFailed(
        'expected !== actual',
        new \behaviour\of\NotEqual($expected, $actual)
      );
    }
  }

  function shouldBe($expected, $arg) {
    if (\behaviour\of\Values::areInstances($expected, $arg)) {
      return $arg;
    } else {
      $type= is_array($expected) ? '['.implode(', ', $expected).']' : $expected;
      throw new \behaviour\of\AssertionFailed("expected !instanceof $type");
    }
  }

  function shouldRaise($pattern, $closure) {
    $raised= null;
    $restore= set_error_handler(function($code, $message) use(&$raised) { $raised= $message; });
    $report= error_reporting(E_ALL);
    try {
      $closure();
    } finally {
      error_reporting($report);
      set_error_handler($restore);
    }

    if (null == $raised) {
      throw new \behaviour\of\AssertionFailed("$pattern but no warning was raised");
    } else if (!preg_match($pattern, $raised)) {
      throw new \behaviour\of\AssertionFailed("$pattern but '$raised' was raised");
    }
  }

  function shouldThrow($class, $pattern, $closure) {
    try {
      $closure();
      throw new \behaviour\of\AssertionFailed("$class but no exception was thrown");
    } catch (\Exception $e) {
      $message= $e->getMessage();
      if (!($e instanceof $class)) {
        $compound= get_class($e).'<'.$message.'>';
        throw new \behaviour\of\AssertionFailed("$class but $compound was thrown instead");
      } else if (!preg_match($pattern, $message)) {
        throw new \behaviour\of\AssertionFailed("$pattern but the message was '$message'");
      }
    }
  }

  // {{{ main
  $result= new \behaviour\of\Result();
  for ($i= 1, $s= sizeof($argv); $i < $s; $i++) {
    $behaves= require($argv[$i]);
    printf("[%3d%%] Verifying %s: [", $i / $s * 100, $behaves);
    $behaves->verify($result);
    echo "]\n";
    flush();
  }
  printf("[100%%] Finished running %d verifications\n%s", $i - 1, $result);
  // }}}
}