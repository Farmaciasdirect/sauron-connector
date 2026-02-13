<?php

declare(strict_types=1);

namespace FarmaciasDirect\Sauron\Enums;

enum ProcessStatus: string
{
    case PROCESSING = 'processing';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
