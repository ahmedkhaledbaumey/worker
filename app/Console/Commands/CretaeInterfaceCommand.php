<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CretaeInterfaceCommand extends FileFactoryCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:cretae-interface-command';
    protected $signature = 'make:interface {classname}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    function setStubName(): string
    {
        return "interface";
    }
    function setFilePath(): string
    {
        return "app\\Interfaces\\";
    }
    function setSuffix(): string
    {
        return "Interface";
    }
}
