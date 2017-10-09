<?php

namespace RedIRIS\SamlPoA;

use \OneLogin_Saml2_Auth;

class PoA
{
    /** @var Array */
    protected $settings;

    /** @var \OneLogin_Saml2_Auth */
    protected $auth;

    /** @var RedIRIS\SamlPoA\Authz\AuthorizationEngine[] */
    protected $authz_engines;

    /**
     * @param \OneLogin_Saml2_Auth $auth
     * @param Array $settings
     */
    public function __construct(\OneLogin_Saml2_Auth $auth, Array $settings)
    {
        $this->auth = $auth;
        $this->settings = $settings;
        $this->authz_engines = array();

        if (isset($settings['authz']) && is_array($settings['authz'])) {
            $this->setUpAuthorization($settings['authz']);
        }

        // Logout!
        if (isset($_GET['sls'])) {
            $this->logout(true);
        }
    }

    /**
     * Sets up authorization engines
     *
     * @param Array $settings Configuration
     */
    protected function setUpAuthorization(Array $settings)
    {
        foreach ($settings as $authz) {
            if (!isset($authz['engine']) || !isset($authz['config'])) {
                throw new \Exception('Invalid authorization configuration');
            }

            $class = $authz['engine'];
            $engine = new $class($authz['config']);

            $this->authz_engines[$authz['engine']] = $engine;
        }
    }


