<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\PaginatedListingsParser as SS_PaginatedListingsParser;
use DD\Client\Core\Parsers\Parser;
use SilverStripe\ORM\ArrayList;

class PaginatedListingsParser extends SS_PaginatedListingsParser
{
	public function __construct()
	{
		$this->iterator = new ArrayList();
	}
}