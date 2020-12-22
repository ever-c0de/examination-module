<?php

namespace Drupal\form_ever\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class FormEver extends FormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $num_year = $form_state->get('num_year');
    if ($num_year === NULL) {
      $year_field = $form_state->set('$num_year', 1);
      $num_year = 1;
    }


    $form['#tree'] = TRUE;
    $form['table'] = [
      '#type' => 'table',
      '#header' => ['Year', 'Jan', 'Feb', 'Mar', 'Q1', 'Apr', 'May', 'Jun', 'Q2',
        'Jul', 'Aug', 'Sep', 'Q3', 'Oct', 'Nov', 'Dec', 'Q4', 'YTD',
      ],
    ];
    foreach ($form['table']['#header'] as $key) {
      $form['table']['first_row'][$key] = [
        '#type' => 'textfield',
        '#size' => '5',
      ];
      $form['table']['first_row']['Year'] = [
        '#plain_text' => 2020,
      ];
    }

    for ($i = 1; $i < $num_year; $i++) {
      foreach ($form['table']['#header'] as $key) {
        $form['table'][$i][$key] = [
          '#type' => 'textfield',
          '#size' => '5',
        ];
      }
    }
    $form['table']['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['add_year'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => ['::addYear'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
      ],
    ];
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

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['table'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addYear(array &$form, FormStateInterface $form_state) {
    $year_field = $form_state->get('num_year');
    $add_button = $year_field + 1;
    $form_state->set('num_year', $add_button);
    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }


}
