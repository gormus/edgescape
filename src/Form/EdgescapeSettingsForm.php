<?php

namespace Drupal\edgescape\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Akamai EdgeScape settings form.
 */
class EdgescapeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['edgescape.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edgescape_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('edgescape.settings');
    $form['header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#size' => 100,
      '#default_value' => $config->get('header'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->configFactory->getEditable('edgescape.settings');
    $config
      ->set('header', $form_state->getValue('header'))
      ->save();
  }

}
