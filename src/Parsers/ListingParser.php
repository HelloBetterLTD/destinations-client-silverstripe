<?php

namespace DD\Client\SilverStripe\Parsers;

use DD\Client\Core\Parsers\ListingParser as SS_ListingParser;
use DD\Client\Core\Parsers\Parser;
use DD\Client\SilverStripe\Model\Listing\Listing;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBTime;
use SilverStripe\View\ArrayData;

class ListingParser extends SS_ListingParser
{
	public function parse($data)
	{
		return Listing::create($this->parserData($data));
	}

	public function processBasicData(&$data)
	{
	    parent::processBasicData($data);
		if (!empty($data['Content'])) {
			$data['Content'] = DBHTMLText::create()->setValue($data['Content']);
		}
		if (!empty($data['AdditionalInfo'])) {
			$data['AdditionalInfo'] = DBHTMLText::create()->setValue($data['AdditionalInfo']);
		}
		if (!empty($data['Summary'])) {
			$data['Summary'] = DBHTMLText::create()->setValue($data['Summary']);
		}
		if (!empty($data['YelpWidget'])) {
			$data['YelpWidget'] = DBHTMLText::create()->setValue($data['YelpWidget']);
		}
		if (!empty($data['StartDate'])) {
			$data['StartDate'] = DBDatetime::create()->setValue($data['StartDate']);
		}
		if (!empty($data['EndDate'])) {
			$data['EndDate'] = DBDatetime::create()->setValue($data['EndDate']);
		}
        if (!empty($data['LastEdited'])) {
            $data['LastEdited'] = DBDatetime::create()->setValue($data['LastEdited']);
        }

		$hours = new ArrayList();
		foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
		    $name = $day . 'Opening';
		    if (!empty($data[$name])) {
		        $day = [
		            'DOW' => substr($day, 0, 3)
                ];
		        if (!empty($data[$name]['From'])) {
		            $day['From'] = DBTime::create()->setValue($data[$name]['From'])->Format('hh:mma');
                }
		        if (!empty($data[$name]['To'])) {
                    $day['To'] = DBTime::create()->setValue($data[$name]['To'])->Format('hh:mma');
                }
                if (!empty($data[$name]['From']) && !empty($data[$name]['To'])) {
                    $day['Open'] = 1;
                }
                $hours->push(new ArrayData($day));
            }
        }
        if ($hours->count()) {
            $data['OpeningHours'] = $hours;
        }

        if (!empty($data['OperatingMonths'])) {
            $json = $data['OperatingMonths'];
            $arrMonths = json_decode($json, true);

            $arrGroups = [];
            $i = 0;
            foreach ($arrMonths as $month => $value) {
                if ($value) {
                    $arrGroup[] = $month;
                } else {
                    $arrGroup = [];
                    if (count($arrGroup)) {
                        $arrGroups[] = $arrGroup;
                    }
                }
                $i++;
                if ($i == 12 && count($arrGroup)) {
                    $arrGroups[] = $arrGroup;
                }
            }
            $arrParts = [];
            foreach ($arrGroups as $arrGroup) {
                if (count($arrGroup) == 1) {
                    $arrParts[] = $arrGroup[0];
                } else {
                    $arrParts[] = $arrGroup[0] . ' - ' . $arrGroup[count($arrGroup) - 1];
                }
            }
            $data['OperatingMonths'] = implode(', ', $arrParts);
        }
	}

	public function processCategories(&$data)
	{
		if (!empty($data['Categories'])) {
			$parser = Parser::get_parser_for(CategoryParser::class);
			$data['Categories'] = $parser->parse($data['Categories']['edges']);
		} else {
			$data['Categories'] = new ArrayList();
		}
	}

	public function processGalleryImages(&$data)
	{
		if (!empty($data['GalleryImages'])) {
			$images = ArrayList::create();
			foreach ($data['GalleryImages']['edges'] as $image) {
				$images->push(ArrayData::create($image['node']));
			}
			$data['GalleryImages'] = $images;
		}
	}

	public function processImage(&$data)
	{
		if (!empty($data['Image'])) {
			$data['Image'] = ArrayData::create($data['Image']);
		}
	}

	public function processMainImage(&$data)
	{
		if (!empty($data['MainImage'])) {
			$data['MainImage'] = ArrayData::create($data['MainImage']);
		}
	}

	public function processLogoImage(&$data)
	{
		if (!empty($data['LogoImage'])) {
			$data['LogoImage'] = ArrayData::create($data['LogoImage']);
		}
	}

	public function processMainCategory(&$data)
	{
		if (!empty($data['MainCategory'])) {
			$data['MainCategory'] = ArrayData::create($data['MainCategory']);
		}
	}

	public function processFiles(&$data)
	{
		if (!empty($data['Files'])) {
			$files = ArrayList::create();
			foreach ($data['Files'] as $file) {
				$files->push(ArrayData::create($file));
			}
			$data['Files'] = $files;
		}
	}
}
