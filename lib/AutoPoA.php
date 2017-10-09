<?php

namespace RedIRIS\SamlPoA;

use \OneLogin_Saml2_Auth;

class AutoPoA extends PoA
{
    /**
     * @param \OneLogin_Saml2_Auth $auth
     * @param Array $settings
     */
    public function __construct(\OneLogin_Saml2_Auth $auth, Array $settings)
    {
        parent::__construct($auth, $settings);

        if (empty($settings['noauthurl'])) {
            throw new \Exception('noauthurl has to be set when using AutoPoA');
        }
    }

    /**
     * Authenticates current user. In case authentication fails, current user will get
     * redirected to noauthurl (from settings)
     *
     * @return RedIRIS\SamlPoA\Defs::AUTHN_SUCCESS or RedIRIS\SamlPoA\Defs::AUTHN_FAILED
     *
     */
    public function authenticate()
    {
        if (!parent::authenticate()) {
            header("Location: " . $this->settings['noauthurl']);
            exit;
        }

        return Defs::AUTHN_SUCCESS;
    }

    /**
     * Checks if an user is authorized
     *
     * @param string $user
     * @param Array $attrs
     * @param string $engine
     * @return bool
     */
    public function isAuthorized($user, $attrs = null, $engine = null)
    {
        if (!parent::isAuthorized($user, $attrs, $engine)) {
            header("Location: " . $this->settings['noauthurl']);
            exit;
        }

        return true;
    }
}
