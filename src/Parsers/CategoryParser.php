<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\CategoryParser as SS_CategoryParser;
use DD\Client\Core\Parsers\Parser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class CategoryParser extends SS_CategoryParser
{
	public function __construct()
	{
		$this->iterator = new ArrayList();
	}

	public function parse($items)
	{
		if (is_array($items)) {
			foreach ($items as $item) {
				if (!empty($item['node'])) {
					$item = $item['node'];
				}
				$children = null;
				if (!empty($item['Children'])) {
					$childrenParser = Parser::get_parser_for(CategoryParser::class);
					$children = $childrenParser->parse($item['Children']['edges']);
				}
				unset($item['Children']);
				$item = ArrayData::create($item);
				$item->Children = $children;
				$this->iterator->push($item);
			}
		}
		return $this->iterator;
	}
}
