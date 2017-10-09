<?php

namespace RedIRIS\SamlPoA;

class Defs
{

    const AUTHN_SUCCESS = true;

    /**
     * Authentication failed.
     */
    const AUTHN_FAILED = false;

    /**
     * Authorization succeeded.
     */
    const AUTHZ_SUCCESS = true;

    /**
     * Authorization failed.
     */
    const AUTHZ_FAILED = false;

    /**
     * Authentication failed error.
     */
    const NOAUTH_ERR = 0;

    /**
     * A system related error.
     */
    const SYSTEM_ERR = 1;

    /**
     * A configuration related error.
     */
    const CONFIG_ERR = 2;

    /**
     * An invitation related error.
     */
    const INVITE_ERR = 3;

    /**
     * An error triggered by the user.
     */
    const USER_ERR =  4;

    /**
     * SAML2 protocol namespace
     */
    const NS_SAML2_PROTOCOL = 'urn:oasis:names:tc:SAML:2.0:protocol';

    const ATTR_NAME_ID = 'nameid';

    const ATTR_NAME_ID_FORMAT = 'nameid_format';

    const ATTR_SESSION_EXPIRATION = 'session_expiration';

    const ATTR_SSO_URL = 'sso_url';

    const ATTR_LAST_REQUEST_ID = 'last_request_id';

    const ATTR_LAST_ASSERTION_ID = 'last_assertion_id';

    const ATTR_LAST_REQUEST_XML = 'last_request_xml';

    const ATTR_LAST_RESPONSE_XML = 'last_response_xml';

    /**
     * PAPI attribute names
     */

    public static $papi_attributes = array(
        'ePA' => 'eduPersonAffiliation',
        'ePE' => 'eduPersonEntitlement',
        'ePPN' => 'eduPersonPrincipalName',
        'ePTI' => 'eduPersonTargetedID',

        'dispN' => 'displayName',
        'gn' => 'givenName',
        'iMMA' => 'irisMailMainAddress',
        'iMAA' => 'irisMailAlternateAddress',

        'sHO' => 'schacHomeOrganization',
        'sHOT' => 'schacHomeOrganizationType',
        'sPUID' => 'schacPersonalUniqueID',
        'sPUC' => 'schacPersonalUniqueCode',
        'sUS' => 'schacUserStatus',
        'sn1' => 'schacSn1',
        'sn2' => 'schacSn2',
        'sG' => 'schacGender',
    );
}
