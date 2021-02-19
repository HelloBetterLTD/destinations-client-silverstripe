<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\OccurrenceParser as SS_OccurrenceParser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class OccurrenceParser extends SS_OccurrenceParser
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
