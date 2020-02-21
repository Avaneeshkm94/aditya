<?php

namespace Drupal\employee_data\Form;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Database\Database;
use Drupal\employee_data\EmployeeStorage;
/**
 * Employee Form.
 */
class EmployeeForm implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_add';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $employee = NULL) {
      if ($employee) {
        if ($employee == 'invalid') {
          drupal_set_message(t('Invalid employee record'), 'error');
          return new RedirectResponse(Drupal::url('employee.list'));
        }
      $form['eid'] = [
        '#type' => 'hidden',
        '#value' => $employee->id,
      ];
    }
      $form['general'] = [
      '#type' => 'details',
      "#title" => "General Details",
      '#open' => TRUE,
      ];
      $form['general']['name'] = [
        '#type' => 'textfield',
        '#title' => t('Name'),
        '#required' => TRUE,
        '#default_value' => ($employee) ? $employee->name : '',
      ];
      $form['general']['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
      '#default_value' => ($employee) ? $employee->email : '',
    ];

    $form['general']['department'] = [
      '#type' => 'select',
      '#title' => t('Department'),
      '#options' => [
        '' => 'Select Department',
        'Development' => 'Development',
        'HR' => 'HR',
        'Sales' => 'Sales',
        'Marketing' => 'Marketing',
      ],
      '#required' => TRUE,
      '#default_value' => ($employee) ? $employee->department : '',
    ];

    $form['general']['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Active?'),
      '#default_value' => ($employee) ? $employee->status : 1,
    ];
    $form['address_details'] = [
    '#type' => 'details',
    "#title" => "Address Details",
    '#open' => TRUE,
    ];
    $form['address_details']['address'] = [
      '#type' => 'textarea',
      '#title' => t('Address'),
      '#required' => TRUE,
      '#default_value' => ($employee) ? $employee->address : '',
    ];
    $form['address_details']['state'] = [
    '#type' => 'select',
    '#title' => t('State'),
    '#options' => $this->getStates(),
    '#required' => TRUE,
    '#default_value' => ($employee) ? $employee->state : '',
    '#ajax' => [
      'callback' => [$this, 'loadDistricts'],
      'event' => 'change',
      'wrapper' => 'districts',
      ],
    ];
    $changed_state = $form_state->getValue('state');
    if ($employee) {
      if (!empty($changed_state)) {
      $selected_state = $changed_state;
      }
      else {
        $selected_state = $employee->state;
        }
      }
    else {
        $selected_state = $changed_state;
    }
  $districts = $this->getDistricts($selected_state);
  $form['address_details']['district'] = [
    '#type' => 'select',
    '#prefix' => '<div id="districts">',
    '#title' => t('District'),
    '#options' => $districts,
    '#required' => TRUE,
    '#suffix' => '</div>',
    '#default_value' => ($employee) ? $employee->district : '',
    '#validated' => TRUE,
  ];
    $form['upload'] = [
      '#type' => 'details',
      "#title" => "Profile Pic",
      '#open' => TRUE,
    ];
    $form['upload']['profile_pic'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://employee_images/',
      '#multiple' => FALSE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg jfif'],
        'file_validate_size' => [25600000],
        // 'file_validate_image_resolution' => array('800x600', '400x300'),.
      ],
      '#title' => t('Upload a Profile Picture'),
      '#default_value' => ($employee) ? [$employee->profile_pic] : '',
    ];

  $form['actions'] = ['#type' => 'actions'];
  $form['actions']['submit'] = [
  '#type' => 'submit',
  '#value' => 'Save',
  ];
  $form['actions']['cancel'] = [
    '#type' => 'link',
    '#title' => 'Cancel',
    '#attributes' => ['class' => ['button', 'button--primary']],
    //'#url' => Url::fromRoute('employee.list'),
  ];
      return $form;
  }

  /**
* {@inheritdoc}
*/
      public function getStates() {
        return [
          '' => 'Select State',
          'AP' => 'Andhra Pradesh',
          'AR' => 'Arunachal Pradesh',
          'AS' => 'Assam',
          'BR' => 'Bihar',
          'CT' => 'Chhattisgarh',
          'GA' => 'Goa',
          'GJ' => 'Gujarat',
          'HR' => 'Haryana',
          'HP' => 'Himachal Pradesh',
          'JK' => 'Jammu and Kashmir',
          'JH' => 'Jharkhand',
          'KA' => 'Karnataka',
          'KL' => 'Kerala',
          'MP' => 'Madhya Pradesh',
          'MH' => 'Maharashtra',
          'MN' => 'Manipur',
          'ML' => 'Meghalaya',
          'MZ' => 'Mizoram',
          'NL' => 'Nagaland',
          'OR' => 'Odisha',
          'PB' => 'Punjab',
          'RJ' => 'Rajasthan',
          'SK' => 'Sikkim',
          'TN' => 'Tamil Nadu',
          'TG' => 'Telangana',
          'TR' => 'Tripura',
          'UT' => 'Uttarakhand',
          'UP' => 'Uttar Pradesh',
          'WB' => 'West Bengal',
          'AN' => 'Andaman and Nicobar Islands',
          'CH' => 'Chandigarh',
          'DN' => 'Dadra and Nagar Haveli',
          'DD' => 'Daman and Diu',
          'DL' => 'Delhi',
          'LD' => 'Lakshadweep',
          'PY' => 'Puducherry',
        ];
      }
      /**
 * {@inheritdoc}
 */
