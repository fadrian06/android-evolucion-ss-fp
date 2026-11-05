<?php

declare(strict_types=1);

use Leaf\Form;

return static function (): Form {
  $form = new Form;

  $form->rule('password', '/^.+$/', '{Field} must be a valid password');

  $form->rule(
    'min',
    static function (mixed $value, string $arg) use ($form): bool {
      if (is_numeric($value) && is_numeric($arg)) {
        $form->message('min', '{Field} must be at least %s');

        return $value >= $arg;
      }

      if (is_string($value)) {
        $form->message('min', '{Field} must be at least %s characters long');

        return strlen($value) >= $arg;
      }

      return false;
    },
  );

  $form->rule(
    'max',
    static function (mixed $value, string $arg) use ($form): bool {
      if (is_numeric($value) && is_numeric($arg)) {
        $form->message('max', '{Field} must not exceed %s');

        return $value <= $arg;
      }

      if (is_string($value)) {
        $form->message('max', '{Field} must not exceed %s characters');

        return strlen($value) <= $arg;
      }

      return false;
    },
  );

  $form->rule(
    'between',
    static function (mixed $value, string $args) use ($form): bool {
      [$min, $max] = explode(',', $args);

      if (is_numeric($value)) {
        $form->message('between', '{Field} must be between %s and %s');

        return $value >= $min && $value <= $max;
      }

      if (is_string($value)) {
        $form->message(
          'between',
          '{Field} must be between %s and %s characters long',
        );

        $valueLength = strlen($value);

        return $valueLength >= $min && $valueLength <= $max;
      }

      return false;
    },
  );

  $form->rule(
    'array',
    static function (
      mixed $value,
      array|string $args,
      string $field,
      array $data,
    ) use ($form): bool {
      if (!is_array($value)) {
        return false;
      }

      if (!$args || !is_string($args)) {
        return true;
      }

      $args = explode(',', $args);
      $argsLength = count($args);
      $keysRules = null;
      $valuesRules = null;

      if ($argsLength === 1) {
        $valuesRules = $args[0];
      } elseif ($argsLength === 2) {
        $keysRules = $args[0];
        $valuesRules = $args[1];
      } else {
        throw new InvalidArgumentException(
          'The "array" rule accepts at most two arguments',
        );
      }

      $test = new ReflectionMethod($form, 'test');

      foreach ($value as $keyToTest => $valueToTest) {
        if (
          $keysRules
          && !$test->invoke($form, $keysRules, $keyToTest, $field, $data)
        ) {
          return true;
        }

        if (!$test->invoke($form, $valuesRules, $valueToTest, $field, $data)) {
          return true;
        }
      }

      return true;
    },
    '{field} must be an array',
  );

  return $form;
};
