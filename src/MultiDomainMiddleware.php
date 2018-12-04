<?php
/**
 * Created by priyashantha@silverstripers.com
 * Date: 12/4/18
 * Time: 11:01 AM
 */

namespace SilverStripe\MultiDomain;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPMiddleware;

class MultiDomainMiddleware implements HTTPMiddleware
{
    /**
     * Generate response for the given request
     *
     * @param HTTPRequest $request
     * @param callable $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $next)
    {
        // If flush, bypass caching completely in order to delegate to Silverstripe's flush protection
        if ($request->offsetExists('flush')) {
            return $next($request);
        }

        foreach (MultiDomain::get_all_domains() as $domain) {
            if (!$domain->isActive()) {
                continue;
            }

            $url = $this->createNativeURLForDomain($domain);
//            echo '<pre>'.print_r($request->getURL(), 1);die();
            $parts = explode('?', $url);
            $request->setURL($parts[0]);
            $request->match('$URLSegment//$Action//$ID/$OtherID');
        }
        return $next($request);
//        echo $request->getURL();die();
    }

    /**
     * Creates a native URL for a domain. This functionality is abstracted so
     * that other modules can overload it, e.g. translatable modules that
     * have their own custom URLs.
     *
     * @param  MultiDomainDomain $domain
     * @return string
     */
    protected function createNativeURLForDomain(MultiDomainDomain $domain)
    {
        return Controller::join_links(
            Director::baseURL(),
            $domain->getNativeURL($domain->getRequestUri())
        );
    }
}