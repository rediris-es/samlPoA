<?php

namespace RedIRIS\SamlPoA\Authz;

class SourceIPAddrAuthzEngine extends AuthorizationEngine {

    public function isAuthorized($user, $attrs) {
        $default = $this->configuration['default'];

        $src_addr = $_SERVER['REMOTE_ADDR'];
        if (isset($this->configuration['proxies']) && in_array($_SERVER['REMOTE_ADDR'], $this->configuration['proxies'])) {
            $src_addr = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        }

        // check if there are IP filters
        $allowed = isset($this->configuration['allowed']) ? $this->configuration['allowed'] : array();
        $denied = isset($this->configuration['denied']) ? $this->configuration['denied'] : array();

        $allowed_match = $this->matches($src_addr, $allowed);
        $denied_match  = $this->matches($src_addr, $denied);

        // check matches giving priority to the default setting
        $order = array($default, !$default);
        foreach ($order as $option) {
            if ($option) { // check allowed attributes
                if ($allowed_match) {
                    return true;
                }
            } else { // check denied attributes
                if ($denied_match) {
                    return false;
                }
            }
        }

        return $default;
    }


    public function getAuthorizedList() {
        return $this->configuration['allowed'];
    }

    public function authorize($user, $attrs, $ref, $expires = 0) {
        return false;
    }

    public function revoke($mail) {
        return false;
    }

    /**
     * Check if an IP address matches the current allowed patterns.
     * @param ip The IP address.
     * @param patterns An array of patterns to be matched with.
     * @return The matched pattern. False otherwise.
     */
    private function matches($addr, $patterns) {
        // setup filtering criteria
        $search = array("/\./",
                        "/\.0/",
                        // IPv6 support
                        "/(:0){1,7}/",
                        "/^::/",
                        "/::$/");
        $replace = array("\.",
                         ".\d{1,3}",
                         // IPv6 support
                         "::",
                         "(([0-9a-fA-F]{1,4})){1,7}\:",
                         "(\:([0-9a-fA-F]{1,4})){1,7}");

        foreach ($patterns as $pattern) {
            if (is_array($pattern)) continue; // arrays are not supported, just single strings

            // transform from network notation to regular expression
            $mask = preg_replace($search, $replace, $pattern);

            if (preg_match("/".$mask."/i", $addr)) {
                return $pattern;
            }
        }
        return false;
    }
}
