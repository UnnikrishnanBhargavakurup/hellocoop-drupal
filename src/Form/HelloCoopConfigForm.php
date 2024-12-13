<?php

namespace Drupal\hellocoop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class HelloCoopConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hellocoop.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hellocoop_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hellocoop.settings');

    $form['api_route'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Route'),
      '#description' => $this->t('Define the API route for your application. Example: "/api/hellocoop".'),
      '#default_value' => $config->get('api_route') ?? '/api/hellocoop',
      '#required' => TRUE,
    ];

    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#description' => $this->t('Your App ID from https://console.hello.coop/.'),
      '#default_value' => $config->get('app_id'),
      '#required' => TRUE,
    ];

    $form['secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret Key'),
      '#description' => $this->t('Provide a 32-byte hex secret key (use: openssl rand -hex 32).'),
      '#default_value' => $config->get('secret'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('hellocoop.settings')
      ->set('api_route', $form_state->getValue('api_route'))
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('secret', $form_state->getValue('secret'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
