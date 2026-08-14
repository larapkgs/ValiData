<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData;

use LaraPkgs\ValiData\Concerns\ImplementsNodeInterface;
use LaraPkgs\ValiData\Contracts\Node as NodeContract;

final class Node implements NodeContract
{
    use ImplementsNodeInterface;
}
