<?php
/**
 * Created by priyashantha@silverstripers.com
 * Date: 12/4/18
 * Time: 11:01 AM
 */

namespace SilverStripe\MultiDomain;

use SilverStripe\CMS\Controllers\ModelAsController;
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
        foreach (MultiDomain::get_all_domains() as $domain) {
            if (!$domain->isActive()) {
                continue;
            }

            $url = $this->createNativeURLForDomain($domain, $request);
            $parts = explode('?', $url);
            $request->setURL($parts[0]);
            $request->match('$URLSegment//$Action//$ID/$OtherID');
        }
        return $next($request);
    }

    /**
     * Creates a native URL for a domain. This functionality is abstracted so
     * that other modules can overload it, e.g. translatable modules that
     * have their own custom URLs.
     *
     * @param  MultiDomainDomain $domain
     * @return string
     */
    protected function createNativeURLForDomain(MultiDomainDomain $domain, HTTPRequest $request)
    {
        $requestURI = $_SERVER['REQUEST_URI'];
        if(!$this->isDirectorRoute($request)) {
            $requestURI = Controller::join_links(
                Director::baseURL(),
                $domain->getNativeURL($domain->getRequestUri())
            );
        }
        return $requestURI;
    }

    protected function isDirectorRoute(HTTPRequest $request)
    {
        return MultiDomainMiddleware::is_director_route($request);
    }

    public static function is_director_route(HTTPRequest $request)
    {
        $rules = Director::config()->get('rules');
        $matchedPattern = null;
        foreach($rules as $pattern => $controllerOptions) {
            if(($arguments = $request->match($pattern)) !== false) {
                $matchedPattern = $controllerOptions;
                break;
            }
        }
        return $matchedPattern != ModelAsController::class;
    }



}
