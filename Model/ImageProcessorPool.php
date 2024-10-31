<?php

namespace IntegrationHelper\BaseImage\Model;

use Magento\Framework\Exception\LocalizedException;

use IntegrationHelper\BaseImage\Api\ImageProcessorInterface;

class ImageProcessorPool
{
    /**
     * @var array
     */
    protected $_processors = [];

    /**
     * @param array $processors
     * @throws LocalizedException
     */
    public function __construct(array $processors = [])
    {
        $this->_processors = array_filter($processors, fn($processor) => $processor instanceof ImageProcessorInterface);
        $this->validate();
        uasort($this->_processors, fn($itemA, $itemB) => $itemA->getSortOrder() <=> $itemB->getSortOrder());
    }

    /**
     * @return ImageProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->_processors;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function validate()
    {
        $uniqueNames = [];
        foreach ($this->_processors as $processor) {
            $processName = $processor->getProcessName();
            if(!in_array($processName, $uniqueNames)) {
                $uniqueNames[] = $processName;
                continue;
            }
            throw new LocalizedException(__('Image Process With Name %1 duplicates', $processName));
        }
    }
}
