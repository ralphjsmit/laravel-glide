<?php

namespace RalphJSmit\Laravel\Glide\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearCacheCommand extends Command
{
    protected $signature = 'glide:clear';

    public function handle(): int
    {
        File::deleteDirectory(
            glide()->getCachePath()
        );

        $this->info("Cleared Glide cache");

        return static::SUCCESS;
    }
}