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

/*  public function renderYears() {

  }
  public function  renderTables() {

  }*/

  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_year = \Drupal::time()->getCurrentTime();
    $current_year = date('Y', $current_year);

    $num_year = $form_state->get('num_year');
    if ($num_year === NULL) {
      $form_state->set('num_year', 0);
      $num_year = 0;
    }
    $num_table = $form_state->get('num_table');
    if ($num_table === NULL) {
      $form_state->set('num_table', 1);
      $num_table = 1;
    }



    $form['#tree'] = TRUE;
    for ($tables = 1; $tables <= $num_table; $tables++) {
/*      $form['fieldset'] = [
        '#type' => 'container',
        '#prefix' => '<div id="table-fieldset-wrapper">',
        '#suffix' => '</div>',
      ];*/
      $form['fieldset'][$tables] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Table № @number',
          ['@number' => $tables]),
        '#prefix' => $this->t('<div id="year-fieldset-wrapper-@tables"><div id="table-fieldset-wrapper">',
          ['@tables' => $tables]),
        '#suffix' => '</div></div>',
      ];
      $form['fieldset'][$tables]['table'] = [
        '#type' => 'table',
        '#header' => ['Year', 'Jan', 'Feb', 'Mar', 'Q1', 'Apr', 'May', 'Jun',
          'Q2', 'Jul', 'Aug', 'Sep', 'Q3', 'Oct', 'Nov', 'Dec', 'Q4', 'YTD',
        ],
      ];
      // get values, array n shift

      for ($i = $num_year; $i >= 0; $i--) {
        // $previous_year = $current_year - $i;
        foreach ($form['fieldset'][$tables]['table']['#header'] as $key) {
          $form['fieldset'][$tables]['table'][$current_year - $i][$key] = [
            '#type' => 'textfield',
            '#size' => '3',
          ];
          $form['fieldset'][$tables]['table'][$current_year - $i]['Year'] = [
            '#plain_text' => $current_year - $i,
          ];
        }
      }
      $form['fieldset'][$tables]['actions'] = [
        '#type' => 'actions',
        '#weight' => -1,
      ];
      /*$form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];*/
      $form['fieldset'][$tables]['actions']['add_year'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Year'),
        '#submit' => ['::addYear'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => $this->t('year-fieldset-wrapper-@tables',
          ['@tables' => $tables]),
          'effect' => 'slide',
          'speed' => 600,
        ],
      ];
    }
    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 1,
    ];
    $form['actions']['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => $this->t('table-fieldset-wrapper-@tables',
        ['@tables' => $tables]),
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

  public function addTable(array &$form, FormStateInterface $form_state) {
    $num_table = $form_state->get('num_table') + 1;
    $form_state->set('num_table', $num_table);
    $form_state->setRebuild();
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage('Nice!');
  }

}
