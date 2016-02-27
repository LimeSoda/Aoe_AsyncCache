<?php

/**
 * Async collection
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Resource_Asynccache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var bool[][] array (indexed by cacheType) of tags.
     * Note: tags are stored as keys to maintain cheap uniqueness.
     */
    protected $tagsByType = array();

    /**
     * @var bool[] array (indexed by cacheType) indicating clearing all tags.
     */
    protected $allByType = array();

    /**
     * @var Aoe_AsyncCache_Model_JobCollection collection of extracted jobs.
     */
    protected $jobCollection = null;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aoeasynccache/asynccache');
    }

    /**
     * Extract jobs
     * Combines job to reduce cache operations
     *
     * @return Aoe_AsyncCache_Model_JobCollection
     */
    public function extractJobs()
    {
        /** @var $jobCollection Aoe_AsyncCache_Model_JobCollection */
        $jobCollection = Mage::getModel('aoeasynccache/jobCollection');
        $this->jobCollection = $jobCollection;

        $this->tagsByType = array();
        $this->allByType = array();
        /** @var $asynccache Aoe_AsyncCache_Model_Asynccache */
        foreach ($this as $asynccache) {
            $mode = $asynccache->getMode();
            $cacheType = $asynccache->getCacheType();
            $tags = $this->getTagArray($asynccache->getTags());

            $this->mergeJob($mode, $cacheType, $tags, $asynccache->getId());
        }

        // Now add the merged any tag jobs as necessary.
        foreach ($this->tagsByType as $cacheType => $tagsByKey) {
            if (!empty($tagsByKey)) {
                /** @var $job Aoe_AsyncCache_Model_Job */
                $job = Mage::getModel('aoeasynccache/job');
                $tags = array_keys($tagsByKey);
                $job->setParameters(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $cacheType, $tags);

                $this->jobCollection->addItem($job);
            }
        }

        return $this->jobCollection;
    }

    /**
     * Optimally merge a job into the collection.
     *
     * @param string $mode The cleaning mode.
     * @param string $cacheType Cache type, such as 'full_page_cache'.
     * @param array $tags Tags to clean.
     * @param string $id Asynccache model ID.
     */
    protected function mergeJob($mode, $cacheType, $tags, $id)
    {
        // If we're already clearing all, there's no need to add more jobs.
        if (!isset($this->allByType[$cacheType])) {
            if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
                $this->mergeAllJob($cacheType, $id);
            } elseif ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
                $this->mergeTags($cacheType, $tags);
            } elseif (($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG) && (count($tags) <= 1)) {
                $this->mergeTags($cacheType, $tags);
            } else {
                $this->mergeOtherJob($mode, $cacheType, $tags, $id);
            }
        }
    }

    /**
     * Merge a job to clear all items in a cache type.
     *
     * Note: also removes other jobs for the type.
     *
     * @param string $cacheType Cache type, such as 'full_page_cache'.
     * @param string $id Asynccache model ID.
     */
    protected function mergeAllJob($cacheType, $id)
    {
        // Other jobs aren't interesting anymore.
        $this->jobCollection->clearByType($cacheType);
        unset($this->tagsByType[$cacheType]);
        // Remember for later jobs.
        $this->allByType[$cacheType] = true;

        /** @var $job Aoe_AsyncCache_Model_Job */
        $job = Mage::getModel('aoeasynccache/job');
        $job->setParameters(Zend_Cache::CLEANING_MODE_ALL, $cacheType, array());
        $job->setAsynccacheId($id);

        $this->jobCollection->addItem($job);
    }

    /**
     * Add additional tags to clear for a cache type.
     *
     * @param string $cacheType Cache type, such as 'full_page_cache'.
     * @param array $tags Tags to clean.
     */
    protected function mergeTags($cacheType, $tags)
    {
        $pendingList = &$this->tagsByType[$cacheType];
        // Add each tag to the array - this keeps it unique.
        foreach ($tags as $tag) {
            $pendingList[$tag] = true;
        }
        unset($pendingList);
    }

    /**
     * Merge a type of clean job we can't optimize.
     *
     * @param string $mode The cleaning mode.
     * @param string $cacheType Cache type, such as 'full_page_cache'.
     * @param array $tags Tags to clean.
     * @param string $id Asynccache model ID.
     */
    protected function mergeOtherJob($mode, $cacheType, $tags, $id)
    {
        /** @var $job Aoe_AsyncCache_Model_Job */
        $job = Mage::getModel('aoeasynccache/job');
        $job->setParameters($mode, $cacheType, $tags);
        $job->setAsynccacheId($id);

        $this->jobCollection->addItem($job);
    }

    /**
     * Get tag array from string
     *
     * @param string $tagString
     * @return array
     */
    protected function getTagArray($tagString)
    {
        $tags = array();
        foreach (explode(',', $tagString) as $tag) {
            $tag = trim($tag);
            if (!empty($tag) && !in_array($tag, $tags)) {
                $tags[] = $tag;
            }
        }
        sort($tags);
        return $tags;
    }
}