public function getDistricts($selected_state) {
  $districts = [
    'UP' => [
      '' => 'Select District',
      'Agra' => 'Agra',
      'Aligarh' => 'Aligarh',
      'Allahabad' => 'Allahabad',
      'Ambedkar Nagar' => 'Ambedkar Nagar',
    ],
    'MP' => [
      '' => 'Select District',
      'Anuppur' => 'Anuppur',
      'Ashoknagar' => 'Ashoknagar',
    ],
    'MH' => [
      '' => 'Select District',
      'Ahmednagar' => 'Ahmednagar',
      'Akola' => 'Akola',
      'Amravati' => 'Amravati',
    ],
   ];
  return ($districts[$selected_state]) ? $districts[$selected_state] : ['' => 'None'];
 }
/**
 * {@inheritdoc}
 */
    public function loadDistricts(array &$form, FormStateInterface $form_state) {
      $form_state->setRebuild(TRUE);
      return $form['address_details']['district'];
    }
    public function validateForm(array &$form, FormStateInterface $form_state) {
    }
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $id = $form_state->getValue('eid');
      $file_usage = Drupal::service('file.usage');
      $profile_pic_fid = NULL;
      $image = $form_state->getValue('profile_pic');
      if (!empty($image)) {
        $profile_pic_fid = $image[0];
      }
      $fields = [
        'name' => SafeMarkup::checkPlain($form_state->getValue('name')),
        'email' => SafeMarkup::checkPlain($form_state->getValue('email')),
        'department' => $form_state->getValue('department'),
        'state' => $form_state->getValue('state'),
        'district' => $form_state->getValue('district'),
        'address' => SafeMarkup::checkPlain($form_state->getValue('address')),
        'status' => $form_state->getValue('status'),
        'profile_pic' => $profile_pic_fid,
      ];
      //return \Drupal::database()->insert('employee')->fields($fields)->execute();
      //print_r($fields); die;
           //  if (!empty($id) && EmployeeStorage::exists($id)) {
           //    $employee = EmployeeStorage::load($id);
           //    if ($profile_pic_fid) {
           //      if ($profile_pic_fid !== $employee->profile_pic) {
           //        file_delete($employee->profile_pic);
           //        $file = File::load($profile_pic_fid);
           //        $file->setPermanent();
           //        $file->save();
           //        $file_usage->add($file, 'employee', 'file', $id);
           //      }
           //   }
           //  else {
           //    file_delete($employee->profile_pic);
           //  }
           //  EmployeeStorage::update($id, $fields);
           //  $message = 'Employee updated sucessfully';
           // }
           $new_employee_id = EmployeeStorage::add($fields);
            if ($profile_pic_fid) {
              $file = File::load($profile_pic_fid);
              $file->setPermanent();
              $file->save();
              $file_usage->add($file, 'employee', 'file', $new_employee_id);
            }
            // $this->dispatchEmployeeWelcomeMailEvent($new_employee_id);
            $message = 'Employee created sucessfully';
            drupal_set_message($message);
  }
}
