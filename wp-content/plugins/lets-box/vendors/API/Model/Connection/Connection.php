<?php

/**
 * @author      Chance Garcia
 * @copyright   (C)Copyright 2013 chancegarcia.com
 *
 * connection assumes a valid access token
 */

namespace Box\Model\Connection;

use Box\Exception\Exception;
use Box\Model\Model;

/**
 * Class Connection.
 *
 * @todo add in method to access last curl info, error and error number for debugging
 */
class Connection extends Model implements ConnectionInterface
{
    public const CONNECTION_ESTABLISHED = "HTTP/1.0 200 Connection established\r\n\r\n";

    protected $responseType = 'code';
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $state;
    protected $requestType = 'GET';
    protected $response;
    protected $responseClass = 'Box\Model\Connection\Response';
    protected $followlocation = true;
    protected $gzip = false;

    /**
     * @var array array of options with the options as the key and the option values as the value
     */
    protected $curlOpts = [];
    private $handler;

    // relooking over auth flow, we have to assume app is already authorized externally. rewrite to use tokens for connection
    // may need to store the tokens
    public function connect()
    {
    }

    public function enableGzip()
    {
        $this->gzip = true;
    }

    public function canGzip()
    {
        return $this->gzip;
    }

    public function getHandler()
    {
        // if (empty($this->handler)){
        $this->handler = curl_init();
        // }

        return $this->handler;
    }

    /**
     * @return resource
     */
    public function initCurl()
    {
        $ch = $this->getHandler();

        $this->initCurlOpts($ch);

        return $ch;
    }

