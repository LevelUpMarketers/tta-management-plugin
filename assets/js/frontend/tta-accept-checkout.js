(function ($) {
  /**
   * === TTA Checkout Reporter (console timeline) ==============================
   * Usage:
   *   const R = Reporter.start('Checkout');
   *   R.step('Collected form fields', data);
   *   R.net('AJAX request', { url, payload });
   *   R.net('AJAX response', { status, json, xhr });
   *   R.ok('Approved', summary);
   *   R.fail('Declined', detail);
   *   R.done();
   */
  const Reporter = (function () {
    const now = () => new Date();
    const fmtTime = (d) => d.toISOString();
    const rand = () => Math.random().toString(36).slice(2, 10);

    function create(label) {
      const traceId = `TTA-${rand()}-${Date.now()}`;
      const start = now();

      // Start the top-level group
      console.groupCollapsed(
        `%c[TTA Checkout] ${label}  trace=${traceId}  @ ${fmtTime(start)}`,
        'color:#0A84FF;font-weight:bold'
      );

      const API = {
        id: traceId,
        startedAt: start,
        step(title, data) {
          console.groupCollapsed(`%c• ${title}`, 'color:#34C759');
          if (arguments.length > 1) console.log(data);
          console.groupEnd();
          return API;
        },
        warn(title, data) {
          console.groupCollapsed(`%c• ${title}`, 'color:#FFD60A');
          if (arguments.length > 1) console.warn(data);
          console.groupEnd();
          return API;
        },
        error(title, data) {
          console.groupCollapsed(`%c• ${title}`, 'color:#FF453A');
          if (arguments.length > 1) console.error(data);
          console.groupEnd();
          return API;
        },
        net(title, data) {
          console.groupCollapsed(`%c⇄ ${title}`, 'color:#BF5AF2');
          if (arguments.length > 1) console.log(data);
          console.groupEnd();
          return API;
        },
        ok(title, data) {
          console.groupCollapsed(`%c✓ ${title}`, 'color:#32D74B');
          if (arguments.length > 1) console.log(data);
          console.groupEnd();
          return API;
        },
        fail(title, data) {
          console.groupCollapsed(`%c✗ ${title}`, 'color:#FF3B30');
          if (arguments.length > 1) console.log(data);
          console.groupEnd();
          return API;
        },
        done(summary) {
          const end = now();
          const ms = end - start;
          console.groupCollapsed(
            `%c[Done] ${fmtTime(end)}  (${ms} ms)`,
            'color:#0A84FF'
          );
          if (summary) console.log(summary);
          console.groupEnd();
          console.groupEnd(); // close top group
          // Expose last run globally for quick copy
          window.TTA_LAST_TRACE = window.TTA_LAST_TRACE || {};
          window.TTA_LAST_TRACE[traceId] = { startedAt: start, endedAt: end, summary: summary || null };
        }
      };
      return API;
    }

    return { start: create };
  })();

  function showMessage(msg, isError) {
    var $resp = $('#tta-checkout-response');
    $resp.text(msg);
    if (isError) {
      $resp.addClass('error');
    } else {
      $resp.removeClass('error');
    }
  }

  $(function () {
    var cfg = window.TTA_ACCEPT || {};

    // TEMP hard override if needed (dev only)
    if (!cfg.clientKey) {
      cfg.clientKey = '3R49F9pXNAKcmqE3932Y9EcV6qDUB7Kj6xudqH5g9Dcr5aAbcXX7MNJTB8n2VVas';
      window.TTA_ACCEPT = cfg;
    }

    // find form that has the place-order button
    var $form = $('form').has('button[name="tta_do_checkout"]');
    if (!$form.length) return;

    $form.on('submit', function (e) {
      const R = Reporter.start('Place Order clicked');

      // Identify the intended submitter
      var submitter = e.originalEvent && e.originalEvent.submitter;
      R.step('Initial config', {
        location: window.location.href,
        TTA_ACCEPT: window.TTA_ACCEPT,
        userAgent: navigator.userAgent
      });

      if (submitter && $(submitter).attr('name') !== 'tta_do_checkout') {
        R.warn('Submit ignored (not the checkout submit button)', {
          submitterName: $(submitter).attr('name')
        });
        return;
      }

      e.preventDefault();
      var $btn = $form.find('button[name="tta_do_checkout"]');
      var $spin = $form.find('.tta-admin-progress-spinner-svg');
      $btn.prop('disabled', true);
      $spin.css({ display: 'inline-block', opacity: 0 }).fadeTo(200, 1);
      showMessage('Processing payment…');

      // Collect fields
      var cardNumber = $.trim($form.find('[name="card_number"]').val());
      var exp = $.trim($form.find('[name="card_exp"]').val());
      var cvc = $.trim($form.find('[name="card_cvc"]').val());

      exp = exp.replace(/\s+/g, '');
      if (/^[0-9]{4}$/.test(exp)) {
        exp = exp.substring(0, 2) + '/' + exp.substring(2);
      }
      var parts = exp.split(/[\/\-]/);
      var month = parts[0];
      var year = parts[1];
      if (year && year.length === 2) year = '20' + year;

      const billing = {
        first_name: $form.find('[name="billing_first_name"]').val(),
        last_name: $form.find('[name="billing_last_name"]').val(),
        email: $form.find('[name="billing_email"]').val(),
        address: $form.find('[name="billing_street"]').val(),
        address2: $form.find('[name="billing_street_2"]').val(),
        city: $form.find('[name="billing_city"]').val(),
        state: $form.find('[name="billing_state"]').val(),
        zip: $form.find('[name="billing_zip"]').val(),
        country: 'USA'
      };

      const amount = (() => {
        const clean = s => {
          const n = parseFloat(String(s || '').trim().replace(/[, ]/g, '').replace(/[^\d.]/g, ''));
          return isNaN(n) ? null : n.toFixed(2);
        };
        return (
          clean($('#tta-final-total').text() || $('#tta-final-total').val()) ||
          clean($form.find('[name="tta_amount"]').val()) ||
          clean($btn.data('amount')) ||
          clean($form.data('amount')) ||
          '0.00'
        );
      })();

      R.step('Collected form fields', {
        amount,
        cardNumber,
        cardExpMMYY: exp,
        cardMonth: month,
        cardYear: year,
        cardCVC: cvc,
        billing
      });

      // Accept.js present?
      if (typeof Accept === 'undefined') {
        R.error('Accept.js not loaded', { scriptExpected: (cfg.mode === 'sandbox' ? 'https://jstest.authorize.net/v1/Accept.js' : 'https://js.authorize.net/v1/Accept.js') });
        showMessage('Payment library not loaded. Please refresh and try again.', true);
        $spin.fadeOut(200);
        $btn.prop('disabled', false);
        R.done({ outcome: 'client_error', reason: 'acceptjs_missing' });
        return;
      }

      // Tokenize
      R.step('Tokenizing via Accept.dispatchData', {
        authData: { apiLoginID: cfg.loginId, clientKey: cfg.clientKey },
        cardData: { cardNumber, month, year, cardCode: cvc }
      });

      Accept.dispatchData(
        {
          authData: { apiLoginID: cfg.loginId, clientKey: cfg.clientKey },
          cardData: { cardNumber: cardNumber, month: month, year: year, cardCode: cvc }
        },
        function (response) {
          R.step('Accept.js callback raw response', response);

          if (response.messages.resultCode === 'Error') {
            const errors = (response.messages.message || []).map(function (m) {
              return m.code + ': ' + m.text;
            });
            R.fail('Accept.js tokenization failed', { errors });
            showMessage(errors.join(' | ') || 'Payment error', true);
            $spin.fadeOut(200);
            $btn.prop('disabled', false);
            R.done({ outcome: 'client_error', reason: 'tokenization_failed', errors });
            return;
          }

          var opaque = response.opaqueData;
          R.ok('Token created (opaqueData)', opaque);

          // Build AJAX payload
          var payload = {
            action: 'tta_process_payment',
            _wpnonce: cfg.nonce,
            amount: amount,
            billing: billing,
            opaqueData: { dataDescriptor: opaque.dataDescriptor, dataValue: opaque.dataValue }
          };
          const ajaxUrl =
            cfg.ajaxUrl + (cfg.ajaxUrl.indexOf('?') > -1 ? '&' : '?') + 'action=tta_process_payment';

          R.net('AJAX request → admin-ajax.php', { url: ajaxUrl, method: 'POST', contentType: 'application/json', payload });

          $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json'
          })
            .done(function (res, textStatus, jqXHR) {
              R.net('AJAX response (success callback)', {
                httpStatus: jqXHR.status,
                textStatus,
                json: res
              });

              var data = res && typeof res === 'object' && res.data ? res.data : res;

              // Optional server debug printing
              if (data && data.debug) {
                R.step('Server debug (AJAX handler)', data.debug);
              }
              if (data && data.gateway) {
                R.step('Gateway debug (charge())', data.gateway);
              }

              if (res && res.success) {
                const summary = {
                  transaction_id: data.transaction_id || null,
                  message: 'Approved (server reported success)'
                };
                R.ok('Payment approved', summary);
                showMessage('Payment approved.', false);

                // Clear sensitive fields
                $form.find('[name="card_number"],[name="card_exp"],[name="card_cvc"]').val('');

                if (typeof window.ttaFinalizeOrder === 'function') {
                  const last4 = (cardNumber || '').slice(-4);
                  window.ttaFinalizeOrder(data.transaction_id, last4);
                } else {
                  R.warn('Finalize callback missing (window.ttaFinalizeOrder)');
                }
              } else {
                const msg = (data && data.error) ? String(data.error) : 'Payment failed.';
                R.fail('Server indicated failure', { error: msg });
                showMessage(msg, true);
              }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
              R.net('AJAX response (fail callback)', {
                httpStatus: jqXHR.status,
                textStatus,
                errorThrown,
                responseText: jqXHR.responseText
              });
              showMessage('Request failed', true);
            })
            .always(function (dataOrJq, textStatus, jqMaybe) {
              $spin.fadeOut(200);
              $btn.prop('disabled', false);

              // Attempt to surface gateway diagnostics if present in any shape
              try {
                const raw =
                  (dataOrJq && dataOrJq.success !== undefined) ? dataOrJq :
                  (jqMaybe && jqMaybe.responseJSON) ? jqMaybe.responseJSON :
                  null;
                var res = raw && raw.data ? raw.data : raw;

                if (res && res.gateway && res.gateway.diag) {
                  const d = res.gateway.diag;
                  const finalSummary = {
                    responseCode: d.responseCode,
                    avsResultCode: d.avsResultCode,
                    cvvResultCode: d.cvvResultCode,
                    transId: d.transId,
                    authCode: d.authCode,
                    hints: d.hints || []
                  };
                  if (d.responseCode === '1') {
                    R.ok('Gateway final summary', finalSummary);
                    R.done({ outcome: 'approved', summary: finalSummary });
                  } else {
                    R.fail('Gateway final summary', finalSummary);
                    R.done({ outcome: 'declined', summary: finalSummary });
                  }
                } else {
                  R.done({ outcome: 'completed_no_diag' });
                }
              } catch (e) {
                R.warn('Final summarize error', { error: String(e) });
                R.done({ outcome: 'completed_with_summarize_error' });
              }
            });
        }
      );
    });
  });
})(jQuery);
