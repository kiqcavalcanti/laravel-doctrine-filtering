<?php

namespace LaravelDoctrineFiltering\Entities;

use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Class FilterEntity
 * @package LaravelDoctrineFiltering\Entities
 */
class FilterEntity
{
  /**
   * @var array
   */
  protected static $availableFields = ['*'];

  /**
   * @var array
   */
  protected static $entityJoins = [];

  public static function getEntityAlias()
  {
    if(property_exists(get_called_class(), 'entityAlias')) {
      return get_called_class()::$entityAlias;
    }

    return snake_case(array_last(explode('\\',get_called_class())));
  }


  /**
   * @return array
   */
  public static function getEntityJoins()
  {
    $entityName = get_called_class();

    $joins = [];

    foreach ($entityName::$entityJoins as $key => $join) {
      $entity = $join['entity'];
      $joins[$key]['entity'] = $join['entity'];
      $joins[$key]['condition'] = $join['condition'];
      $joins[$key]['available_fields'] = $entity::getAvailableFields();
    }

    return $joins;
  }

  /**
   * @return array
   */
  public static function getAvailableFields()
  {
    $fields = EntityManager::getClassMetadata(get_called_class())->fieldMappings;

    $availableFields = [];

    foreach ($fields as $key => $field) {
      if(in_array($field['columnName'], self::$availableFields) || in_array('*', self::$availableFields)) {
        $availableFields[$field['columnName']] = ['type' => $field['type'], 'fieldName' => $field['fieldName']];
      }
    }
    return $availableFields;
  }
}