<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\Parser;
use DD\Client\SilverStripe\Model\Listing\Listing;
use SilverStripe\ORM\ArrayList;

class ListingsParser extends ListingParser
{
    public function __construct()
    {
        $this->iterator = new ArrayList();
    }

    public function parse($items)
    {
        if ($items && is_array($items)) {
            foreach ($items as $item) {
                $this->iterator->push(Listing::create($this->parserData($item)));
            }
        }
        return $this->iterator;
    }
}
