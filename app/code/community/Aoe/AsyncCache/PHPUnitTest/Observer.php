<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoe.com>
 */

class Aoe_AsyncCache_PHPUnitTest_Observer
{
    public function disableAsyncCache()
    {
        Mage::register('disableasynccache', true);
    }
}
