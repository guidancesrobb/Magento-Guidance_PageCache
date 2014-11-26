<?php
/**
 * Force page cache clearing
 *
 * @author    Guidance Magento Team <magento@guidance.com>
 * @category  Guidance
 * @package   Guidance_PageCache
 * @copyright Copyright (c) 2014 Guidance Solutions (http://www.guidance.com)
 */

class Guidance_PageCache_Model_Observer
    extends Enterprise_PageCache_Model_Observer
{
    /**
     * Observe relevant cache clearing events
     *
     * On:
     *  - adminhtml_cache_flush_all
     *  - adminhtml_cache_flush_system
     *  - adminhtml_cache_refresh_type
     *  
     * @return Guidance_PageCache_Model_Observer
     */
    public function cleanCache()
    {
        parent::cleanCache();
        $processor = $this->_processor;
        $tags = $processor->getRequestTags();
        if (in_array(Enterprise_PageCache_Model_Processor::CACHE_TAG, $tags)) {
            $this->_purgeFpc();
        }
        return $this;
    }

    /**
     * Clear fpc dir
     * 
     * @return void
     */
    protected function _purgeFpc()
    {
        $cacheDirs = $this->_getCacheDirs();
        if (empty($cacheDirs)) {
            return;
        }
        foreach ($cacheDirs as $dir) {
            $this->_rrmdir($dir);
            //system('/bin/rm -rf ' . escapeshellarg($dir));
            //exec('rm -rf ' . $dir);
        }
    }

    /**
     * Get dirs in fpc
     * 
     * @return false|array
     */
    protected function _getCacheDirs()
    {
        $options = Mage::app()->getConfig()->getNode('global/full_page_cache');
        if (!$options) {
            return false;
        }
        $options = $options->asArray();
        $dirs    = array();
        foreach (array('backend_options', 'slow_backend_options') as $tag) {
            if (!empty($options[$tag]['cache_dir'])) {
                $dirs[] = Mage::getBaseDir('var') . DS
                    . $options[$tag]['cache_dir'];
            }
        }
        return empty($dirs) ? false : $dirs;
    }

    /**
     * Recursively delete directory and contents
     * 
     * @param  string $dir
     * @return void
     */
    protected function _rrmdir($dir)
    {
        foreach(glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->_rrmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
