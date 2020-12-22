<?php

namespace Drupal\form_ever\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\examples\Utility\DescriptionTemplateTrait;

/**
 * Simple page controller for drupal.
 */
class Page extends ControllerBase {

  use DescriptionTemplateTrait;

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
