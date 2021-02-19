<?php

namespace DD\Client\SilverStripe\Extension\GoogleSitemap;

use DD\Client\SilverStripe\Client;
use DD\Client\SilverStripe\Model\Listing\Listing;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;
use Wilr\GoogleSitemaps\Control\GoogleSitemapController;
use Wilr\GoogleSitemaps\GoogleSitemap;

class Controller extends GoogleSitemapController
{
    private static $allowed_actions = [
        'index',
        'sitemap',
        'styleSheetIndex',
        'styleSheet'
    ];

    public function index($url)
    {
        if (GoogleSitemap::enabled()) {
            $this->getResponse()->addHeader('Content-Type', 'application/xml; charset="utf-8"');
            $this->getResponse()->addHeader('X-Robots-Tag', 'noindex');

            $sitemaps = GoogleSitemap::inst()->getSitemaps();
            $this->invokeWithExtensions('updateGoogleSitemaps', $sitemaps);

            return $this->customise(new ArrayData([
                'Sitemaps' => $sitemaps
            ]))->renderWith(GoogleSitemapController::class);
        } else {
            return new HTTPResponse('Page not found', 404);
        }
    }

    public function updateGoogleSitemaps(ArrayList $sitemaps)
    {
        $sitemaps->push(ArrayData::create([
            'ClassName' => $this->sanitiseClassName(Listing::class),
            'Page' => 1
        ]));
    }

    public function sitemap()
    {
        $class = $this->unsanitiseClassName($this->request->param('ID'));
        if ($class == Listing::class) {
            $items = Client::inst()->searchListings(
                null,
                null,
                '',
                null,
                null,
                null,
                99999
            );
            return array(
                'Items' => $items->getIterator()
            );
        }
        return parent::sitemap();
    }

    protected function sanitiseClassName($class)
    {
        return str_replace('\\', '-', $class);
    }
}
