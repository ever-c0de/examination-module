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
    // Attach style and js files.
    $form['#attached'] = ['library' => ['form_ever/form']];
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
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => $this->t('Submit'),
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

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Start validation only if submit button triggered.
    if ($form_state->getTriggeringElement()['#name'] == 'submit') {
      // Get array of the input values.
      $tables = $form_state->getValue('fieldset');
      // Variables to store the common not empty period of values
      // what should be the same for all tables.
      $period_start = NULL;
      $period_end = NULL;

      // Walk across the tables.
      foreach ($tables as $table_num => $table) {
        // Variables to store period inside one table.
        $start = NULL;
        $end = NULL;
        // Indicates that period is completed.
        $completed = FALSE;

        // Walk across the years (table rows).
        foreach ($table['table'] as $year => $months) {
          // Walk across the months cells only not summaries and year.
          for ($i = 1; $i <= 12; $i++) {
            // If cell is not empty...
            if ($months[$i] !== '') {
              // Check if period had been completed.
              if ($completed) {
                // If so, we have interrupted period. So set an error.
                // These setting necessary to avoid empty form error
                // cause all of the loops will be broken and it will not be set.
                $period_start = $start;
                $period_end = $end;
                $form_state->setError($form['fieldset'][$table_num]['table'][$year][$i], 'Invalid!');
                break(3);
              }
              // If period was not completed.
              else {
                // Set start and end if it does not exist.
                if (!$start) {
                  $start = mktime(0, 0, 0, $i, 1, $year);
                  $end = $start;
                }
                // Else just set end of the period at current month.
                else {
                  $end = mktime(0, 0, 0, $i, 1, $year);
                }
              }
            }
            // If cell is empty...
            else {
              // If end of the period is set, we have end of
              // uninterrupted period, so set the completed flag.
              if ($end) {
                $completed = TRUE;
              }
            }
          }
        }
        // If it isn't the first table we walking at and the common period is
        // set compare it with current table and set error if it isn't the same.
        if ($period_start && $period_end) {
          if (($period_start !== $start) || ($period_end !== $end)) {
            $form_state->setError($form['fieldset'][$table_num], 'Invalid!');
            break;
          }
        }
        // Else set the first found period as common to compare with.
        else {
          $period_start = $start;
          $period_end = $end;
        }
      }
      // If at the end we found nothing form is empty.
      if (!$period_start && !$period_end) {
        $form_state->setError($form, 'Form is empty!');
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage('Valid!');
  }

}
