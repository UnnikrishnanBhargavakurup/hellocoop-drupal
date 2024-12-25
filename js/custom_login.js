(function (Drupal, drupalSettings) {
    Drupal.behaviors.customLoginButton = {
      attach: function (context) {

        const baseUrl = drupalSettings.hellocoop.baseUrl;
        const scope = drupalSettings.hellocoop.scope;
        const provider_hint = drupalSettings.hellocoop.provider_hint;

        const buttons = context.querySelectorAll('.hello-btn');
        buttons.forEach(button => {
          if (!button.hasAttribute('data-custom-login-bound')) {
            button.setAttribute('data-custom-login-bound', 'true');
            button.addEventListener('click', function (event) {
              event.preventDefault(); // Prevent default behavior
  
              const LOGIN_PATH = `${baseUrl}?op=login` +
              `&target_uri=/user` +
              `&scope=${scope}` +
              `&provider_hint=${provider_hint}`;
          
              // Add spinner and disable the button
              this.classList.add('hello-btn-loader');
              this.disabled = true;
  
              // Redirect to the login endpoint
              window.location.href = LOGIN_PATH;
            });
          }
        });
      }
    };
  })(Drupal, drupalSettings);
  