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

class Builder
{
    public static function poa(Array $settings)
    {
        $auth = new \OneLogin_Saml2_Auth($settings);

        return new PoA(
            $auth,
            $settings
        );
    }

    public static function autopoa(Array $settings)
    {
        $auth = new \OneLogin_Saml2_Auth($settings);

        return new AutoPoA(
            $auth,
            $settings
        );
    }
}
