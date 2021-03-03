<?php

namespace DD\Client\SilverStripe\Page;

use DD\Client\SilverStripe\Client;
use DD\Client\SilverStripe\Model\Listing\Listing;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;

class ListPageController extends \PageController
{
	private static $allowed_actions = [
		'listing'
	];

	private static $url_handlers = [
		'listing/$Segment!' => 'listing',
	];

	private $listing_cache = null;

	public function index()
	{
		return $this;
	}

	public function listing()
	{
	    /** @var Listing $listing */
		if ($listing = $this->getListing()) {
			return $this->customise([
				'Listing' => $listing,
				'Title' => $listing->Title,
			]);
		}
		return $this->httpError('404');
	}

	public function getListing()
	{
		if ($this->listing_cache) {
			return $this->listing_cache;
		}
		$segment = $this->request->param('Segment');
		$stage = Versioned::LIVE;
		$client = Client::inst();
		if ($listing = $client->getListing($segment)) {
			$this->listing_cache = $listing;
            $this->extend('updateListing', $listing);
        }
		return $this->listing_cache;
	}
}
