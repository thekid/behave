<?php namespace test\date_create;

// @see http://de3.php.net/manual/de/datetime.construct.php
// @see http://de3.php.net/manual/de/datetime.formats.php
return new \behaviour\of\TheFunction('date_create', [
  'before' => function() {
    date_default_timezone_set('Europe/Berlin');
  },

  'describe' => [
    it('returns current date per default', function() {
      shouldEqual(time(), date_create()->getTimestamp());
    }),

    it('returns current date when given', [null, 'now'], function($value) {
      shouldEqual(time(), date_create($value)->getTimestamp());
    }),

    it('processes a timestamp', [-1, 0, 1389547377], function($ts) {
      shouldEqual($ts, date_create('@'.$ts)->getTimestamp());
    }),

    it('processes a time string', function() {
      shouldEqual(1389547377, date_create('Sun Jan 12 18:22:57 CET 2014')->getTimestamp());
    }),

    it('handles timezone', function() {
      shouldEqual(0, date_create('01.01.1970 00:00:00', new \DateTimeZone('GMT'))->getTimestamp());
    }),

    it('calculates timezone offset', function() {
      shouldEqual(-3600, date_create('01.01.1970 00:00:00', new \DateTimeZone('Europe/Berlin'))->getTimestamp());
    }),

    it('accepts null as timezone and uses default timezone', function() {
      shouldEqual(-3600, date_create('01.01.1970 00:00:00', null)->getTimestamp());
    }),

    it('returns false for unparseable timestamp', ['@', '@a', '@-'], function($value) {
      shouldEqual(false, date_create($value));
    }),

    it('does not accept timezone strings', function() {
      shouldRaise('/expects parameter 2 to be DateTimeZone, string given/', function() {
        shouldEqual(false, date_create('01.01.1970 00:00:00', 'GMT'));
      });
    }),
  ]
]);