<?php

namespace DD\Client\SilverStripe\Model\Listing;

use DD\Client\SilverStripe\Client;
use DD\Client\SilverStripe\Model\ImageCached;
use DD\Client\SilverStripe\Page\ListPage;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class Listing extends ArrayData
{
    public function getLink()
    {
        $page = ListPage::get()->first();
        return $page ? Controller::join_links($page->Link(), 'listing', $this->URLSlug) : '';
    }

    public function AbsoluteLink()
    {
        return Director::absoluteURL($this->getLink());
	}

    public function ChangeFrequency()
    {
        return 'Daily';
    }

    public function GooglePriority()
    {
        return 1;
    }

	public function getNearByListings($limit = 3)
	{
        $excludeCategories = null;

        if (Config::inst()->get(Client::class, 'exclude_categories_for_nearby')) {
			$excludeCategories = array_merge(
				[$this->MainCategory->ID],
				$this->Categories->column('ID')
			);
		}

		if ($this->Latitude && $this->Longitude) {
            $client = Client::inst();
			$list = $client->searchListings(
                null,
				null,
				'',
				null,
				null,
				null,
				$limit,
                $this->Latitude,
                $this->Longitude,
                2000,
                $this->ID,
				$excludeCategories
			);
			return $list ? $list->getIterator() : null;
		}
	}

	public function getRelatedListings($limit = 4)
	{
		$mainCategoryID = $this->MainCategory ? $this->MainCategory->ID : 0;
		if ($mainCategoryID) {
            $client = Client::inst();
            $list = $client->searchListings(
                [ $mainCategoryID ],
                null,
                '',
                null,
                null,
                null,
                $limit,
                null,
                null,
                2000,
                $this->ID
            );
            return $list ? $list->getIterator() : null;
        }
	}

	public function MetaTitle()
    {
        return $this->MetaData ? $this->MetaData->meta_title : SiteConfig::current_site_config()->Title;
    }

	public function MetaKeywords()
    {
        return $this->MetaData ? $this->MetaData->keywords : null;
    }

	public function MetaDescription()
    {
        return $this->MetaData ? $this->MetaData->meta_description : null;
    }

	public function CanonicalURL()
    {
        return $this->MetaData ? $this->MetaData->canonical : null;
    }

    public function getOGPostType()
    {
        return $this->MetaData ? $this->MetaData->og_type : 'article';
    }

    public function FacebookTitle()
    {
        return $this->MetaData ? $this->MetaData->og_title : null;
    }

    public function FacebookDescription()
    {
        return $this->MetaData && $this->MetaData->og_description ? $this->MetaDescription() : null;
    }

    public function FacebookImage()
    {
        $url = null;
        if ($this->MetaData && $this->MetaData->og_image) {
            $url = $this->MetaData->og_image;
        }
        return new ImageCached($url);
    }

    public function TwitterTitle()
    {
        return $this->MetaData ? $this->MetaData->twitter_title : null;
    }

    public function TwitterDescription()
    {
        return $this->MetaData && $this->MetaData->twitter_description ? $this->MetaDescription() : null;
    }

    public function TwitterImage()
    {
        $url = null;
        if ($this->MetaData && $this->MetaData->twitter_image) {
            $url = $this->MetaData->twitter_image;
        }
        return new ImageCached($url);
    }

    public function ExtraMeta()
    {
        return $this->MetaData ? $this->MetaData->extra_meta : null;
    }

    public function getDefaultImage()
    {
        return SiteConfig::current_site_config()->GlobalSocialSharingImage();
    }

	public function IsPastEvent()
    {
        $ret = 'NA';
        if ($this->Type == 'Event') {
            if ($this->EndDate) {
                $dtNow = strtotime(DBDatetime::now()->getValue());
                $dtEndDate = strtotime($this->EndDate);
                $today = mktime(0, 0, 0, date('n', $dtNow), date('j', $dtNow), date('Y', $dtNow));
                $endDate = mktime(0, 0, 0, date('n', $dtEndDate), date('j', $dtEndDate), date('Y', $dtEndDate));
                $ret = $endDate >= $today ? 'NO' : 'YES';
            } else {
                $ret = 'YES';
            }

        }
        return $ret;
    }

	public function ImagesForSitemap()
    {
        $list = new ArrayList();
        if ($this->Image && $this->Image->AbsoluteURL) {
            $list->push(new ArrayData([
                'AbsoluteLink' => $this->Image->AbsoluteURL
            ]));
        } elseif ($this->Image && $this->Image->URLForList) {
            $list->push(new ArrayData([
                'AbsoluteLink' => $this->Image->URLForList
            ]));
        }
        return $list;
    }
}
