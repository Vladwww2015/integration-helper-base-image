<?php

namespace IntegrationHelper\BaseImage\Api;

interface ConstraintsInterface
{
    public const PROCESS_CLEANER = 'cleaner';

    public const PROCESS_OPTIMIZER = 'optimizer';

    public const PROCESS_RESIZER = 'resizer';

    public const PROCESS_SAVER = 'saver';

    public const PROCESS_UPLOADER = 'uploader';
}
