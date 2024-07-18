<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Pluralizer;

class CreateServiceCommand extends FileFactoryCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {classname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates a service class pattern';

    function setStubName(): string
    {
        return "service";
    }
    function setFilePath(): string
    {
        return "app\\Services\\";
    }
    function setSuffix(): string
    {
        return "Service";
    }
}
