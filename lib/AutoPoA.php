<?php

/*
 *  This file is part of samlPoA.
 *
 *  Copyright 2017 RedIRIS, http://www.rediris.es/
 *
 *  samlPoA is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  samlPoA is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with samlPoA.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace RedIRIS\SamlPoA;

use \OneLogin\Saml2\Auth;

class AutoPoA extends PoA
{
    /**
     * @param \OneLogin\Saml2\Auth $auth
     * @param Array $settings
     */
    public function __construct(\OneLogin\Saml2\Auth $auth, Array $settings)
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
