<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\RegionParser as SS_RegionParser;
use DD\Client\Core\Parsers\Parser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class RegionParser extends SS_RegionParser
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
