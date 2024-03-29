<?php

namespace Blok\Utils;

/**
 * Utils method
 *
 * @author   Daniel Sum <daniel@cherrypulp.com>
 * @author   Stéphan Zych <stephan@cherrypulp.com>
 */

use Blok\Utils\Traits\SingletonTrait;
use ReflectionClass;

class Utils
{
    use SingletonTrait;

    public $_start;

    public function __construct(){
        $this->_start = microtime();
    }

#A
    /**
     * Create an Alias of a function check if it's exist
     * @param $aliasName
     * @param $callback
     * @return array
     */
    public static function alias($aliasName, $callback)
    {
        $err = false;

        if (function_exists($aliasName)) {
            $err = 'This function already' . $aliasName . ' exists';
        }

        if (!is_callable($callback, false, $realfunc)) {
            $err = $callback.' is not callable';
        }

        if($err === false){
            try {
                $bodyFunc = 'function ' . $aliasName . '() {
                    $args = func_get_args();
                    return call_user_func_array("' . $realfunc . '", $args);
                }';

                eval($bodyFunc);

                return array('result' => true, 'msg' => "function $aliasName created from $callback");

            } catch (\Exception $e) {

                $trace = debug_backtrace();

                $msg = sprintf(
                    '%s(): %s in %s on line %d',
                    $trace[0]['function'],
                    $err,
                    $trace[0]['file'],
                    $trace[0]['line']
                );

                trigger_error($msg,
                    E_USER_WARNING
                );

                return array('result' => false, 'msg' => $err);
            }

        } else {
            return array('result' => false, 'msg' => $err);
        }
    } // alias

#B

    public static function benchIt()
    {
        if (function_exists('xdebug_time_index')) {
            return xdebug_time_index();
        } else {
            return microtime();
        }
    } // benchIt

#C
    /**
     * @param $url
     * @return mixed
     */
    public static function curlGet($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($curl);
        curl_close($curl);

        return $return;
    } // curlGet

    /**
     * Call an abstract object with param
     * @param $sObject
     * @param array $array
     * @return object
     * @throws \ReflectionException
     */
    public static function call_user_obj_array($sObject, $param = array()){
        $reflector = new ReflectionClass($sObject);
        return $reflector->newInstanceArgs($param);
    }


    /**
     * Transform a CSV file to array
     *
     * @param $file
     * @param array $param
     * @return array
     */
    public static function csvToArray($file, array $param = array(
        'length' => 0,
        'delimiter' => ';',
        'enclosure' => '"',
        'escape' => '\\',
        'skipFirstRow' => false,
        'indexFromFirstRow' => false
    )){

        $defParam = array(
            'length' => 0,
            'delimiter' => ';',
            'enclosure' => '"',
            'escape' => '\\',
            'skipFirstRow' => false,
            'indexFromFirstRow' => false
        );

        $param = array_merge($defParam, $param);

        $handle = fopen($file, 'r');

        $data = array();

        $first = true;
        $index = false;

        while (($line = fgetcsv($handle, $param['length'], $param['delimiter'], $param['enclosure'], $param['escape'])) !== FALSE) {
            if($first){
                if($param['indexFromFirstRow']){
                    $index = $line;
                }
            }

            if(($param['indexFromFirstRow'] && !$first) || ($param['indexFromFirstRow'] && $param['skipFirstRow'] !== true)){

                $newline = array();

                foreach($line as $key => $value){
                    if(isset($index[$key])){
                        $newline[$index[$key]] = $value;
                    } else {
                        $newline[] = $value;
                    }
                }
                $data[] = $newline;
            } elseif(!$first || ($first && $param['skipFirstRow'] !== true)) {
                $data[] = $line;
            }

            $first = false;
        }

        fclose($handle);

        return $data;


    }

    /**
     * Debug method
     */
    public static function debug(){
        $args = func_get_args();

        foreach($args as $item){
            echo '<pre>';
            print_r($item);
            echo '</pre>';
        }

        die();
    }

