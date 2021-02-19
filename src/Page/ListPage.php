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
		'Categories' => 'Text',
	];

	private static $singular_name = 'Destinations List Page';

	private static $plural_name = 'Destinations List Pages';

	private static $default_url = 'explore';

	private static $table_name = 'ListPage';

	private static $controller_name = ListPageController::class;

	private $isEventCache = null;

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$client = Client::inst();

		$categories = $client->getCategories(true, null, true);
		if ($categories && $categories->count()) {
			$fields->addFieldsToTab('Root.Main', [
                HierarchicalCheckboxSetField::create('Categories', 'Categories', $categories),
//				TextField::create(
//					'Categories', 'Categories'
//				),
			], 'Content');
		}

		return $fields;
	}

    public function IsEventsPage()
    {
        if(is_null($this->isEventCache)) {
            $client = Client::inst();
            if ($this->Categories && ($ids = json_decode($this->Categories)) && count($ids) == 1) {
                $categoryDetails = $client->getCategory($ids[0], false, true);
                if ($categoryDetails) {
                    $this->isEventCache = $categoryDetails->IsEventCategory == 1;
                }
            }
        }
        return $this->isEventCache;
    }

	public function updateMetaTags(&$tags)
	{
		$metaTags = [
			'host' => Client::config()->get('destinations_endpoint'),
			'pageTitle' => $this->Title,
			'categories' => $this->Categories ? json_decode($this->Categories, true) : [],
            'eventsPage' => $this->IsEventsPage()
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
