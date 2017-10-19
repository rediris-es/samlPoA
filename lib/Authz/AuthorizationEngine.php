<?php
/*
 *  This file is part of samlPoA and comes originally from phpPoA
 *
 *  Copyright 2005-2017 RedIRIS, http://www.rediris.es/
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

namespace RedIRIS\SamlPoA\Authz;

abstract class AuthorizationEngine {

    protected $configuration;

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Check authorization for the specified user.
     *
     * @param user The string that identifies the user.
     * @param attrs All attributes related to the user.
     * @return boolean RedIRIS\SamlPoA\Defs::AUTHZ_SUCCESS if the user is authorized,
     *                 RedIRIS\SamlPoA\Defs::AUTHZ_FAILED otherwise
     */
    public abstract function isAuthorized($user, $attrs);

    /**
     * @return array
     */
    public abstract function getAuthorizedList();

    /**
     * @return boolean
     */
    public abstract function authorize($user, $attrs, $ref, $expires = 0);

    /**
     * @return boolean
     */
    public abstract function revoke($mail);

}
