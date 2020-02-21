<?php

namespace Drupal\employee_data\Form;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;
use Drupal\employee_data\EmployeeStorage;

/**
 * Employee list in tableselect format.
 */
class EmployeeTableForm implements FormInterface {

    protected $db;
    private $searchKey;

    public function __construct(Connection $con, $search_key = '') {
      $this->db = $con; 
      $this->searchKey = $search_key; 
    }
    
    public static function create(ContainerInterface $container) {
      return new static(
          $container->get('database')
        );
    }     
       
    public function getFormId() {
      return 'employee_table_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
      global $base_url;
      // Table header.
      $header = [
        ['data' => t('ID'), 'field' => 'e.id'],
        'picture' => '',
        ['data' => t('Name'), 'field' => 'e.name'],
        ['data' => t('Email'), 'field' => 'e.email'],
        ['data' => t('State'), 'field' => 'e.state'],
        ['data' => t('District'), 'field' => 'e.district'],
        ['data' => t('Status')],
        'actions' => 'Operations',
      ];
     $query = \Drupal::database()->select('employee', 'e')
     ->fields('e')
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
      $query->orderByHeader($header);
      
      $search_key = $this->searchKey;
      if (!empty($this->searchKey)) {
        $query->condition('e.name', "%" .
        Html::escape($search_key) . "%", 'LIKE');
      }
      $results = $query->execute()
      ->fetchAll();
      $rows = [];
      foreach ($results as $row) {
      $ajax_link_attributes = [
        'attributes' => [
          'class' => 'use-ajax',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => ['width' => 700, 'height' => 400],
           ],
         ];
         $view_url = Url::fromRoute('employee_data.view',
        ['employee' => $row->id, 'js' => 'nojs']);
       $ajax_view_url = Url::fromRoute('employee_data.view',
         ['employee' => $row->id, 'js' => 'ajax'], $ajax_link_attributes);
       $ajax_view_link = Drupal::l($row->name, $ajax_view_url);
      // $view_link = Drupal::l('View', Url::fromRoute('employee_data.view',
      //   ['employee' => $row->id, 'js' => 'nojs']));
      // $mail_url = Url::fromRoute('employee_data.sendmail', ['employee' => $row->id],
      //   $ajax_link_attributes);
      $drop_button = [
        '#type' => 'dropbutton',
        '#links' => [
          'view' => [
            'title' => t('View'),
            'url' => $view_url,
          ],
          // 'edit' => [
          //   'title' => t('Edit'),
          //   'url' => Url::fromRoute('employee.edit', ['employee' => $row->id]),
          // ],
          'delete' => [
            'title' => t('Delete'),
            'url' => Url::fromRoute('employee_data.delete', ['id' => $row->id]),
          ],
          // 'quick_edit' => [
          //   'title' => t('Quick Edit'),
          //   'url' => Url::fromRoute('employee.quickedit', ['employee' => $row->id],
          //     $ajax_link_attributes),
          // ],
          // 'mail' => [
          //   'title' => t('Mail'),
          //   'url' => $mail_url,
          // ],
        ],
      ];
        $profile_pic = File::load($row->profile_pic);
        if ($profile_pic) {
          $style = Drupal::entityTypeManager()->getStorage('image_style')->load('tiny_thumbnail');
          $profile_pic_url = $style->buildUrl($profile_pic->getFileUri());
        }
        else {
          $module_handler = Drupal::service('module_handler');
          $path = $module_handler->getModule('employee_data')->getPath();
          $profile_pic_url = $base_url . '/' . $path . '/assets/profile_placeholder_thumb.png';
        }
        $rows[$row->id] = [
          [sprintf("%04s", $row->id)],
          'picture' => [
            'data' => [
              '#type' => 'html_tag',
              '#tag' => 'img',
              '#attributes' => ['src' => $profile_pic_url],
            ],
          ],
          [$ajax_view_link],
          [$row->email],
          [$row->state],
          [$row->district],
          [($row->status) ? 'Active' : 'Blocked'],
          'actions' => [
            'data' => $drop_button,
          ],
        ];
      }
      $form['action'] = [
        '#type' => 'select',
        '#title' => t('Action'),
        '#options' => [
          'delete' => 'Delete Selected',
          'activate' => 'Activate Selected',
          'block' => 'Block Selected',
        ],
      ];  
        $form['submit'] = [
          '#type' => 'submit',
          '#value' => 'Apply to selected items',
          '#prefix' => '<div class="form-actions js-form-wrapper form-wrapper">',
          '#suffix' => '</div>',
        ];
        $form['table'] = [
          '#type' => 'tableselect',
          '#header' => $header,
          '#options' => $rows,
          '#attributes' => [
            'id' => 'employee-contact-table',
          ],
        ];
      return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {}

    public function submitForm(array &$form, FormStateInterface $form_state) {}
 }