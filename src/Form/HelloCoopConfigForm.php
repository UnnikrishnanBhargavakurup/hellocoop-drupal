<?php

namespace Drupal\hellocoop\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

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

    // Check if the 'client_id' parameter is present in the URL.
    $request = \Drupal::request();
    $client_id = $request->query->get('client_id');

    if (!empty($client_id)) {
      // Update the App ID in configuration.
      $config->set('app_id', $client_id)->save();
    }

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

    $form['generate_app_id'] = [
      '#type' => 'markup',
      '#markup' => '<a href="' . $this->generateQuickstartUrl() . '" class="button">' . $this->t('Generate App ID') . '</a>',
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
   * Generates the Quickstart URL for App ID creation.
   */
  private function generateQuickstartUrl(): string {
    $response_uri = Url::fromRoute(
      'hellocoop_config_form',
      [],
      ['absolute' => TRUE]
    );

    $logo_path = theme_get_setting('logo');
    $logo_url = '';

    if (!empty($logo_path['url'])) {
      // Check if the logo URL is already absolute.
      if (UrlHelper::isExternal($logo_path['url'])) {
        $logo_url = $logo_path['url'];
      }
      else {
        // Convert the relative URL to an absolute one.
        $logo_url = \Drupal::request()->getSchemeAndHttpHost() . $logo_path['url'];
      }
    }

    return 'https://quickstart.hello.coop/?response_uri=' . urlencode($response_uri->toString()) . '&image_uri=' . urlencode($logo_url);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
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
  public function updateSecretKey(array &$form, FormStateInterface $form_state): void {
    $new_secret = bin2hex(random_bytes(32));

    // Update the secret key in configuration.
    $this->config('hellocoop.settings')
      ->set('secret', $new_secret)
      ->save();

    // Store the new secret in the form state for the current rebuild.
    $form_state->setRebuild(TRUE);
    // Temporary storage for rebuild.
    $form_state->set('new_secret', $new_secret);
  }

  /**
   * Ajax callback to update the secret key field.
   */
  public function updateSecretKeyAjaxCallback(array &$form, FormStateInterface $form_state): array {
    // Update the field value dynamically.
    if ($new_secret = $form_state->get('new_secret')) {
      // Update the #value property.
      $form['secret']['#value'] = $new_secret;
    }

    return $form['secret'];
  }

}
