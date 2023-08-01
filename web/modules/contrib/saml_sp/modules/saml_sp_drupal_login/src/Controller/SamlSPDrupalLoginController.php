<?php

namespace Drupal\saml_sp_drupal_login\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\saml_sp\Entity\Idp;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides route responses for the SAML SP module.
 */
class SamlSPDrupalLoginController extends ControllerBase {

  /**
   * Initiate a SAML login for the given IdP.
   */
  public function initiate(Idp $idp) {
    $config = $this->config('saml_sp_drupal_login.config');
    if ($this->currentUser()->isAuthenticated()) {
      $redirect_path = $config->get('logged_in_redirect');
      if ($this->config('saml_sp.settings')->get('debug')) {
        _saml_sp__debug('$redirect_path', $redirect_path);
      }
      if (strpos($redirect_path, '/') === 0) {
        $url = URL::fromUserInput($redirect_path);
      }
      else {
        $url = URL::fromRoute($redirect_path);
      }
      // The user is already logged in, so redirect.
      return new RedirectResponse($url->toString());
    }

    // Start the authentication process; invoke
    // saml_sp_drupal_login__saml_authenticate() when done.
    $callback = 'saml_sp_drupal_login__saml_authenticate';
    $forceAuthn = $config->get('force_authentication') ?? FALSE;
    $return = saml_sp_start($idp, $callback, $forceAuthn);
    if (!empty($return)) {
      // Something was returned, echo it to the screen.
      return $return;
    }
  }

  /**
   * Tests condition for requesting accounts.
   */
  public function access(AccountInterface $account) {
    $authenticated = saml_sp_drupal_login_is_authenticated();
    return AccessResult::allowedIf($account->isAnonymous() && $authenticated);
  }

}
