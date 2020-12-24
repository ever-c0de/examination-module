<?php

namespace Drupal\form_ever\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the ajax demo form controller.
 *
 * This example demonstrates using ajax callbacks to add people's names to a
 * list of picnic attendees.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class FormEver extends FormBase {
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_year = \Drupal::time()->getCurrentTime();
    $current_year = date('Y', $current_year);

    $num_year = $form_state->get('num_year');
    if ($num_year === NULL) {
      $form_state->set('num_year', 0);
      $num_year = 0;
    }

    $form['#tree'] = TRUE;
    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Table №1'),
      '#prefix' => '<div id="year-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['fieldset']['table'] = [
      '#type' => 'table',
      '#header' => ['Year', 'Jan', 'Feb', 'Mar', 'Q1', 'Apr', 'May', 'Jun', 'Q2',
        'Jul', 'Aug', 'Sep', 'Q3', 'Oct', 'Nov', 'Dec', 'Q4', 'YTD',
      ],
    ];
    /*foreach ($form['table']['#header'] as $key) {
      $form['table'][$year][$key] = [
        '#type' => 'textfield',
        '#size' => '5',
      ];
      $form['table'][$year]['Year'] = [
        '#plain_text' => 2020,
      ];
    }*/
// get values, array n shift

    for ($i = $num_year; $i >= 0 ; $i--) {
//      $previous_year = $current_year - $i;
      foreach ($form['fieldset']['table']['#header'] as $key) {
        $form['fieldset']['table'][$current_year-$i][$key] = [
          '#type' => 'textfield',
          '#size' => '3',
        ];
        $form['fieldset']['table'][$current_year-$i]['Year'] = [
          '#plain_text' => $current_year-$i,
        ];
      }
    }
    $form['fieldset']['actions'] = [
      '#type' => 'actions',
      '#weight' => -100,
    ];
    /*$form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];*/
    $form['fieldset']['actions']['add_year'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => ['::addYear'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'year-fieldset-wrapper',
        'effect' => 'slide',
      ],
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
    return $form['fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addYear(array &$form, FormStateInterface $form_state) {
    $num_year = $form_state->get('num_year') + 1;
    $form_state->set('num_year', $num_year);
    $form_state->setRebuild();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage('Nice!');
  }

}
