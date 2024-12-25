<?php

namespace Drupal\hellocoop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * For hellocoop configurations.
 */
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
      '#description' => $this->t('Click the button below to generate a new secret key.'),
      '#default_value' => $config->get('secret'),
      '#required' => TRUE,
      '#attributes' => [
        'readonly' => 'readonly',
        'style' => 'background-color: #f9f9f9; color: #6c757d;',
      ],
    ];

    $form['update_secret'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Secret Key'),
      '#submit' => ['::updateSecretKey'],
      '#ajax' => [
        'callback' => '::updateSecretKeyAjaxCallback',
        'wrapper' => 'secret-key-wrapper',
      ],
    ];

    $form['secret']['#prefix'] = '<div id="secret-key-wrapper">';
    $form['secret']['#suffix'] = '</div>';

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

  /**
   * Custom submit handler to update the secret key dynamically.
   */
  public function updateSecretKey(array &$form, FormStateInterface $form_state) {
    $new_secret = bin2hex(random_bytes(32));

    // Update the secret key in configuration.
    $this->config('hellocoop.settings')
      ->set('secret', $new_secret)
      ->save();

    // Store the new secret in the form state for the current rebuild.
    $form_state->setRebuild(TRUE);
    $form_state->set('new_secret', $new_secret); // Temporary storage for rebuild.
  }

  /**
   * Ajax callback to update the secret key field.
   */
  public function updateSecretKeyAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Update the field value dynamically.
    if ($new_secret = $form_state->get('new_secret')) {
      $form['secret']['#value'] = $new_secret; // Update the #value property.
    }

    return $form['secret'];
  }

}
