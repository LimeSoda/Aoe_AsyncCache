<?php

class Aoe_AsyncCache_Model_JobCollection extends Varien_Data_Collection
{
    /**
     * Check for duplicates before adding new job to the collection
     *
     * @param Aoe_AsyncCache_Model_Job|Varien_Object $job
     * @return Aoe_AsyncCache_Model_JobCollection
     */
    public function addItem(Varien_Object $job)
    {
        // check if job with same mode and tags already exists
        /** @var $existingJob Aoe_AsyncCache_Model_Job */
        foreach ($this->getItems() as $existingJob) {
            if ($existingJob->isEqualTo($job)) {
                return $this;
            }
        }

        return parent::addItem($job);
    }

    /**
     * Summary for Aoe_Scheduler output
     *
     * @return string
     */
    public function getSummary()
    {
        return "";
    }
}
