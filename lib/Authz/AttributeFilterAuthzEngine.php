<?php

namespace RedIRIS\SamlPoA\Authz;

class AttributeFilterAuthzEngine extends AuthorizationEngine {

    public function isAuthorized($user, $attrs) {
        $default = $this->configuration['default'];
        $allowed = isset($this->configuration['allowed']) ? $this->configuration['allowed'] : array();
        $denied = isset($this->configuration['denied']) ? $this->configuration['denied'] : array();

        $allowed_match = $this->matches($attrs, $allowed);
        $denied_match  = $this->matches($attrs, $denied);

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
     * Returns the attribute (or attributes) that matched a list of patterns.
     * @param attrs An associative array of attributes to check.
     * @param patterns An associative array of attributes and their patterns.
     * @return The names of the attributes matched, comma separated if more than one.
     * False otherwise.
     */
    private function matches($attrs, $patterns) {
        $match = false;
        foreach ($patterns as $key => $value) {
            if (is_numeric($key) && is_array($value)) { // must match a bunch of options
                $partial_match = true;
                $matches = array();
                foreach ($value as $name => $pattern) {
                    if (!isset($attrs[$name])) { // attribute not set, skip this option
                        $partial_match = false;
                        break;
                    }

                    // convert attribute to array for easy handling
                    $attr = $attrs[$name];
                    if (!is_array($attrs[$name])) {
                        $attr = array($attrs[$name]);
                    }

                    // convert pattern to array for easy handling
                    $pats = $pattern;
                    if (!is_array($pattern)) {
                        $pats = array($pattern);
                    }

                    // check if any of the possible values match
                    $some_val_matches = false;
                    foreach ($attr as $item) {
                        foreach ($pats as $pat) {
                            if (preg_match('/^'.$pat.'$/', $item)) {
                                // attribute matches, continue
                                $some_val_matches = true;
                                break;
                            }
                        }
                        if ($some_val_matches) break;
                    }
                    if (!$some_val_matches) {
                        $partial_match = false;
                        break;
                    }

                    $matches[] = $name;
                }
                if ($partial_match) {
                    $match = implode(",", $matches);
                }

            } else { // match just one attribute
                if (!isset($attrs[$key])) // attribute not set, skip this option
                    continue;

                // convert attribute to array for easy handling
                $attr = $attrs[$key];
                if (!is_array($attrs[$key])) {
                    $attr = array($attrs[$key]);
                }

                // convert pattern to array for easy handling
                $pats = $value;
                if (!is_array($value)) {
                    $pats = array($value);
                }

                // check if any of the possible values match
                foreach ($attr as $item) {
                    foreach ($pats as $pattern) {
                        if (preg_match('/^'.$pattern.'$/', $item)) {
                            // attribute matches, stop searching
                            $match = $key;
                            break;
                        }
                    }
                    if ($match) break;
                }
            }
        }
        return $match;
    }
}
