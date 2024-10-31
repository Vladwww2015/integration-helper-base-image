<?php

namespace IntegrationHelper\BaseImage\Model\ImageProcessors\ImageUploader;

interface SourceInterface
{
    /**
     * @return []
     */
    public function getImages(): iterable;

    public function callbackAfterUpload(array $data);
}
