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

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form-ever';
  }

  public function renderYears(&$form, $form_state, $tables, $current_year) {
    $num_year = $form_state->getValues();
    if ($num_year == NULL) {
      $form_state->set("fieldset_$tables", 0);
      $num_year = 0;
    }
    elseif ($form_state->get("fieldset_$tables") === NULL) {
      $num_year = 0;
    }
    else {
      $num_year = $form_state->get("fieldset_$tables");
    }
    for ($i = $num_year; $i >= 0; $i--) {
      foreach ($form['fieldset'][$tables]['table']['#header'] as $key) {
        $form['fieldset'][$tables]['table'][$current_year - $i][$key] = [
          '#type' => 'number',
          '#min' => 0,
          '#step' => 0.01,
          '#size' => 3,
        ];
        $form['fieldset'][$tables]['table'][$current_year - $i]['Year'] = [
          '#plain_text' => $current_year - $i,
        ];
      }
    }
  }

  public function renderTables(&$form, $form_state, $tables) {
    $form['fieldset'][$tables] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Table № @number',
        ['@number' => $tables]),
    ];
    $form['fieldset'][$tables]['table'] = [
      '#type' => 'table',
      '#header' => ['Year', 'Jan', 'Feb', 'Mar', 'Q1', 'Apr', 'May', 'Jun',
        'Q2', 'Jul', 'Aug', 'Sep', 'Q3', 'Oct', 'Nov', 'Dec', 'Q4', 'YTD',
      ],
    ];
    $form['fieldset'][$tables]['actions'] = [
      '#type' => 'actions',
      '#weight' => -1,
    ];
    $form['fieldset'][$tables]['actions']['add_year'] = [
      '#name' => "add_year_$tables",
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => ['::addYear'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => $this->getFormId(),
        'effect' => 'slide',
        'speed' => 600,
      ],
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_year = \Drupal::time()->getCurrentTime();
    $current_year = date('Y', $current_year);

    $num_table = $form_state->get('num_table');
    if ($num_table === NULL) {
      $form_state->set('num_table', 1);
      $num_table = 1;
    }
    $form['#tree'] = TRUE;
    $form['#attributes'] = [
      'id' => $this->getFormId(),
    ];

    for ($tables = 1; $tables <= $num_table; $tables++) {
      $this->renderTables($form, $form_state, $tables);
      $this->renderYears($form, $form_state, $tables, $current_year);

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
          'wrapper' => $this->getFormId(),
          'effect' => 'slide',
          'speed' => 600,
        ],
      ];
    }
    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $num_table = $form_state->get('num_table') + 1;
    $form_state->set('num_table', $num_table);
    $form_state->setRebuild();
  }

  public function addYear(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues()['fieldset'] as $key => $val) {
      if ($a = $form_state->getTriggeringElement()['#parents'][1] == $key) {
        $num_year = $form_state->get("fieldset_$key") + 1;
        $form_state->set("fieldset_$key", $num_year);
        $form_state->setRebuild();
      }
      else {
        $form_state->setRebuild();
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage('Nice!');
  }

}
