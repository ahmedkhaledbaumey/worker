<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Pluralizer;

abstract class FileFactoryCommand extends Command
{
    protected $file;
    public function __construct(Filesystem $file)
    {
        parent::__construct();
        $this->file = $file;
    }
    abstract function setStubName(): string;
    abstract function setFilePath(): string;
    abstract function setSuffix(): string;
    protected function singleClassName($name)
    {
        return ucwords(Pluralizer::singular($name));
    }

    protected function makeDir($path)
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
    }

    protected function stubPath()
    {
        $stubName = $this->setStubName();
        return __DIR__ . "/../../../stubs/{$stubName}.stub";
    }

    protected function stubVariable()
    {
        return [
            'NAME' => $this->singleClassName($this->argument('classname'))
        ];
    }

    protected function stubContent($stubPath, $stubVariable)
    {
        $content = file_get_contents($stubPath);
        foreach ($stubVariable as $key => $value) {
            $contents = str_replace('$' . $key, $value, $content);
        }
        return $contents;
    }

    protected function getPath()
    {
        $filePath = $this->setFilePath();
        $suffix = $this->setSuffix();
        // Determine the directory path for saving the service file
        return base_path($filePath) . $this->singleClassName($this->argument('classname')) . "{$suffix}.php";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->getPath();

        // Create the directory if it doesn't exist
        $this->makeDir(dirname($path));

        if (File::exists($path)) {
            $this->info('This class already exists');
        } else {
            $suffix = $this->setSuffix();
            $content = $this->stubContent($this->stubPath(), $this->stubVariable());
            File::put($path, $content);
            $name  = $this->singleClassName($this->argument('classname')) . "{$suffix}";
            $this->info($name . ' has been created');
        }
    }
}
