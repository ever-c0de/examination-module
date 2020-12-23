<?php

namespace Drupal\form_ever\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

/**
 * Simple page controller for drupal.
 */
class Page extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function getModuleName() {
    return 'form_ever';
  }

  public function renderForm() {
    return Drupal::formBuilder()->getForm('Drupal\form_ever\Form\FormEver');
  }

}
