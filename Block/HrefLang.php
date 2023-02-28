<?php

namespace BrunoCanada\HrefLang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

class HrefLang extends Template
{
    /**
     * @var \BrunoCanada\HrefLang\Service\HrefLang\AlternativeUrlService
     */
    private $alternativeUrlService;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \BrunoCanada\HrefLang\Service\HrefLang\AlternativeUrlService $alternativeUrlService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->alternativeUrlService = $alternativeUrlService;
    }

    /**
     * @return array in format [en_us => $url] or [en => $url]
     */
    public function getAlternatives()
    {
        $data = [];
        foreach ($this->getStores() as $store) {
            $url = $this->getStoreUrl($store);
            if ($url) {
                $data[$this->getLocaleCode($store)] = $url;
            }
        }
        return $data;
    }

    /**
     * @param Store $store
     * @return string
     */
    private function getStoreUrl($store)
    {
        return $this->alternativeUrlService->getAlternativeUrl($store);
    }

    /**
     * @param StoreInterface $store
     * @return bool
     */
    private function isCurrentStore($store)
    {
        return $store->getId() == $this->_storeManager->getStore()->getId();
    }

    /**
     * @param StoreInterface $store
     * @return string
     */
    private function getLocaleCode($store)
    {
		
        $localeCode = $this->_scopeConfig->getValue('general/locale/code', 'stores', $store->getId());
        return str_replace('_', '-', strtolower($localeCode));
    }

    /**
     * @return Store[]
     */
    private function getStores()
    {
        $config = $this->_scopeConfig->getValue('brunocanada_hreflang/general/same_website_only');
        if ($config === null || $config === '1') {
            return $this->getSameWebsiteStores();
        }

        // Get array of website ids which should be excluded, if empty or not exist will return array with empty item, means no sites will be excluded
        $excludeWebsitesArray = explode(',', $this->_scopeConfig->getValue('brunocanada_hreflang/general/exclude_website'));

        // Filter stores according to excluded websites
        $result = array_filter($this->_storeManager->getStores(), function($v, $k) use ($excludeWebsitesArray) {
            return !in_array($v->getWebsiteId(), $excludeWebsitesArray, true);
        }, ARRAY_FILTER_USE_BOTH);
        return $result;
    }

    /**
     * @return Store[]
     */
    private function getSameWebsiteStores()
    {
        $stores = [];
        /** @var Website $website */
        $website = $this->_storeManager->getWebsite();
        foreach ($website->getGroups() as $group) {
            /** @var Group $group */
            foreach ($group->getStores() as $store) {
                $stores[] = $store;
            }
        }
        return $stores;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->getValue('brunocanada_hreflang/general/enabled')) {
            return '';
        }
        return parent::_toHtml();
    }
}