    public function initCurlOpts($ch)
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // Cannot use Followlocation if safe_mode = on, or an open_base dir is set
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->getFollowLocation());
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');

        // Force TLS 1.1 or higher, required as of June 15, 2018
        if (defined('CURL_SSLVERSION_TLSv1_2')) {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        } else {
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
        }

        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/cacerts.pem');

        if ($this->canGzip()) {
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        }

        return $ch;
    }

    /**
     * @param mixed $ch
     *
     * @return mixed
     */
    public function getCurlData($ch)
    {
        return curl_exec($ch);
    }

    public function getCurlFileValue($file)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($file->tmp_path, $file->type, $file->name);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$file->tmp_path};filename=".$file->name;
        if ($file->type) {
            $value .= ';type='.$file->type;
        }

        return $value;
    }

    public function initAdditionalCurlOpts($ch)
    {
        $opts = $this->getCurlOpts();
        if (0 != count($opts)) {
            foreach ($opts as $opt => $optValue) {
                // CURLOPT_HTTPHEADER, CURLOPT_QUOTE, CURLOPT_HTTP200ALIASES and CURLOPT_POSTQUOTE require array or object arguments

                switch ($opt) {
                    case 'CURLOPT_HTTPHEADER':
                    case 'CURLOPT_QUOTE':
                    case 'CURLOPT_HTTP200ALIASES':
                    case 'CURLOPT_POSTQUOTE':
                        // throw exception so it doesn't throw a warning
                        if (!is_array($optValue)) {
                            $this->error(
                                [
                                    'error' => 'curl opt ('.$opt.') needs to be an array or object',
                                    'error_description' => 'curl opt ('.$opt.') needs to be an array or object',
                                ]
                            );
                        }
                        curl_setopt($ch, constant($opt), $optValue);

                        break;

                    default:
                        curl_setopt($ch, constant($opt), $optValue);

                        break;
                }
            }
        }

        return $ch;
    }

    public function doCurlRequest($ch)
    {
        // Uncomment code to debug communication if needed
        // file_put_contents(LETSBOX_CACHEDIR.'api.log', "\r\n\r\n".' ***********'.date('c')."************* \r\n", FILE_APPEND);

        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // $this->verbose = fopen('php://temp', 'w+');
        // curl_setopt($ch, CURLOPT_STDERR, $this->verbose);

        $response = $this->getCurlData($ch);

        // rewind($this->verbose);
        // $verboseLog = stream_get_contents($this->verbose);
        // file_put_contents(LETSBOX_CACHEDIR.'api.log', $verboseLog, FILE_APPEND);

        // extract(curl_getinfo($ch));
        // $metrics = <<<EOD
        // URL....: {$url}
        // Code...: {$http_code} ({$redirect_count} redirect(s) in {$redirect_time} secs)
        // Content: {$content_type} Size: {$download_content_length} (Own: {$size_download}) Filetime: {$filetime}
        // Time...: {$total_time} Start @ {$starttransfer_time} (DNS: {$namelookup_time} Connect: {$connect_time} Request: {$pretransfer_time})
        // Speed..: Down: {$speed_download} (avg.) Up: {$speed_upload} (avg.)
        // EOD;
        // file_put_contents(LETSBOX_CACHEDIR.'api.log', $metrics, FILE_APPEND);

        // Get Body & Header
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        list($responseHeaders, $responseBody) = $this->parseHttpResponse($response, $headerSize);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check Rate Limiting
        if (429 === $responseCode) {
            if (isset($responseHeaders['Retry-After'])) {
                // Wait 1.5 times the requested retry time
                usleep($responseHeaders['Retry-After'] * 1500000);

                // Try again
                $response = $this->getCurlData($ch);

                // Get Body & Header
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                list($responseHeaders, $responseBody) = $this->parseHttpResponse($response, $headerSize);
                $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            }
        }
        curl_close($ch);

        if (isset($responseHeaders['headers']['location']) && true === $this->followlocation) {
            return $this->query($responseHeaders['headers']['location']);
        }

        return ['body' => $responseBody, 'headers' => $responseHeaders, 'code' => $responseCode];
    }

    /**
     * GET.
     *
     * @param mixed $uri
     *
     * @return mixed
     */
    public function query($uri)
    {
        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    public function delete($uri, $params = [], $nameValuePair = false)
    {
        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        if ($nameValuePair) {
            $params = json_encode($params);
        }

        if (is_array($params)) {
            $postParams = $this->buildQuery($params);
        } else {
            $postParams = $params;
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    public function put($uri, $params = [], $nameValuePair = false)
    {
        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        if ($nameValuePair) {
            $params = json_encode($params);
        }

        if (is_array($params)) {
            $postParams = $this->buildQuery($params);
        } else {
            $postParams = $params;
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    public function options($uri, $params = [], $nameValuePair = false)
    {
        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');

        if ($nameValuePair) {
            $params = json_encode($params);
        }

        if (is_array($params)) {
            $postParams = $this->buildQuery($params);
        } else {
            $postParams = $params;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    /**
     * POST.
     *
     * @param array|string $params        will convert array to string
     * @param bool         $nameValuePair
     * @param mixed        $uri
     *
     * @return mixed
     */
    public function post($uri, $params = [], $nameValuePair = false)
    {
        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);

        if ($nameValuePair) {
            $params = json_encode($params);
        }

        if (is_array($params)) {
            $postParams = $this->buildQuery($params);
        } else {
            $postParams = $params;
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    public function postFile($uri, $file, $parentId = 0)
    {
        // @todo allow Content-MD5 header to be set
        // Post 1-n files, each element of $files array assumed to be absolute
        // path to a file.  $files can be array (multiple) or string (one file).
        // Data will be posted in a series of POST vars named $file0, $file1...
        // $fileN
        $data = [
            'filename' => $this->getCurlFileValue($file),
            'parent_id' => $parentId,
        ];

        $ch = $this->initCurl();
        $ch = $this->initCurlOpts($ch);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $ch = $this->initAdditionalCurlOpts($ch);

        return $this->doCurlRequest($ch);
    }

    /**
     * @param array $curlOpts
     *
     * @return Connection|ConnectionInterface
     */
    public function setCurlOpts($curlOpts = null)
    {
        if (!is_array($curlOpts)) {
            $curlOpts = [$curlOpts];
        }
        $this->curlOpts = $curlOpts;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurlOpts()
    {
        return $this->curlOpts;
    }

    public function setResponseClass($responseClass = null)
    {
        $this->validateClass($responseClass, 'ResponseInterface');
        $this->responseClass = $responseClass;

        return $this;
    }

    public function getResponseClass()
    {
        return $this->responseClass;
    }

    public function setClientId($clientId = null)
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientSecret($clientSecret = null)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setRedirectUri($redirectUri = null)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setRequestType($requestType = null)
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getRequestType()
    {
        return $this->requestType;
    }

    public function setResponse($response = null)
    {
        $this->response = $response;

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponseType($responseType = null)
    {
        $this->responseType = $responseType;

        return $this;
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function setFollowLocation($followlocation = true)
    {
        $this->followlocation = $followlocation;

        return $this;
    }

    public function getFollowLocation()
    {
        return $this->followlocation;
    }

    public function setState($state = null)
    {
        $this->state = $state;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function parseHttpResponse($respData, $headerSize)
    {
        if (false !== stripos($respData, self::CONNECTION_ESTABLISHED)) {
            $respData = str_ireplace(self::CONNECTION_ESTABLISHED, '', $respData);
        }

        if ($headerSize) {
            $responseBody = substr($respData, $headerSize);
            $responseHeaders = substr($respData, 0, $headerSize);
        } else {
            list($responseHeaders, $responseBody) = explode("\r\n\r\n", $respData, 2);
        }

        $responseHeaders = $this->getHttpResponseHeaders($responseHeaders);

        return [$responseHeaders, $responseBody];
    }

    /**
     * Parse out headers from raw headers.
     *
     * @param rawHeaders array or string
     * @param mixed $rawHeaders
     *
     * @return array
     */
    public function getHttpResponseHeaders($rawHeaders)
    {
        if (is_array($rawHeaders)) {
            return $this->parseArrayHeaders($rawHeaders);
        }

        return $this->parseStringHeaders($rawHeaders);
    }

    private function parseStringHeaders($rawHeaders)
    {
        $headers = [];
        $responseHeaderLines = explode("\r\n", $rawHeaders);
        foreach ($responseHeaderLines as $headerLine) {
            if ($headerLine && false !== strpos($headerLine, ':')) {
                list($header, $value) = explode(': ', $headerLine, 2);
                $header = strtolower($header);
                if (isset($responseHeaders[$header])) {
                    $headers[$header] .= "\n".$value;
                } else {
                    $headers[$header] = $value;
                }
            }
        }

        return array_change_key_case($headers, CASE_LOWER);
    }

    private function parseArrayHeaders($rawHeaders)
    {
        $header_count = count($rawHeaders);
        $headers = [];

        for ($i = 0; $i < $header_count; ++$i) {
            $header = $rawHeaders[$i];
            // Times will have colons in - so we just want the first match.
            $header_parts = explode(': ', $header, 2);
            if (2 == count($header_parts)) {
                $headers[$header_parts[0]] = $header_parts[1];
            }
        }

        return array_change_key_case($headers, CASE_LOWER);
    }
}