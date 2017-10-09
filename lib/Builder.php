<?php

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
