<?php

namespace LaravelDoctrineFiltering\Validation\Rules\Filter;

use Illuminate\Contracts\Validation\ImplicitRule;

/**
 * Class IsValidFilter
 * @package LaravelDoctrineFiltering\Validation\Rules\Filter
 */
class IsValidFilter implements ImplicitRule
{
  /**
   * @var string
   */
  private $entityClass = '';

  /**
   * @var array
   */
  private $filters = [];

  /**
   * @var array
   */
  private $unavailableFields = [];
  private $unavailableJoins = [];
  private $unavailableOperators = [];
  private $unavailableValues = [];
  private $unavailableValuesByType = [];
  private $isValid = true;

  /**
   * IsValidFilter constructor.
   * @param string $entityClass
   * @param array $filters
   */
  public function __construct(string $entityClass, array $filters = [])
  {
    $this->entityClass = $entityClass;
    $this->filters = $filters;
  }

  /**
   * @param string $attribute
   * @param mixed $value
   * @return bool
   */
  public function passes($attribute, $value)
  {
    if (is_empty($this->filters)) {
      return true;
    }

    foreach ($this->filters as $attributeName => $value) {
      if (!is_array($value)) {
        $this->getUnavailableFields($attributeName, $value, $this->entityClass);
        continue;
      }
      $this->getUnavailableRelationships($attributeName, $value, $this->entityClass);
    }
    return $this->isValid;
  }

  /**
   * @return string|null
   */
  public function message()
  {
    $message = null;

    $invalidFields = implode(', ', $this->unavailableFields);
    $invalidJoins = implode(', ', $this->unavailableJoins);
    $invalidOperators = implode(', ', $this->unavailableOperators);

    if (is_not_empty($invalidFields)) {
      $message = is_empty($message)
        ? $message . 'invalid field(s): ' . $invalidFields
        : $message . ' | invalid field(s): ' . $invalidFields;
    }

    if (is_not_empty($invalidJoins)) {
      $message = is_empty($message)
        ? $message . 'invalid join(s): ' . $invalidJoins
        : $message . ' | invalid join(s): ' . $invalidJoins;
    }

    if (is_not_empty($invalidOperators)) {
      $message = is_empty($message)
        ? $message . 'invalid operator(s): ' . $invalidOperators
        : $message . ' | invalid operator(s): ' . $invalidOperators;
    }

    for ($i = 0; $i < count($this->unavailableValues); $i++) {
      $message = is_empty($message)
        ? $message . 'operator ' . "'" . $this->unavailableValues[$i]['operator'] . "'" . ' must receive a valid value, value given: ' . "'" . $this->unavailableValues[$i]['value'] . "'"
        : $message . ' | ' . 'operator ' . "'" . $this->unavailableValues[$i]['operator'] . "'" . ' must receive a valid value, value given: ' . "'" . $this->unavailableValues[$i]['value'] . "'";
    }

    for ($i = 0; $i < count($this->unavailableValuesByType); $i++) {
      $message = is_empty($message)
        ? $message . 'field type ' . "'" . $this->unavailableValuesByType[$i]['fieldType'] . "'" . ' must receive a valid value, value given: ' . "'" . $this->unavailableValuesByType[$i]['value'] . "'"
        : $message . ' | ' . 'field type ' . "'" . $this->unavailableValuesByType[$i]['fieldType'] . "'" . ' must receive a valid value, value given: ' . "'" . $this->unavailableValuesByType[$i]['value'] . "'";
    }

    return $message;
  }

  /**
   * @param $value
   * @return array|string
   */
  protected function prepareColumnName($value)
  {
    if (strpos($value, '.') !== false) {
      $value = explode('.', $value);
    }

    if (is_array($value)) {

      if (strpos($value[count($value) - 1], '|') === false) {
        return $value;
      }

      $value[count($value) - 1] = trim(substr($value[count($value) - 1], 0, strpos($value[count($value) - 1], '|')));

      return $value;
    }

    if (strpos($value, '|') === false) {
      return trim($value);
    }

    return trim(substr($value, 0, strpos($value, '|')));
  }

