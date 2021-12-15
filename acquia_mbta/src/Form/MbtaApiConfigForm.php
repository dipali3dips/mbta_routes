<?php

namespace Drupal\acquia_mbta\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config Form for Mbta API.
 *
 * @package Drupal\acquia_mbta\Form
 */
class MbtaApiConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'acquia_mbta.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_mbta_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $form = [];
    $form['#cache']['tags'] = $config->getCacheTags();
    $form['mbta_api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mbta API Url'),
      '#default_value' => $config->get('mbta_api'),
    ];
    $form['mbta_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mbta API Key'),
      '#default_value' => $config->get('mbta_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $config->set('mbta_api', $form_state->getValue('mbta_api'));
    $config->set('mbta_key', $form_state->getValue('mbta_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
