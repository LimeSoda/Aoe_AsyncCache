<?php

/**
 * @method getTags()
 * @method getMode()
 * @method setDuration()
 * @method getDuration()
 * @method getIsProcessed()
 * @method setIsProcessed()
 * @method setAsynccacheId()
 * @method getAsynccacheId()
 * @method string getCacheType()
 * @method Aoe_AsyncCache_Model_Job setCacheType(string $type)
 */
class Aoe_AsyncCache_Model_Job extends Varien_Object
{
    /**
     * Check if this job equals to another job
     *
     * @param Aoe_AsyncCache_Model_Job $job
     * @return bool
     */
    public function isEqualTo(Aoe_AsyncCache_Model_Job $job)
    {
        $sameMode = $this->getMode() == $job->getMode();
        $sameTags = $this->getTags() == $job->getTags();
        $sameCacheType = $this->getCacheType() == $job->getCacheType();
        return $sameMode && $sameTags && $sameCacheType;
    }

    /**
     * Set mode and tags
     *
     * @param $mode
     * @param $cacheType
     * @param $tags
     * @return Aoe_AsyncCache_Model_Job
     */
    public function setParameters($mode, $cacheType, array $tags)
    {
        if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
            $tags = array(); // we don't need any tags for mode 'all'
        }

        $this->setData('mode', $mode)
            ->setData('cache_type', $cacheType)
            ->setData('tags', $tags);

        return $this;
    }

    /**
     * Check if this job is affecting the config cache
     *
     * @return bool
     */
    public function affectsConfigCache()
    {
        $affectsConfig = false;
        if ($this->getCacheType() == 'cache') {
            $affectsConfig = $this->getMode() == Zend_Cache::CLEANING_MODE_ALL
                || in_array(Mage_Core_Model_Config::CACHE_TAG, $this->getTags());
        }
        return $affectsConfig;
    }
}
