<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\LocationParser as SS_LocationParser;
use DD\Client\Core\Parsers\Parser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class LocationParser extends SS_LocationParser
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
