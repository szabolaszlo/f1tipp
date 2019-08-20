<?php

namespace App\Controller\Module\AutoDownloadFaceCoverPhoto;

use App\Controller\Controller;
use Entity\Setting;

/**
 * Class AutoDownloadFaceCoverPhoto
 * @package App\Controller\Module\AutoDownloadFaceCoverPhoto
 */
class AutoDownloadFaceCoverPhoto extends Controller
{
    const URL = 'https://www.facebook.com/Boxutca/';

    public function indexAction()
    {
    }

    /**
     * @return string|void
     */
    public function findAction()
    {
        $sourceCode = $this->getWebPage(self::URL);

        preg_match_all('/"coverPhotoData":{.*?{.*?{.*?},"uri":"(.*?)"/m', $sourceCode['content'], $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            if(isset($match[1])){
                $img = str_replace('/', '', $match[1]);
                echo $img . PHP_EOL;
                $this->entityManager
                    ->getRepository(Setting::class)
                    ->setKeyValue('faceCoverImage', $img);
            }
        }
        echo 'OK';
        exit;
    }


    /**
     * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
     * array containing the HTTP server response header fields and content.
     */
    public function getWebPage($url)
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST => "GET",        //set request type post or get
            CURLOPT_POST => false,        //set to GET
            CURLOPT_USERAGENT => $user_agent, //set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }
}