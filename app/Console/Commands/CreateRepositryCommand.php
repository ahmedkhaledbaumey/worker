<?php

namespace App\Console\Commands;



class CreateRepositryCommand extends FileFactoryCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repositry {classname}';
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
        return "repo";
    }
    function setFilePath(): string
    {
        return "app\\Repositries\\";
    }
    function setSuffix(): string
    {
        return "Repo";
    }
}
