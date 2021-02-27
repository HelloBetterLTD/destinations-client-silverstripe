<?php

namespace DD\Client\SilverStripe\Page;

use DD\Client\SilverStripe\Client;
use DD\Client\SilverStripe\Page\ListPageController;
use DD\Client\SilverStripe\Model\Page;
use DD\Client\SilverStripe\Forms\HierarchicalCheckboxSetField;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\HTML;

class ListPage extends \Page
{
	private static $db = [
	    'ResultsTitle' => 'Varchar(300)',
        'IsEventPage' => 'Boolean',
        'IsAccommodationPage' => 'Boolean',
		'CategoryIDs' => 'Text',
	];

	private static $singular_name = 'Destinations List Page';

	private static $plural_name = 'Destinations List Pages';

	private static $default_url = 'explore';

	private static $table_name = 'ListPage';

	private static $controller_name = ListPageController::class;

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->insertAfter(
		    'MenuTitle',
            TextField::create('ResultsTitle')->setDescription('Please use merge tag {$ResultsCount} to display results count in title. eg: "{$ResultsCount} activities to discover"')
        );

		$client = Client::inst();

		$categories = $client->getCategories(null, true, null, true);
		if ($categories && $categories->count()) {
			$fields->addFieldsToTab('Root.Main', [
                HierarchicalCheckboxSetField::create('CategoryIDs', 'Categories', $categories)
                    ->setValueField('Identifier'),
			], 'Content');
		}

		$this->extend('updateListPageCMSFields', $fields);

		return $fields;
	}

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->isChanged('CategoryIDs', 2)) {
            $isEventPage = false;
            $isAccommodationPage = false;
            if ($this->CategoryIDs && ($ids = json_decode($this->CategoryIDs))) {
                $client = Client::inst();
                $categories = $client->getCategories($ids);
                $isEventPage = $categories->count() && !!$categories->find('IsEventCategory', true);
                $isAccommodationPage = $categories->count() && !!$categories->find('IsAccommodationCategory', true);
            }
            $this->IsEventPage = $isEventPage;
            $this->IsAccommodationPage = $isAccommodationPage;
        }
    }

    public function getSettings()
    {
        $list = [];
        if ($this->CategoryIDs) {
            $list['categories'] = json_decode($this->CategoryIDs, true);
        }
        $list['endpoint'] = Client::config()->get('destinations_endpoint');
        $list['resultsTitle'] = $this->ResultsTitle;
        $list['hasEvents'] = $this->IsEventPage;
        $list['hasAccommodations'] = $this->IsAccommodationPage;
        $this->extend('updateSettings', $list);
        return Convert::raw2htmlatt(json_encode($list));
    }

	public function updateMetaTags(&$tags)
	{
		$metaTags = [
			'host' => Client::config()->get('destinations_endpoint'),
			'pageTitle' => $this->Title,
			'categories' => $this->Categories ? json_decode($this->Categories, true) : [],
            'eventsPage' => $this->IsEventPage
		];
		$tags[] = HTML::createTag('meta', [
			'name' => '_Destinations',
			'id' => '_Destinations',
			'content' => json_encode($metaTags)
		]);
	}

	public function getEndpoint()
    {
        return Client::config()->get('destinations_endpoint');
    }

    public static function default_to_page()
    {
        $urlSegment = self::config()->get('default_url');
        if($default = ListPage::get()->filter([
            'URLSegment' => $urlSegment,
        ])->first()) {
            return $default;
        }

        if($listPage = ListPage::get_one(ListPage::class)) {
            return $listPage;
        }

        return null;
    }
}
