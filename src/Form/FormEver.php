<?php

namespace Drupal\form_ever\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class FormEver extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form['table'] = [
      '#type' => 'table',
      '#header' => ['Year', 'Jan', 'Feb', 'Mar', 'Q1', 'Apr', 'May', 'Jun', 'Q2',
        'Jul', 'Aug', 'Sep', 'Q3', 'Oct', 'Nov', 'Dec', 'Q4', 'YTD',
      ],

    ];
    $form['table']['first_row']['year'] = [
      'default' => [
        'year' => [
          '#plain_text' => 2020,
        ],
      ],
    ];
    foreach ($form['table']['#header'] as $key) {
      $form['table']['first_row'][$key] = [
        '#type' => 'decimal',
      ];
    }
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'form_ever';
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }


}
