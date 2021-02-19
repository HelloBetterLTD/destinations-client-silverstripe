<?php

namespace DD\Client\SilverStripe\Controller;

use DD\Client\SilverStripe\Client;
use PageController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\CMS\Search\SearchForm;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class SearchPageController extends PageController
{
    private static $allowed_actions = [
        'listings',
    ];

    /**
     * Type of search to use
     * 'fulltext': Standard SS full text search
     * 'elemental-search': Elemental search module
     *
     * @var string
     * @config
     */
    private static $search_type = 'fulltext';

    /**
     * Should the total number of results (from both pages and listings) be
     * added to the search results data. The option to disable this has been
     * added because it will result in a call to the Destinations API even when only
     * page results are displayed.
     *
     * @var boolean
     */
    private static $add_total_results_count = true;

    /**
     * Classes to use for search. Should be overridden to SearchDocument for
     * elemental search
     *
     * @var array
     */
    private static $search_classes = [];

    /**
     * @var SearchForm
     */
    protected $searchForm;

    protected $page_length;

    public function __construct($dataRecord = null)
    {
        parent::__construct($dataRecord);

        $this->dataRecord->MetaTitle = $this->MetaTitle();
    }

    protected function init() {
        parent::init();

        $vars = $this->getRequest()->requestVars();
        if (!array_key_exists('Search', $vars) || !$vars['Search']) {
            return $this->httpError('404', 'Page Not Found');
        }

    }

    public function Link($action = null)
    {
        return Controller::join_links('search', $action);
    }

    public function index(HTTPRequest $request)
    {
        return $this->customise($this->getSearchedData())->renderWith(['SearchPage', 'Page']);
    }

    public function listings(HTTPRequest $request)
    {
        return $this->customise($this->getSearchedData())->renderWith(['SearchPage', 'Page']);
    }

    public function getSearchedData()
    {
        $form = $this->searchForm();

        $this->page_length = $form->getPageLength();

        $results = [
            'SearchResults' => $this->getAction() == 'listings' ? $this->getListingResults() : $this->getPageResults(),
            'Query' => $form->getSearchQuery(),
            'ListingsTabOpened' => $this->getAction() == 'listings',
            'Title' => 'Search Results'
        ];

        if ($this->config()->get('add_total_results_count')) {
            $supplementaryResults = $this->owner->getAction() == 'listings'
                ? $this->owner->getPageResults()
                : $this->owner->getListingResults();
            if($results['SearchResults']) {
                $results['TotalCount'] = $results['SearchResults']->Count() + $supplementaryResults->Count();
            }
        }

        $this->extend('updateSearchResults', $results);

        return $results;
    }

    public function getPageResults()
    {
        return $this->searchForm()->getResults();
    }

    /**
     * Search Destinations Listings by keyword
     *
     * @return PaginatedList
     */
    public function getListingResults()
    {
        $pageLength = $this->page_length;
        $start = $this->getRequest()->requestVar('start') ?: 0;
        $listingsData = Client::inst()->fulltextSearchListings(
            $this->getRequest()->requestVar('Search'),
            $start,
            $pageLength
        );

        if (count($listingsData->getIterator()) > 0) {
            $listings = ArrayList::create();
            foreach ($listingsData->getIterator() as $listing) {
                $listings->push($listing);
            }

            // To match standard search results
            $list = new PaginatedList($listings);
            $list->setPageStart($start);
            $list->setPageLength($pageLength);
            $list->setTotalItems($listingsData->getTotalCount());

            // The list has already been limited by the query above
            $list->setLimitItems(false);
            return $list;
        }
        return null;
    }

    /**
     * Link to stadard page results
     *
     * @return string
     */
    public function PageTabLink()
    {
        $vars = $this->getRequest()->requestVars();
        return Controller::join_links($this->Link(), '?Search=' . rawurlencode($vars['Search']));
    }

    /**
     * Link to listings results
     *
     * @return string
     */
    public function ListingsTabLink()
    {
        $vars = $this->getRequest()->requestVars();
        return Controller::join_links($this->Link(), 'listings', '?Search=' . rawurlencode($vars['Search']));
    }

    /**
     * Page title
     *
     * @return string
     */
    public function MetaTitle() {
        return 'Search Results | '. SiteConfig::current_site_config()->Title;
    }

    protected function searchForm()
    {
        if ($this->searchForm === null) {
            $this->searchForm = SearchForm::create($this, 'searchForm');
            $this->searchForm->classesToSearch($this->config()->get('search_classes'));
        }
        return $this->searchForm;
    }
}
