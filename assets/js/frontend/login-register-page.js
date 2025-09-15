(function ($) {
  'use strict';

  $(function () {
    var settings = window.ttaLoginRegister || {};
    var $form = $('#tta-register-form');
    if (!$form.length) {
      return;
    }

    var $spinner = $form.find('.tta-admin-progress-spinner-svg');
    var $button = $form.find('button[type="submit"]');
    var $response = $('#tta-register-response');

    $spinner.hide();

    function resetState() {
      $response.removeClass('error updated').text('');
    }

    function showError(message) {
      $response.removeClass('updated').addClass('error').text(message);
    }

    function showSuccess(message) {
      $response.removeClass('error').addClass('updated').text(message);
    }

    $form.on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();

      resetState();

      var email = $form.find('[name="email"]').val();
      var emailVerify = $form.find('[name="email_verify"]').val();
      var password = $form.find('[name="password"]').val();
      var passwordVerify = $form.find('[name="password_verify"]').val();

      if (email !== emailVerify) {
        showError(settings.emailMismatch || 'Email addresses do not match.');
        return;
      }

      if (password !== passwordVerify) {
        showError(settings.passwordMismatch || 'Passwords do not match.');
        return;
      }

      if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(password)) {
        showError(settings.passwordRequirements || 'Password requirements not met.');
        return;
      }

      $button.prop('disabled', true);
      $spinner.show().css({ opacity: 0 }).fadeTo(200, 1);

      $.post(settings.ajaxUrl, {
        action: 'tta_register',
        nonce: settings.nonce,
        first_name: $form.find('[name="first_name"]').val(),
        last_name: $form.find('[name="last_name"]').val(),
        email: email,
        email_verify: emailVerify,
        password: password,
        password_verify: passwordVerify
      }, null, 'json').done(function (response) {
        $spinner.fadeOut(200);
        if (response && response.success) {
          showSuccess(settings.successMessage || 'Account created! Redirecting…');
          var redirectUrl = settings.redirectUrl || window.location.href;
          setTimeout(function () {
            window.location.href = redirectUrl;
          }, 600);
        } else {
          $button.prop('disabled', false);
          var msg = response && response.data && response.data.message ? response.data.message : (settings.requestFailed || 'Request failed.');
          showError(msg);
        }
      }).fail(function () {
        $spinner.fadeOut(200);
        $button.prop('disabled', false);
        showError(settings.requestFailed || 'Request failed.');
      });
    });
  });
})(jQuery);
