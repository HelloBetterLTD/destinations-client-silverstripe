<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\ListingTagsParser as SS_ListingTagsParser;
use DD\Client\Core\Parsers\Parser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class ListingTagsParser extends SS_ListingTagsParser
{
	public function __construct()
	{
		$this->iterator = new ArrayList();
	}


	public function parse($items)
	{
		if ($items && is_array($items)) {
			foreach ($items as $item) {
				$this->iterator->push(ArrayData::create($item));
			}
		}
		return $this->iterator;
	}
}