#E
#F

    public function findCaller($mVar)
    {
        if (is_string($mVar)) {
            $aQuery = array('function' => $mVar);
        } elseif (is_array($mVar)) {
            $aQuery = $mVar;
        }

        $aErrors = debug_backtrace();

        foreach ($aErrors as $key => $error) {
            if (array_intersect($error, $aQuery) == $aQuery && !empty($error['line']) && !empty($error['file'])) {
                $line = $error['line'];
                $file = $error['file'];
                return $error;
            }
        }
    } // findCaller

    public function findCallers($functionName)
    {
        $aErrors = debug_backtrace();
        $aResult = array();

        foreach ($aErrors as $error) {
            if ($error['function'] === $functionName && !empty($error['line']) && !empty($error['file'])) {
                $aResult[] = $error;
            }
        }

        return $aResult;
    } // findCallers

#G

    /**
     * This will generate alphabetical columns
     *
     * @param $end_column
     * @param string $first_letters
     * @return array
     */
    public static function genAlphaColumns($end_column, $first_letters = ''){

        $columns = array();
        $length = strlen($end_column);
        $letters = range('A', 'Z');

        foreach ($letters as $letter) {

            $column = $first_letters . $letter;

            $columns[] = $column;

            if ($column == $end_column)
                return $columns;
        }

        foreach ($columns as $column) {
            if (!in_array($end_column, $columns) && strlen($column) < $length) {
                $new_columns = self::genAlphaColumns($end_column, $column);
                $columns = array_merge($columns, $new_columns);
            }
        }

        return $columns;
    }

    public static function getContents($file)
    {
        return self::curlGet($file);
    } // getContents


    public static function getHeight($image)
    {
        $sizes = getimagesize($image);
        return $sizes[1];
    } // getHeight


    public static function getJSON($url, $as_array = false)
    {
        return json_decode(@file_get_contents($url), $as_array);
    } // getJSON

    public static function getIp()
    {
        return \Request::getClientIp();
    }

    public static function getIpInfos($ip = null)
    {
        return self::getJSON('http://freegeoip.net/json');
    }

    /**
     * Get Video embed from Youtube, Vimeo or Dailymotion
     *
     * @param $url
     * @param int $width
     * @param int $height
     * @return bool|string
     */
    public static function getVideoEmbed($url, $width = 560, $height = 315)
    {
        switch (true) {
            case false !== stripos($url, "youtu"):

                $id = self::getVideoId($url);

                if (!$id) {
                    return '<iframe width="' . $width . '" height="' . $height . '" src="//www.youtube.com/embed/' . $id . '?rel=0" frameborder="0" allowfullscreen></iframe>';
                }

                return false;

            case false !== stripos($url, "vimeo"):

                $id = self::getVideoId($url);

                if (!$id) {
                    return '<iframe src="//player.vimeo.com/video/' . $id . '?portrait=0" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
                }

                return false;

            case false !== stripos($url, "dailymotion"):

                $id = self::getVideoId($url);

                if (!$id) {
                    return '<iframe frameborder="0" width="' . $width . '" height="' . $height . '" src="//www.dailymotion.com/embed/video/' . $id . '"></iframe>';
                }

                return false;
        }
    } // getVideoEmbed


    /**
     *
     * @param $url
     * @return bool|mixed
     */
    public static function getVideoId($url){
        switch (true) {
            case false !== stripos($url, "youtu"):
                $url_string = parse_url($url, PHP_URL_QUERY);
                parse_str($url_string, $args);
                $id = isset($args['v']) ? $args['v'] : false;

                if (!empty($id)) {
                    return $id;
                }

                return false;

            case false !== stripos($url, "vimeo"):
                $id = filter_var($url, FILTER_SANITIZE_NUMBER_INT);
                if (!empty($id)) {
                    return $id;
                }

                return false;

            case false !== stripos($url, "dailymotion"):
                $id = str_replace('/video/', '', parse_url($url, PHP_URL_PATH));

                if (!empty($id)) {
                    return $id;
                }

                return false;
        }
    }

    public static function getVideoThumb($url, $params = [])
    {
        switch (true) {
            case false !== stripos($url, "youtu"):

                $id = self::getVideoId($url);

                if (!$id) {
                    return "//img.youtube.com/vi/" . $id . "/default.jpg";
                }

                return false;

            case false !== stripos($url, "vimeo"):

                $id = self::getVideoId($url);

                if (!$id) {
                    $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".$id.".php"));
                    return $hash[0]['thumbnail_medium'];
                }

                return false;

            case false !== stripos($url, "dailymotion"):

                $id = self::getVideoId($url);

                if (!$id) {
                    return "//www.dailymotion.com/thumbnail/video/".$id;
                }

                return false;
        }
    } // epre


    public static function getWidth($image)
    {
        $sizes = getimagesize($image);
        return $sizes[0];
    } // getWidth

