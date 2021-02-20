<?php

namespace DD\Client\SilverStripe\Forms;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;

class HierarchicalCheckboxSetField extends CheckboxSetField
{
    /** @var null | SS_List */
    protected $sourceList = null;

    protected $valueField = 'ID';

    public function __construct($name, $title = null, SS_List $source = null, $value = null)
    {
        $this->setSourceList($source);
        if (!isset($title)) {
            $title = $name;
        }
        parent::__construct($name, $title, $source, $value);
    }

    public function setValueField($field)
    {
        $this->valueField = $field;
        return $this;
    }

    public function getValueField()
    {
        return $this->valueField;
    }

    public function setSourceList(SS_List $list)
    {
        $this->sourceList = $list;
        return $this;
    }

    public function getSourceList()
    {
        return $this->sourceList;
    }

    /**
     * @param null $sourceObj
     *
     * @return mixed|ArrayList
     */
    public function getOptions($sourceObj = null)
    {
        $selectedValues = $this->getValueArray();
        $defaultItems = $this->getDefaultItems();
        $disabledItems = $this->getDisabledItems();

        // Generate list of options to display
        $odd = false;
        $formID = $this->ID();
        $options = new ArrayList();

        if (!$sourceObj) {
            $sourceObj = $this->getSourceList();
        }
        foreach ($sourceObj as $item) {
            $itemValue = $item->{$this->valueField};
            $itemID = Convert::raw2htmlid("{$formID}_{$itemValue}");
            $odd = !$odd;
            $extraClass = $odd ? 'odd' : 'even';
            $extraClass .= ' val' . preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $itemValue);

            $itemChecked = in_array($itemValue, $selectedValues) || in_array($itemValue, $defaultItems);
            $itemDisabled = $this->isDisabled() || in_array($itemValue, $disabledItems);

            $options->push(new ArrayData([
                'ID' => $itemID,
                'Class' => $extraClass,
                'Role' => 'option',
                'Name' => "{$this->name}[{$itemValue}]",
                'Value' => $itemValue,
                'Title' => $item->Title,
                'isChecked' => $itemChecked,
                'isDisabled' => $itemDisabled,
                'children' => $this->getOptions($item->Children),
            ]));
        }
        $this->extend('updateGetOptions', $options);
        return $options;
    }

    public function Type()
    {
        return 'optionset hierarchical-checkboxset checkboxset';
    }
}
