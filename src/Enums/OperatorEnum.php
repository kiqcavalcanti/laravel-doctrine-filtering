<?php

namespace LaravelDoctrineFiltering\Enums;

/**
 * Class OperatorEnum
 * @package LaravelDoctrineFiltering\Enums
 */
class OperatorEnum
{
  const EQ = 'eq';
  const GT = 'gt';
  const GTE = 'gte';
  const LT = 'lt';
  const LTE = 'lte';
  const BETWEEN = 'between';
  const LIKE = 'like';
  const IN = 'in';
  const NOTIN = 'notIn';
  const ISNULL = 'isNull';
  const ISNOTNULL = 'isNotNull';
}