    /**
     * Generates XML metadata associated to this SP
     *
     * @throws \RedIRIS\SamlPoA\Exception\InvalidMetadataException   If any metadata validation error is detected
     * @return string XML representation of metadata
     */
    public function getMetadata()
    {
        try {
            $settings = $this->auth->getSettings();
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);

            if (empty($errors)) {
                return $metadata;
            }

            throw new Exception\InvalidMetadataException(
                'Invalid SP metadata: '.implode(', ', $errors)
            );
        } catch (\Exception $e) {
            throw new Exception\InvalidMetadataException($e->getMessage());
        }
    }

    /**
     * Dumps current XML metadata using proper headers
     */
    public function dumpMetadata()
    {
        $metadata = $this->getMetadata();
        header('Content-type: application/xml');
        echo $metadata;
        exit;
    }

    /**
     * Authenticates current user
     *
     * @return RedIRIS\SamlPoA\Defs::AUTHN_SUCCESS or RedIRIS\SamlPoA\Defs::AUTHN_FAILED
     */
    public function authenticate()
    {
        @session_start();

        if ($this->isAuthenticated()) {
            return Defs::AUTHN_SUCCESS;
        }

        // Consume assertion instead of trying to log in again
        if (isset($_GET['acs'])) {
            if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
                $request_id = $_SESSION['AuthNRequestID'];
            } else {
                $request_id = null;
            }

            $this->auth->processResponse($request_id);
            unset($_SESSION['AuthNRequestID']);
            $errors = $this->auth->getErrors();

            if (!empty($errors) || !$this->isAuthenticated()) {
                return Defs::AUTHN_FAILED;
            }

            $_SESSION['samlUserdata'] = $this->auth->getAttributes();
            $_SESSION['samlProtodata'] = [
                 Defs::ATTR_NAME_ID => $this->auth->getNameId(),
                 Defs::ATTR_NAME_ID_FORMAT => $this->auth->getNameIdFormat(),
                 Defs::ATTR_SESSION_EXPIRATION => $this->auth->getSessionExpiration(),
                 Defs::ATTR_SSO_URL => $this->auth->getSSOurl(),
                 Defs::ATTR_LAST_REQUEST_ID => $this->auth->getLastRequestID(),
                 Defs::ATTR_LAST_ASSERTION_ID => $this->auth->getLastAssertionID(),
                 Defs::ATTR_LAST_REQUEST_XML => $this->auth->getLastRequestXML(),
                 Defs::ATTR_LAST_RESPONSE_XML => $this->auth->getLastResponseXML(),
            ];

            if (isset($_POST['RelayState'])
                && \OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
                $this->auth->redirectTo($_POST['RelayState']);
            }


            return Defs::AUTHN_SUCCESS;
        }

        try {
            $sso_url = $this->auth->login(null, array(), false, false, true);
            $_SESSION['AuthNRequestID'] = $this->auth->getLastRequestID();

            header('Pragma: no-cache');
            header('Cache-Control: no-cache, must-revalidate');
            header('Location: ' . $sso_url);
            exit();
        } catch (\Exception $e) {
            return Defs::AUTHN_FAILED;
        }

        return Defs::AUTHN_SUCCESS;
    }

    /**
     * Check if current user is authenticated
     *
     * @return RedIRIS\SamlPoA\Defs::AUTHN_SUCCESS or RedIRIS\SamlPoA\Defs::AUTHN_FAILED
     */
    public function isAuthenticated()
    {
        return $this->auth->isAuthenticated() || isset($_SESSION['samlUserdata']);
    }

    /**
     * Return all attributes from user using PAPI names
     *
     * @return Array
     */
    public function getAttributes()
    {
        $flipped_papi_attributes = array_flip(Defs::$papi_attributes);
        $attributes = $this->getSAMLAttributes();
        $result = array();

        foreach ($attributes as $name => $value) {
            $attribute = isset($flipped_papi_attributes[$name]) ?
                $flipped_papi_attributes[$name] : $name;

            // Multi-valued: array; single value: string
            $new_value = count($value) > 1 ? $value : $value[0];
            $result[$attribute] = $new_value;
        }

        return $result;
    }

    /**
     * Return all attributes from user using original SAML names
     *
     * @return Array
     */
    public function getSAMLAttributes()
    {
        $result = $this->auth->getAttributes();

        if (empty($result)) {
            $result = isset($_SESSION['samlUserdata']) ? $_SESSION['samlUserdata'] : array();
        }

        return $result;
    }

    /**
     * Return all attributes from user
     *
     * @param string $name
     * @param string $namespace
     * @return mixed|null
     */
    public function getAttribute($name, $namespace = null)
    {
        if ($namespace === Defs::NS_SAML2_PROTOCOL) {
            return $this->getSAML2Details($name);
        }

        $attributes = $this->getAttributes();

        $result = null;

        if (isset($attributes[$name])) {
            $result = $attributes[$name];
        }

        return $result;
    }

    /**
     * Returns SAML2 protocol details
     *
     * @param string $name
     * @return string|null
     */
    public function getSAML2Details($name)
    {
        $result = isset($_SESSION['samlProtodata'][$name]) ?
            $_SESSION['samlProtodata'][$name] :
            null;

        return $result;
    }

    /**
     * Handles logout
     *
     * @param bool $slo Single Log Out (defaults to true)
     * @return bool true on success, false otherwise
     */
    public function logout($slo = true)
    {
        @session_start();

        // Just remove local session information
        if ($slo === false) {
            \OneLogin_Saml2_Utils::deleteLocalSession();
            return true;
        }

        // If we are not on the Single Log Out service, proceed
        if (!isset($_GET['sls'])) {
            $this->auth->logout();
            return true;
        }

        if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
            $request_id = $_SESSION['LogoutRequestID'];
        } else {
            $request_id = null;
        }

        $this->auth->processSLO(false, $request_id);
        $errors = $this->auth->getErrors();

        // TODO store last errors
        if (!empty($errors)) {
            return false;
        }

        return true;
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
        if ($attrs === null) {
            $attrs = $this->getAttributes();
        }

        if (empty($engine) && empty($this->authz_engines)) {
            return true;
        }

        if (!empty($engine)) {
            if (!isset($this->authz_engines[$engine])) {
                throw new \Exception('Authorization engine not configured: ' . $engine);
            }

            $engines = array($this->authz_engines[$engine]);
        } else {
            $engines = $this->authz_engines;
        }

        $result = false;

        foreach ($engines as $authz_engine) {
            $result |= $authz_engine->isAuthorized($user, $attrs);
        }

        return $result;
    }

    /**
     * Returns the authorization engines configured for the current PoA, or
     * the one specified.
     *
     * @param string $engine The name of the authorization engine to retrieve.
     * If more than one engine should be returned then this must be an array.
     * @return \RedIRIS\SamlPoA\Authz\AuthorizationEngine The authorization engine(s) requested if it was previously configured.
     * If none was specified, all configured engines will be returned. An empty
     * array will be returned if no authorization engines were found.
     */
    public function getAuthorizationEngines($engine = null)
    {
        $list = $this->authz_engines;

        if (!empty($engine)) {
            $list = array();
            $engines = is_array($engine) ? $engine : array($engine);

            foreach ($engines as $e) {
                if (!isset($this->authz_engines[$e])) {
                    throw new \Exception('Engine ' . $e . ' not found');
                }

                $list[$e] = $this->authz_engines[$e];
            }
        }

        return $list;
    }

    /**
     * Placeholder for getAuthorizationLevels(), as the original phpPoA library
     * always returns an empty array
     */
    public function getAuthorizationLevels($user, $attributes)
    {
        return array();
    }

    /**
     * Revoke authorization for a user, given his/her email
     *
     * @param string $mail
     * @param string $engine
     * @return bool true if authorization was revoked on all engines
     */
    public function revoke($mail, $engine = null)
    {
        $result = false;

        $engines = array_keys($this->authz_engines);

        if (!empty($engine)) {
            $engines = is_array($engine) ? $engine : array($engine);
        }

        foreach ($engines as $e) {
            if (!isset($this->authz_engines[$e])) {
                throw new \Exception('Engine ' . $e . ' not found');
            }

            $result |= $this->authz_engines[$e]->revoke($mail);
        }
    }

    /**
     * Authorize a given subject with the data retrieved from federated login.
     * Multiple authorization engines are supported, so
     * authorization will be done in all of them.
     *
     * @param string $user The subject of authorization.
     * @param Array $attrs The attributes of the user.
     * @param mixed $reference An internal reference that may be valuable for the engine, tipically
     * referring to a previous invitation or similar.
     * @param int $expires The time (POSIX) when authorization will expire. Use 0 if authorization
     * should never expire. Defaults to 0.
     * @param string $engine The authorization engine(s) to use. All engines are used if none specified.
     * If more than one engine should be checked then this must be an array.
     * @return bool true if any of the supported engines succeeds or if no
     * authorization engine is configured. false if all the engines fail.
     */
    public function authorize($user, $attrs, $reference = null, $expires = 0, $engine = null) {
        $result = false;

        // check specific engines
        $engines = array_key($this->authz_engines);
        if (!empty($engine)) {
            $engines = is_array($engine) ? $engine : array($engine);
        }

        // iterate over engines
        foreach ($engines as $e) {
            if (!isset($this->authz_engines[$e])) {
                throw new \Exception('Engine ' . $e . ' not found');
            }

            $result |= $this->authz_engines[$e]->authorize($user, $attrs, $reference, $expires);
        }

        return $result;
    }

    /**
     * Returns last error(s)
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->auth->getErrors();
    }

    /**
     * Returns last error reason
     *
     * @return string
     */
    public function getLastErrorReason()
    {
        return $this->auth->getLastErrorReason();
    }
}
