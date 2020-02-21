<?php
namespace Drupal\employee_data\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Database\Connection;

/**
 * Provides a Employee Resource
 *
 * @RestResource(
 *   id = "employee_resource",
 *   label = @Translation("Employee Data"),
 *   uri_paths = {
 *     "canonical" = "/employee_api"
 *   }
 * )
 */
class EmployeeResource extends ResourceBase {

    /**
     * Responds to entity GET requests.
     * @return \Drupal\rest\ResourceResponse
     */
    public function get() {
      
      // if (!(\Drupal::currentUser)->hasPermission('access content')) {
      // throw new AccessDeniedHttpException();
      // }
    $query = \Drupal::database()->select('employee', 'e')
    ->fields('e');
    $results = $query->execute()->fetchAll(); 
    foreach($results as $key => $value) {
     $data[] = [
       'id' => $value->id,
       'name' => $value->name,
       'email' => $value->email,
       'department' => $value->department,
       'address' => $value->address,
       'state' => $value->state,
       'district' => $value->district,
       'status' => $value->status
     //'profile_pic' => $value->profile_pic     
     ];
    }
    return new ResourceResponse($data);
  }
}