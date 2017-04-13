<?php

namespace Ampersa\SURBL;

use League\Uri\Schemes\Http;
use League\Uri\Components\Host;

class SURBL
{
    const LIST_PH     = 0b00001000;
    const LIST_MW     = 0b00010000;
    const LIST_ABUSE  = 0b01000000;
    const LIST_CR     = 0b10000000;

    const LOOKUP_URL  = '%s.multi.surbl.org';

    /** @var int */
    protected $options;

    /**
     * Setup an instance and set options
     * @param int $options  A bitmask of options for lookups
     */
    public function __construct(int $options = self::LIST_PH | self::LIST_MW | self::LIST_ABUSE | self::LIST_CR)
    {
        $this->options = $options;
    }

    /**
     * Returns boolean whether $url is listed at SURBL
     * @param  string      $url     The URL to check
     * @return bool
     */
    public function listed(string $url) : bool
    {
        // If a local 127.x.x.x IP is provided as the URL, assume we're testing
        if ($this->validateIpAddress($url)) {
            return $this->checkResult($url);
        }

        // Retrieve the root domain name from the URL
        $parsedUrl = $this->extractDomainFromUrl($url);

        // Get the result from surbl.org via DNS lookup
        $result = gethostbyname(sprintf(self::LOOKUP_URL, $parsedUrl));

        // If there's no domain, simply return negative
        if (empty($result)) {
            return false;
        }

        // gethostbyname() returns the input on NXDOMAIN, so return negative
        if ($result == $parsedUrl) {
            return false;
        }

        // If the returned data is not an IP address, return negative
        if (!$this->validateIpAddress($result)) {
            return false;
        }

        return $this->checkResult($result);
    }

    /**
     * Extract and return the domain from a URL using League\Uri
     * @param  string $url
     * @return string
     */
    protected function extractDomainFromUrl(string $url) : string
    {
        $uri = Http::createFromString($url);

        $host = new Host($uri->getHost());

        return $host->getRegisterableDomain();
    }

    /**
     * Validate a returned or provided IP address
     * @param  string $ip
     * @return bool
     */
    protected function validateIpAddress(string $ip) : bool
    {
        return (filter_var($ip, FILTER_VALIDATE_IP) and preg_match('/127\.0\.0\.\d/', $ip));
    }

    /**
     * Check the result returned from SURBL by using the
     * last octet of the IP address against the options
     * @param  string $result
     * @return bool
     */
    protected function checkResult(string $result) : bool
    {
        $lastOctet = explode('.', $result)[3];

        if (!$lastOctet or empty($lastOctet)) {
            return false;
        }

        // Check for Phishing sites list
        if ($this->options & self::LIST_PH) {
            if ($lastOctet & self::LIST_PH) {
                return true;
            }
        }

        // Check for Malware sites list
        if ($this->options & self::LIST_MW) {
            if ($lastOctet & self::LIST_MW) {
                return true;
            }
        }

        // Check for AbuseButler list
        if ($this->options & self::LIST_ABUSE) {
            if ($lastOctet & self::LIST_ABUSE) {
                return true;
            }
        }

        // Check for Cracked sites list
        if ($this->options & self::LIST_CR) {
            if ($lastOctet & self::LIST_CR) {
                return true;
            }
        }

        return false;
    }

    /**
     * Static accessor for listed()
     * @return bool
     */
    public static function isListed(string $url, int $options = self::LIST_PH | self::LIST_MW | self::LIST_ABUSE | self::LIST_CR) : bool
    {
        $instance = new static($options);
        return $instance->listed($url);
    }
}
