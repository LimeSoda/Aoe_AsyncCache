<?php

/**
 * Async collection
 *
 * @author Fabrizio Branca
 */
class Aoe_AsyncCache_Model_Resource_Asynccache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
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

        $matchingAnyTag = array();
        /** @var $asynccache Aoe_AsyncCache_Model_Asynccache */
        foreach ($this as $asynccache) {
            $mode = $asynccache->getMode();
            $tags = $this->getTagArray($asynccache->getTags());

            /** @var $job Aoe_AsyncCache_Model_Job */
            $job = Mage::getModel('aoeasynccache/job');
            $job->setParameters($mode, $tags);
            $job->setAsynccacheId($asynccache->getId());

            if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
                $jobCollection->addItem($job);
                return $jobCollection; // no further processing needed as we're going to clean everything anyway
            } elseif ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
                // collect tags and add to job collection later
                $matchingAnyTag = array_merge($matchingAnyTag, $tags);
            } elseif (($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG) && (count($tags) <= 1)) {
                // collect tags and add to job collection later
                $matchingAnyTag = array_merge($matchingAnyTag, $tags);
            } else {
                // everything else will be added to the job collection
                $jobCollection->addItem($job);
            }
        }

        // processed collected tags
        $matchingAnyTag = array_unique($matchingAnyTag);
        if (count($matchingAnyTag) > 0) {
            /** @var $job Aoe_AsyncCache_Model_Job */
            $job = Mage::getModel('aoeasynccache/job');
            $job->setParameters(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $matchingAnyTag);

            $jobCollection->addItem($job);
        }

        return $jobCollection;
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
