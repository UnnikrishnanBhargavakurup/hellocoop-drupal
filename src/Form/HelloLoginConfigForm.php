<?php

namespace Drupal\HelloLogin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

/**
 * For hello_login configurations.
 */
class HelloLoginConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hello_login.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hello_login_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('hello_login.settings');

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

    // Field for provider hint checkboxes.
    $form['provider_hint'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Provider Hint'),
      '#description' => $this->t('Select the providers to include.'),
      '#options' => [
        'apple' => $this->t('Apple'),
        'discord' => $this->t('Discord'),
        'facebook' => $this->t('Facebook'),
        'github' => $this->t('GitHub'),
        'gitlab' => $this->t('GitLab'),
        'google' => $this->t('Google'),
        'twitch' => $this->t('Twitch'),
        'twitter' => $this->t('Twitter'),
        'tumblr' => $this->t('Tumblr'),
        'mastodon' => $this->t('Mastodon'),
        'microsoft' => $this->t('Microsoft'),
        'line' => $this->t('LINE'),
        'wordpress' => $this->t('WordPress'),
        'yahoo' => $this->t('Yahoo'),
        'phone' => $this->t('Phone'),
        'ethereum' => $this->t('Ethereum'),
        'qrcode' => $this->t('QR Code'),
        'apple--' => $this->t('Apple--'),
        'microsoft--' => $this->t('Microsoft--'),
      ],
      '#default_value' => $config->get('provider_hint') ?? ['github', 'google', 'twitter'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Generates the Quickstart URL for App ID creation.
   */
  private function generateQuickstartUrl(): string {
    $response_uri = Url::fromRoute(
      'hello_login_config_form',
      [],
      ['absolute' => TRUE]
    );

    $host_url = \Drupal::request()->getSchemeAndHttpHost();

    $logo_path = theme_get_setting('logo');
    $logo_url = '';

    if (!empty($logo_path['url'])) {
      // Check if the logo URL is already absolute.
      if (UrlHelper::isExternal($logo_path['url'])) {
        $logo_url = $logo_path['url'];
      }
      else {
        // Convert the relative URL to an absolute one.
        $logo_url = $host_url . $logo_path['url'];
      }
    }

    $responseUri = urlencode($response_uri->toString());
    $imageUri = urlencode($logo_url);
    $redirectUri = urlencode($host_url);
    return sprintf(
        'https://quickstart.hello.coop/?response_uri=%s&image_uri=%s&redirect_uri=%s',
        $responseUri,
        $imageUri,
        $redirectUri
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('hello_login.settings')
      ->set('api_route', $form_state->getValue('api_route'))
      ->set('app_id', $form_state->getValue('app_id'))
      ->set('secret', $form_state->getValue('secret'))
      ->set('provider_hint', array_filter($form_state->getValue('provider_hint')))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Custom submit handler to update the secret key dynamically.
   */
  public function updateSecretKey(array &$form, FormStateInterface $form_state): void {
    $new_secret = bin2hex(random_bytes(32));

    // Update the secret key in configuration.
    $this->config('hello_login.settings')
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
