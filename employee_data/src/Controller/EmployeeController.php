<?php

namespace Drupal\employee_data\Controller;

use Drupal\employee_data\Form\EmployeeTableForm;
 use Drupal;
 use Drupal\Core\Url;
 use Symfony\Component\HttpFoundation\RedirectResponse;
// use Drupal\Core\Ajax\AjaxResponse;
 use Drupal\Core\Ajax\OpenModalDialogCommand;
 use Drupal\Core\Form\FormBuilder;
 use Symfony\Component\DependencyInjection\ContainerInterface;
 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Connection;
 use Symfony\Component\HttpFoundation\RequestStack;
 use Drupal\file\Entity\File;

/**
 * Controller class.
 */
class EmployeeController extends ControllerBase {
  
  /**
   * The Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */

    protected $formBuilder;

    /**
     * Databse Connection.
     *
     * @var \Drupal\Core\Database\Connection
     */

    protected $db;

     /**
    * Request.
    *
    * @var Symfony\Component\HttpFoundation\RequestStack
     */
     protected $request;
     
       /**
     * Constructs the EmployeeController.
     *
     * @param \Drupal\Core\Form\FormBuilder $form_builder
     *   The Form builder.
     * @param \Drupal\Core\Database\Connection $con
     *   The database connection.
     * @param \Symfony\Component\HttpFoundation\RequestStack $request
     *   Request stack.
     */
    public function __construct(FormBuilder $form_builder,
      Connection $con,
      RequestStack $request) {
      $this->formBuilder = $form_builder;
      $this->db = $con;
      $this->request = $request;
    }  
        /**
       * {@inheritdoc}
       */
      public static function create(ContainerInterface $container) {
        return new static(
            $container->get('form_builder'),
            $container->get('database'),
            $container->get('request_stack')
          );
      }
    
   public function listEmployees() {
     $content = [];
     $content['search_form'] =
     $this->formBuilder->getForm('Drupal\employee_data\Form\EmployeeSearchForm');
     $search_key = $this->request->getCurrentRequest()->get('search');
     $employee_table_form_instance = new EmployeeTableForm($this->db, $search_key);
     $content['table'] = $this->formBuilder->getForm($employee_table_form_instance);
     $content['pager'] = [
       '#type' => 'pager',
     ];
    $content['#attached'] = ['library' => ['core/drupal.dialog.ajax']];
     return $content;
   }
   public function viewEmployee() {
      global $base_url;
     if ($employee == 'invalid') {
     drupal_set_message(t('Invalid employee record'), 'error');
     return new RedirectResponse(Drupal::url('employee.list'));
     }
     //print_r('hello'); die;
      $rows = [
        [
            $employee->id,
        ],
        [
          ['data' => 'Name', 'header' => TRUE],
          $employee->name,
        ],
        [
          ['data' => 'Email', 'header' => TRUE],
          $employee->email,
        ],
        [
          ['data' => 'Department', 'header' => TRUE],
          $employee->department,
        ],
        [
          ['data' => 'Country', 'header' => TRUE],
          $employee->state,
        ],
        [
          ['data' => 'State', 'header' => TRUE],
          $employee->district,
        ],
        [
          ['data' => 'Address', 'header' => TRUE],
          $employee->address,
        ],
      ];
      $profile_pic = File::load($employee->profile_pic);
      if ($profile_pic) {
        $profile_pic_url = file_create_url($profile_pic->getFileUri());
      }
      else {
        $module_handler = Drupal::service('module_handler');
        $path = $module_handler->getModule('employee_data')->getPath();
        $profile_pic_url = $base_url . '/' . $path . '/assets/profile_placeholder.png';
      }
      $content['image'] = [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => ['src' => $profile_pic_url, 'height' => 400],
      ];
      $content['details'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => ['class' => ['employee-detail']],
      ];
      return $content;
   }
 }