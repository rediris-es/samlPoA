<?php

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
