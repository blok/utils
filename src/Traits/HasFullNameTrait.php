<?php

namespace Blok\Utils\Traits;

/**
 * Class modelUserTrait
 *
 * Usefull moduleUser extender trait for your model
 *
 * @package Arx\traits
 */
trait HasFullNameTrait {
    /**
     * Get Meta
     *
     * @param $value
     * @return array|mixed
     */
    public function getFullNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if (!empty($this->first_name) && !empty($this->last_name)) {
            return $this->first_name.' '.$this->last_name;
        }

        if (!empty($this->first_name) && empty($this->last_name)) {
            return $this->first_name;
        }

        if(empty($this->first_name) && !empty($this->last_name)) {
            return $this->last_name;
        }

        return $this->email;
    }

    /**
     * Inflexive trait
     *
     * @param $value
     * @return array|mixed
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst(strtolower($value));
    }

    /**
     * Inflexive trait
     *
     * @param $value
     * @return array|mixed
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst(strtolower($value));
    }

    /**
     * Get Gravatar image
     *
     * @param int $s
     * @param string $d
     * @param string $r
     * @param bool $img
     * @param array $atts
     * @return string
     */
    public function getGravatar($s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array()){

        $email = $this->email;

        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }
}
