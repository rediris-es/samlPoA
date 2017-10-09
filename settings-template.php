<?php

$config = array(
    // Opciones de autorización
    'authz' => array(
        // Ejemplo de motor de autorización basado en IP
        /*
        array(
            'engine' => '\RedIRIS\SamlPoA\Authz\SourceIPAddrAuthzEngine',
            'config' => array(
                // Opcional. Si la aplicación está tras un balanceador, incluir su IP
                // en esta sección, y la biblioteca usará el contenido de la cabecera
                // X-Forwarded-For
                'proxies' => array(
                    '10.0.0.1',
                ),
                'default' => false, // Rechazar por defecto
                'allowed' => array(
                    '1.2.3.4',
                    '5.6.7.8',
                ),
                'denied' => array(),
            ),
        ),
         */
    ),
    // Modo estricto de validación en SAML
    'strict' => false,

    // Depuración
    'debug' => false,

    // URL base. Si no se especifica, se intenta adivinar en tiempo de ejecución
    'baseurl' => null,

    // Información de contacto para el SP
    'contactPerson' => array (
        'technical' => array (
            'givenName' => '',
            'emailAddress' => ''
        ),
        'support' => array (
            'givenName' => '',
            'emailAddress' => ''
        ),
    ),

    // Organización
    'organization' => array (
        'es-ES' => array(
            'name' => '',
            'displayname' => '',
            'url' => ''
        ),
    ),

    // Datos del proveedor de servicio
    'sp' => array (
        // Identificador del SP. Debe ser una URI
        'entityId' => '',
        'assertionConsumerService' => array (
            // Servicio de consumo de aserciones. Debe contener el parámetro ?acs
            'url' => '',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        /*
        // Si la aplicación necesita recibir un conjunto de atributos,
        // descomentar el siguiente bloque y configurar los atributos
        "attributeConsumingService"=> array(
                "serviceName" => "Aplicación 1",
                "serviceDescription" => "Aplicación de ejemplo",
                "requestedAttributes" => array(
                    array(
                        "name" => "atributo1",
                        "isRequired" => true,
                    ),
                )
        ),
        */
        // Servicio de cierre de sesión
        'singleLogoutService' => array (
            // Servicio de cierre de sesión. Debe contener el parámetro ?sls
            'url' => '',
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => <<<CERT
...
CERT
,
        'privateKey' => <<<PRIVKEY
...
PRIVKEY
,
        /*
         * Key rollover
         */
        // 'x509certNew' => '',
    ),

    // Compresión de peticiones y respuestas
    'compress' => array (
        'requests' => true,
        'responses' => true
    ),

    // Opciones de seguridad
    'security' => array (
        // Cifrar NameID
        'nameIdEncrypted' => false,

        // Firmar peticiones <samlp:AuthnRequest>
        'authnRequestsSigned' => false,

        // Firmar peticiones <samlp:logoutRequest>
        'logoutRequestSigned' => false,

        // Enviar <samlp:logoutResponse> firmados
        'logoutResponseSigned' => false,

        // Firma de metadatos
        'signMetadata' => false,


        // Requerir firma en elementos <samlp:Response>, <samlp:LogoutRequest> y
        // <samlp:LogoutResponse> enviados a este SP
        'wantMessagesSigned' => false,

        // Requerir cifrado en los elementos <saml:Assertion> enviados a este SP
        'wantAssertionsEncrypted' => true,

        // Requerir firma en los elementos <saml:Assertion> enviados a este SP
        'wantAssertionsSigned' => true,

        // Requerir NameID en todos las SAMLResponse
        'wantNameId' => true,

        // Requerir NameID cifrado
        'wantNameIdEncrypted' => false,

        // Algoritmo de firma
        //    'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
        //    'http://www.w3.org/2000/09/xmldsig#dsa-sha1'
        //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'
        //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha384'
        //    'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512'
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',

        // Algoritmo de digest
        //    'http://www.w3.org/2000/09/xmldsig#sha1'
        //    'http://www.w3.org/2001/04/xmlenc#sha256'
        //    'http://www.w3.org/2001/04/xmldsig-more#sha384'
        //    'http://www.w3.org/2001/04/xmlenc#sha512'
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',

        // ADFS URL-Encodes SAML data as lowercase, and the toolkit by default uses
        // uppercase. Turn it True for ADFS compatibility on signature verification
        // Convertir todos los datos SAML a minúsculas. Útil si se quiere
        // compatibilidad con ADFS en la validación de firmas
        'lowercaseUrlencoding' => false,
    ),


    // Datos del proveedor de identidad. Se recomienda usar bin/parse-idp-metadata.php, 
    // y copiar tras este comentario su salida
    // 'idp' => ....
);

return $config;
