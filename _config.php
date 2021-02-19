<?php

use DD\Client\Core\Parsers\Parser;
use DD\Client\Core\Parsers\CategoryParser as SS_CategoryParser;
use DD\Client\Core\Parsers\EventParser as SS_EventParser;
use DD\Client\Core\Parsers\ListingParser as SS_ListingParser;
use DD\Client\Core\Parsers\ListingsParser as SS_ListingsParser;
use DD\Client\Core\Parsers\ListingTagsParser as SS_ListingTagsParser;
use DD\Client\Core\Parsers\LocationParser as SS_LocationParser;
use DD\Client\Core\Parsers\PaginatedListingsParser as SS_PaginatedListingsParser;
use DD\Client\Core\Parsers\RegionParser as SS_RegionParser;
use DD\Client\Core\Parsers\OccurrenceParser as SS_OccurrenceParser;

use DD\Client\SilverStripe\Controller\SearchPageController;
use DD\Client\SilverStripe\Parsers\CategoryParser;
use DD\Client\SilverStripe\Parsers\EventParser;
use DD\Client\SilverStripe\Parsers\ListingParser;
use DD\Client\SilverStripe\Parsers\ListingsParser;
use DD\Client\SilverStripe\Parsers\ListingTagsParser;
use DD\Client\SilverStripe\Parsers\LocationParser;
use DD\Client\SilverStripe\Parsers\PaginatedListingsParser;
use DD\Client\SilverStripe\Parsers\RegionParser;
use DD\Client\SilverStripe\Parsers\OccurrenceParser;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\Search\FulltextSearchable;

Parser::register_parser(SS_CategoryParser::class, CategoryParser::class);
Parser::register_parser(SS_EventParser::class, EventParser::class);
Parser::register_parser(SS_ListingParser::class, ListingParser::class);
Parser::register_parser(SS_ListingsParser::class, ListingsParser::class);
Parser::register_parser(SS_ListingTagsParser::class, ListingTagsParser::class);
Parser::register_parser(SS_LocationParser::class, LocationParser::class);
Parser::register_parser(SS_PaginatedListingsParser::class, PaginatedListingsParser::class);
Parser::register_parser(SS_RegionParser::class, RegionParser::class);
Parser::register_parser(SS_OccurrenceParser::class, OccurrenceParser::class);

// enable fulltext search if configured
$searchType = Config::inst()->get(SearchPageController::class, 'search_type');
if ($searchType === 'fulltext') {
    FulltextSearchable::enable();
}