#H
    public static function header($type, $output = false)
    {
        $aHeader = include(__DIR__ .'/utils/header.php');

        return $aHeader[$type];
    } // header

#I
    public static function issetOr($sValue, $defaultValue = null)
    {

        if (($sValue = strstr($sValue, '$')) !== false) {
            $sValue = substr($sValue, 1);
        }

        if (isset(${$sValue})) {
            return $sValue;
        }

        return $defaultValue;
    }

    /**
     * check if string is json
     *
     * @param $string
     * @return bool
     * @throws \JsonException
     */
    public static function isJson($string){

        if(!is_string($string)) return false;

        if(empty($string)) return false;

        json_decode($string, false, 512, JSON_THROW_ON_ERROR);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Function to check if the Server is on HTTPS or not
     *
     * You can precise a server configuration (useful if you need to check an external server Params)
     *
     * @param array $server
     *
     * @return bool
     */
    public static function isHTTPS(array $server = array())
    {

        $response = false;

        if (empty($server) && isset($_SERVER)) {
            $server = $_SERVER;
            if (!isset($server['SERVER_PORT'])) {
                $server['SERVER_PORT'] = 80;
            }
        } else {
            $server['HTTPS'] = getenv('HTTPS');
            $server['SERVER_PORT'] = getenv('SERVER_PORT');
        }

        if ((isset($server['HTTPS']) && $server['HTTPS'] !== 'off')
            || $server['SERVER_PORT'] == 443
        ) {
            $response = true;
        }

        return $response;
    }

    /**
     * Check if it's a closure string
     *
     * @param $var
     * @return bool
     */
    public static function isClosure($var)
    {
        return is_object($var) && ($var instanceof \Closure);
    }

    /**
     * Check if data is serialized string
     *
     * @param $data
     * @return bool
     */
    public static function isSerialized($data){
        // if it isn't a string, it isn't serialized
        if ( !is_string( $data ) )
            return false;
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
            return false;
        switch ( $badions[1] ) {
            case 'a' :
            case 'O' :
            case 's' :
                if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                    return true;
                break;
        }
        return false;
    }

#J

    /**
     * Simple json Die with correct header
     * @param $array
     */
    public static function jsonDie($array)
    {
        header("content-type: application/json");
        die(json_encode($array, true));
    } // json_die

    /**
     * @deprecated non PSR-1 standard use jsonDie instead !
     */
    public static function json_die($array)
    {
        self::jsonDie($array);
    } // json_die

#K
    public static function k()
    {
        $aErrors = debug_backtrace();

        $file = 'undefined';
        $line = 'undefined';

        foreach ($aErrors as $error) {
            if (false !== stripos($error['function'], "k") && !empty($error['line']) && !empty($error['file'])) {
                $line = $error['line'];
                $file = $error['file'];
            }
        }

        $start = self::getInstance()->_start;

        $time = microtime(true);
        $total_time = ($time - $start);

        trigger_error("K called @ $file line $line loaded in " . $total_time . " seconds");

        exit;
    } // k
    
    /**
     * Shortest way to ping an url code and retrieve execution time
     *
     * @param $url
     * @return array
     */
    public static function pingUrl($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($handle);
        curl_close($handle);

        return [
            'time' => $info['total_time'],
            'code' => $httpCode,
        ];
    }

    public static function predie()
    {
        $aErrors = debug_backtrace();

        $line =
        $file =
            null;

        foreach ($aErrors as $key => $error) {

            if (preg_match('/predie|ddd|dd|de/i', $error['function']) && !empty($error['line']) && !empty($error['file'])) {
                $line = $error['line'];
                $file = $error['file'];
                break;
            }
        }

        $start = self::getInstance()->_start;

        $time = microtime(true);
        $total_time = ($time - $start);

        die("Predie called @ $file line $line loaded in " . $total_time . " seconds");
    } // predie

    public static function putJson($dest, $value, $type = false){
        return @file_put_contents($dest, json_encode($value));
    }

#R
    public static function randGen($numb = 10, $c = '')
    {
        if (!is_array($c)) {
            $c = json_decode($c, true);
        }

        $chaine = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        if ($c['with'] === 'specialchars') {
            $chaine .= "éâ'`,!('&#$*^";
        }

        if (!empty($c['add'])) {
            $chaine .= $c['add'];
        }

        if (!empty($c['only'])) {
            $chaine = $c['only'];
        }

        return $c['prepend'] . substr(str_shuffle(str_repeat($chaine, $numb)), 0, $numb) . $c['append'];
    } // randGen

    /**
     * @param int $numb
     * @param string|array $c
     * @return string
     */
    public static function randString($numb = 10, $c = '')
    {
        if (!is_array($c)) {
            $c = json_decode($c, true);
        }

        $chaine = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($c['with'] === 'specialchars') {
            $chaine .= "éâ'`,!('&#$*^";
        }

        if (!empty($c['add'])) {
            $chaine .= $c['add'];
        }

        if (!empty($c['only'])) {
            $chaine = $c['only'];
        }

        return $c['prepend'] . substr(str_shuffle(str_repeat($chaine, $numb)), 0, $numb) . $c['append'];
    } // randString

    /**
     * @param int $numb
     * @param string|array $c
     * @return string
     */
    public static function randNum($numb = 10, $c = '')
    {
        if (!is_array($c)) {
            $c = json_decode($c, true);
        }

        $chaine = '0123456789';

        if ($c['with'] == 'specialchars') {
            $chaine .= "éâ'`,!('&#$*^";
        }

        if (!empty($c['add'])) {
            $chaine .= $c['add'];
        }

        if (!empty($c['only'])) {
            $chaine = $c['only'];
        }

        return $c['prepend'] . substr(str_shuffle(str_repeat($chaine, $numb)), 0, $numb) . $c['append'];
    } // randNum

    public static function randEmail($numb = 10, $c = '')
    {
        if (!is_array($c)) {
            $c = json_decode($c, true);
        }

        $chaine = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

        if ($c['with'] === 'specialchars') {
            $chaine .= "éâ'`,!('&#$*^";
        }

        if (!empty($c['add'])) {
            $chaine .= $c['add'];
        }

        if (!empty($c['only'])) {
            $chaine = $c['only'];
        }

        if (!empty($c['domain'])) {
            $domain = $c['domain'];
        } else {
            $domain = substr(str_shuffle(str_repeat($chaine, $numb)), 0, $numb) . '.com';
        }

        return $c['prepend'] . substr(str_shuffle(str_repeat($chaine, $numb)), 0, $numb) . $c['append'] . '@' . $domain;
    } // randEmail

    /**
     * Random an array
     *
     * @param array $array
     * @param array $param
     * @return string
     */
    public static function randArray(array $array, $param = array())
    {
        if (!is_array($param)) {
            $param = json_decode($param, true);
        }

        $defParam = array();

        $param = Arr::merge($defParam, $param);

        # Define the number of element to take
        if (!empty($param['take'])) {
            $response = array();

            for ($i = 1; $i <= $param['take']; $i++) {
                $response []= $array[array_rand($array, 1)];
            }

            return $response;
        } else {
            # Return randomly only 1 element value of the array
            return $array[array_rand($array, 1)];
        }
    } // randArray

    public static function removeSVN($dir){
        return self::removeSubfolder($dir, ".svn");
    }

    public static function removeGit($dir){
        return self::removeSubfolder($dir, ".git");
    }

    public static function removeSubfolder($dir, $subfolder = ".git")
    {
        $out = array();

        $out[] = "Searching: $dir\n\t";

        $flag = false; // haven't found .svn directory
        $target = $dir . $subfolder;

        $isDirTarget = is_dir($target);

        if (is_dir($isDirTarget)) {

            if (!chmod($target, 0777)) {
                $out[] = "File permissions could not be changed (this may or may not be a problem--check the statement below).\n\t"; // if the permissions were already 777, this is not a problem
            }

            self::removeTree($target); // remove the .svn directory with a helper function

            if ($isDirTarget) { // deleting failed
                $out[] = "Failed to delete $target due to file permissions.";
            } else {
                $out[] = "Successfully deleted $target from the file system.";
            }

            $flag = true; // found directory
        }

        if (!$flag) { // no .svn directory
            $out[] = 'No .svn directory found.';
        }

        $out[] = "\n\n";

        $handle = opendir($dir);

        while (false !== ($file = readdir($handle))) {
            if ($file === '.' || $file === '..') { // don't get lost by recursively going through the current or top directory
                continue;
            }

            if (is_dir($dir . $file)) {
                self::removeSubfolder($dir . $file . '/', $subfolder); // apply the SVN removal for sub directories
            }
        }

        return $out;
    } // removeSVN


    public static function removeTree($dir)
    {
        $files = glob($dir . '*', GLOB_MARK); // find all files in the directory

        foreach ($files as $file) {
            if (str_ends_with($file, '/')) {
                self::removeTree($file); // recursively apply this to sub directories
            } else {
                unlink($file);
            }
        }

        if (is_dir($dir)) {
            rmdir($dir); // remove the directory itself (rmdir only removes a directory once it is empty)
        }
    } // removeTree


    public static function resizeImage($image, $width, $height, $scale)
    {
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        $ext = pathinfo($image, PATHINFO_EXTENSION);

        switch ($ext) {
            case ($ext === 'jpg' || $ext === 'jpeg'):
                $source = imagecreatefromjpeg($image);
                break;

            case 'gif':
                $source = imagecreatefromgif($image);
                break;

            case 'png':
                $source = imagecreatefrompng($image);
                break;
        }

        //  $source = imagecreatefromjpeg($image) || imagecreatefromgif($image) || imagecreatefrompng($image);
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
        imagejpeg($newImage, $image, 90);
        chmod($image, 0777);

        return $image;
    } // resizeImage


    /**
     * Quick resize image function
     *
     * @param $thumb_image_name
     * @param $image
     * @param $width
     * @param $height
     * @param $start_width
     * @param $start_height
     * @param $scale
     *
     * @return mixed
     */
    public static function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale)
    {
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        $source = imagecreatefromjpeg($image);
        imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
        imagejpeg($newImage, $thumb_image_name, 90);
        chmod($thumb_image_name, 0777);

        return $thumb_image_name;
    } // resizeThumbnailImage

    /**
     * Check if it's running in a console
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

#S

    /**
     * SendMail
     *
     * @deprected use Mail class instead !
     * @param $recipient
     * @param null $subject
     * @param $html
     * @param null $c
     * @return bool
     */
    public static function sendMail($recipient, $subject = null, $html, $c = null)
    {
        $c = self::toArray($c);
        $headers = 'From: ' . stripslashes($c['exp_nom']) . ' <' . $c['exp_mail'] . '>' . "\r\n";
        $headers .= 'MIME-version: 1.0' . "\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\n";
        $headers .= 'Reply-To: ' . $c['exp_mail'] . "\r\n";
        $success = mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', stripslashes($html), $headers);

        return $success;
    } // sendMail

    /**
     * Fastest template engine ever !
     *
     * @return mixed
     */
    public static function smrtr($haystack, $aMatch, $aDelimiter = array("{","}")) {
        return Str::smrtr($haystack, $aMatch, $aDelimiter);
    } // smrtr

    /**
     * Transform any value to Array
     *
     * @param mixed $mValue
     * @return mixed
     */
    public static function toArray($mValue){
        return Arr::toArray($mValue);
    }
} // class::Utils
