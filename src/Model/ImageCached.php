<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 8/14/20
 * Time: 1:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DD\Client\SilverStripe\Model;

use SilverStripe\View\ViewableData;

class ImageCached extends ViewableData
{

    private $link = null;
    
    public function __construct($url)
    {
        $this->link = $url;
        parent::__construct();
    }
    
    public function exists()
    {
        return !empty($this->link);
    }
    
    public function AbsoluteLink()
    {
        return $this->link;
    }
    
    public function Link()
    {
        return $this->link;
    }
}