  /**
   * @param $attributeName
   * @param $value
   * @param $entityClass
   */
  protected function getUnavailableFields($attributeName, $value, $entityClass)
  {
    $columnNameFixed = self::prepareColumnName($attributeName);

    if (!is_array($columnNameFixed)) {
      if (!array_key_exists($columnNameFixed, $entityClass::getAvailableFields())) {
        $this->unavailableFields[] = $columnNameFixed;
        $this->isValid = false;
      }
    } else if (!array_key_exists(array_first($columnNameFixed), $entityClass::getAvailableFields())) {
      $this->unavailableFields[] = array_first($columnNameFixed);
      $this->isValid = false;
    }

    $operator = $this->getUnavailableOperators($attributeName);

    is_array($columnNameFixed)
      ?  $this->getUnavailableValues($value, $operator, $entityClass::getAvailableFields()[array_first($columnNameFixed)])
      :  $this->getUnavailableValues($value, $operator, $entityClass::getAvailableFields()[$columnNameFixed]);

  }

  /**
   * @param $joinName
   * @param $values
   * @param $entityName
   */
  protected function getUnavailableRelationships($joinName, $values, $entityName)
  {
    $entityJoins = $entityName::getJoins();

    if (!array_key_exists($joinName, $entityJoins)) {
      $this->unavailableJoins[] = $joinName;
      $this->isValid = false;
      return;
    }

    foreach ($values as $jn => $v) {
      if (is_array($v)) {
        $this->getUnavailableRelationships($jn, $v, $entityJoins[$joinName]['entity']);
      } else {
        $this->getUnavailableFields($jn, $v, $entityJoins[$joinName]['entity']);
      }
    }
  }

  /**
   * @param $value
   * @return string
   */
  protected function getUnavailableOperators($value)
  {
    if (strpos($value, '|') === false) {
      return '';
    }

    $value = trim(substr($value, strpos($value, '|') + 1));

    switch (strtolower(camel_case($value))) {
      case 'eq':
      case '=':
      case 'gt':
      case '>':
      case 'gte':
      case '>=':
      case 'lt':
      case '<':
      case 'lte':
      case '<=':
      case 'between':
      case 'like':
      case 'in':
      case 'notin':
      case 'isnull':
      case 'isnotnull':
        return strtolower(camel_case($value));

      default:
        $this->unavailableOperators[] = $value;
        $this->isValid = false;
        return '';
    }
  }

  /**
   * @param $value
   * @param $operator
   * @param $field
   */
  protected function getUnavailableValues($value, $operator, $field)
  {
    $valueGiven = $value;

    if (strpos($value, ',') != false) {
      $value = explode(',', $value);
    }

    if (is_empty($operator) && is_array($value)) {
      $operator = 'in';
    }

    if (is_empty($operator) && !is_array($value)) {
      $operator = 'eq';
    }

    switch (strtolower(camel_case($operator))) {
      case 'eq':
      case '=':
      case 'gt':
      case '>':
      case 'gte':
      case '>=':
      case 'lt':
      case '<':
      case 'lte':
      case '<=':
      case 'like':
        if (is_empty($value) || is_array($value)) {
          $this->isValid = false;
          $this->unavailableValues[] = ['operator' => $operator, 'value' => $valueGiven];
        }
        if (in_array(strtolower(camel_case($field['type'])), ['datetime', 'date', 'carbondatetime', 'carbondate']) &&
          !$this->isDate($value)) {
          $this->isValid = false;
          $this->unavailableValuesByType[] = ['fieldType' => $field['type'], 'value' => $valueGiven];
        }
        break;
      case 'between':
      case 'in':
      case 'notin':
        if (is_empty($value) || !is_array($value)) {
          $this->isValid = false;
          $this->unavailableValues[] = ['operator' => $operator, 'value' => $valueGiven];
        }
        if (in_array(strtolower(camel_case($field['type'])), ['datetime', 'date', 'carbondatetime', 'carbondate']) &&
          (!$this->isDate($value[0]) || !$this->isDate($value[1]))) {
          $this->isValid = false;
          $this->unavailableValuesByType[] = ['fieldType' => $field['type'], 'value' => $valueGiven];
        }
        break;
    }
  }

  /**
   * @param $value
   * @return bool
   */
  function isDate($value)
  {
    if (!$value) {
      return false;
    }
    try {
      new \DateTime($value);
      return true;
    } catch (\Exception $e) {
      return false;
    }
  }

}