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
  }

  /**
   * Assertion failures are reported as this specialized exception
   */
  class AssertionFailed extends \Exception {

    public function __toString() {
      return $this->getMessage()."\n  ".str_replace("\n", "\n  ", $this->getTraceAsString());
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

  function shouldEqual($expected, $actual) {
    if (\behaviour\of\Values::areEqual($expected, $actual)) {
      return $actual;
    } else {
      throw new \behaviour\of\AssertionFailed('expected !== actual');
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