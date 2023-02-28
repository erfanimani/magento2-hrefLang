<?php

namespace BrunoCanada\HrefLang\Model\Config\Source;

class WebsitesList implements \Magento\Framework\Option\ArrayInterface
{ 
    protected $_storeManager;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->_storeManager = $storeManager;
    }

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->_storeManager->getWebsites() as $storeKey => $store) {
            $result[] = [
                'value' => $storeKey,
                'label' => $store->getName()
            ];
        }
        return $result;
    }
}
