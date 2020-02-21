<?php

namespace Drupal\employee_data;

/**
 * DAO class for employee table.
 */
class EmployeeStorage {  

    public static function add(array $fields) {
      return \Drupal::database()->insert('employee')->fields($fields)->execute();
    }
    
    public static function exists($id) {
      $result = \Drupal::database()->select('employee', 'e')
      ->fields('e', ['id'])
      ->condition('id', $id, '=')
      ->execute()
      ->fetchField();
      return (bool) $result;
    }
    
    public static function load($id) {
    $result = \Drupal::database()->select('employee', 'e')
    ->fields('e')
    ->condition('id', $id, '=')
    ->execute()
    ->fetchObject();
    return $result;
    }
    
    public static function delete($id) {
    $record = self::load($id);
    if ($record->profile_pic) {
      file_delete($record->profile_pic);
    }
    return \Drupal::database()->delete('employee')->condition('id', $id)->execute();
    }
    
  //   public static function getAll($limit = NULL, $orderBy = NULL, $order = 'DESC') {
  //   $query = \Drupal::database()->select('employee', 'e')
  //     ->fields('e');
  //   if ($limit) {
  //     $query->range(0, $limit);
  //   }
  //   if ($orderBy) {
  //     $query->orderBy($orderBy, $order);
  //   }
  //   $result = $query->execute()
  //     ->fetchAll();
  //   return $result;
  // }
}  