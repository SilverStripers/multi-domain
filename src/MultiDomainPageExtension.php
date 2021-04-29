<?php

namespace SilverStripe\MultiDomain;

use SilverStripe\ORM\DataExtension;

class MultiDomainPageExtension extends DataExtension
{
    public function updateRelativeLink(&$base, &$action)
    {
        $domains = MultiDomain::config()->domains;
        if (
            isset($_SERVER['SERVER_NAME'])
            && ($serverName = $_SERVER['SERVER_NAME'])
            && ($primaryDomain = $domains['primary'])
            && $primaryDomain['hostname'] != $serverName
        ) {
            foreach ($domains as $domainKey => $domain) {
                $arrNameParts = explode('.', $serverName);
                if (
                    $serverName == $domain['hostname']
                    && ($resolvesTo = $domain['resolves_to'])
                ) {
                    $base = ltrim($base, $resolvesTo);
                    break;
                }
            }
        }
    }
}
