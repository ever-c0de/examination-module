<?php

namespace Drupal\form_ever\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the form that calculate quartals and YTD values.
 *
 * Its have functionality for adding more than 1 table and years to each table.
 */
class FormEver extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form-ever';
  }

  /**
   * Method for rendering years for each table.
   *
   * @param array $form
   *   Gets the form.
   * @param array $form_state
   *   Gets the form state for set and get values.
   * @param int $tables
   *   Count of tables in the form.
   * @param int $current_year
   *   Current year set by Drupal.
   *
   * @return array
   *   An array with year fields.
   */
  public function renderYears(array &$form, $form_state, int $tables, int $current_year): array {
    $rows = ['Year', '1', '2', '3', 'Q1', '4', '5', '6',
      'Q2', '7', '8', '9', 'Q3', '10', '11', '12', 'Q4', 'YTD',
    ];
    $years = [];
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
      foreach ($rows as $row) {
        $year_value = $current_year - $i;
        $years = $form['fieldset'][$tables]['table'][$year_value][$row] = [
          '#type' => 'number',
          '#min' => 0,
          '#step' => 0.01,
        ];
        $form['fieldset'][$tables]['table'][$year_value]['Year'] = [
          '#plain_text' => $current_year - $i,
        ];
      }
    }
    return $years;
  }

  /**
   * Render tables in fieldset for the form.
   *
   * @param array $form
   *   Gets the form.
   * @param array $form_state
   *   Gets the form state.
   * @param int $tables
   *   A number of tables in fieldset.
   *
   * @return array
   *   Return an array of tables.
   */
  public function renderTables(array &$form, $form_state, int $tables) {
    $tables_return = [];
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
    return $tables_return;
  }

  /**
   * {@inheritdoc}
   */
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
   *
   * @param array $form
   *   Gets the form.
   * @param $form_state
   *   Gets the form state.
   *
   * @return array
   *   Return ready form.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Submit handler for the "Add table" button.
   *
   * Increments tables value to render more tables.
   *
   * @param array $form
   *   Gets the form.
   * @param $form_state
   *   Use form state for get the number of tables and set it.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $num_table = $form_state->get('num_table') + 1;
    $form_state->set('num_table', $num_table);
    $form_state->setRebuild();
  }

  /**
   * Submit handler for the "Add year" button in each fieldset .
   *
   * Gets the right fieldset and increase value for year value.
   *
   * @param array $form
   *   Gets the form.
   * @param $form_state
   *   Use form state to find triggered button and set new value to num_year.
   */
  public function addYear(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues()['fieldset'] as $key => $val) {
      if ($form_state->getTriggeringElement()['#parents'][1] == $key) {
        $num_year = $form_state->get("fieldset_$key") + 1;
        $form_state->set("fieldset_$key", $num_year);
        $form_state->setRebuild();
      }
      else {
        $form_state->setRebuild();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage('Form is valid.');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'submit') {
      // Get the array of 'fieldset' values.
      $tables = $form_state->getValue('fieldset');
      // Storage values for not empty period.
      $period_start = NULL;
      $period_end = NULL;

      foreach ($tables as $table_num => $table) {
        // Variables for each table.
        $start_value = NULL;
        $end_value = NULL;
        // Variable for table is ready.
        $finish = FALSE;

        // Check each year in table.
        foreach ($table['table'] as $year => $months) {
          // Check each month in year.
          for ($i = 1; $i < 13; $i++) {
            if ($months[$i] !== '') {
              if ($finish) {
                // That means period is broken.
                $period_start = $start_value;
                $period_end = $end_value;
                $form_state->setError($form['fieldset'][$table_num]['table'][$year][$i], 'Form is not Valid.');
                break(3);
              }
              // If period was not completed.
              else {
                // Set start and end if it does not exist.
                if (!$start_value) {
                  $start_value = mktime(0, 0, 0, $i, 1, $year);
                  $end_value = $start_value;
                }
                // Else just set end of the period at current month.
                else {
                  $end_value = mktime(0, 0, 0, $i, 1, $year);
                }
              }
            }
            else {
              // If end of the period is set, we have end of
              // uninterrupted period, so set the completed flag.
              if ($end_value) {
                $finish = TRUE;
              }
            }
          }
        }
        if ($period_start && $period_end) {
          if (($period_start !== $start_value) || ($period_end !== $end_value)) {
            $form_state->setError($form['fieldset'][$table_num], 'Form is not Valid.');
            break;
          }
        }
        // Else set the first found period as common to compare with.
        else {
          $period_start = $start_value;
          $period_end = $end_value;
        }
      }
      if (!$period_start && !$period_end) {
        $form_state->setError($form, 'Form is empty.');
      }
    }
  }

}
