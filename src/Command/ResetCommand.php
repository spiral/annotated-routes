<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Command;

use Spiral\Boot\MemoryInterface;
use Spiral\Console\Command;
use Spiral\Router\Bootloader\AnnotatedRouteBootloader;

final class ResetCommand extends Command
{
    protected const NAME        = 'route:reset';
    protected const DESCRIPTION = 'Reset route cache';

    /**
     * @param MemoryInterface $memory
     */
    public function perform(MemoryInterface $memory)
    {
        $memory->saveData(AnnotatedRouteBootloader::MEMORY_SECTION, null);
        $this->writeln("<info>Done.</info>");
    }
}
