<?php

namespace CuongNX\LaravelFlexibleOtp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeOtpModelCommand extends GeneratorCommand
{
    protected $name = 'otp:make-model';
    protected $description = 'Create a custom OTP model (MySQL or MongoDB compatible)';

    protected $type = 'Model';

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Models';
    }

    protected function getStub()
    {
        return config('otp.connection') === 'mongodb'
            ? __DIR__ . '/stubs/OtpRecord.mongo.stub'
            : __DIR__ . '/stubs/OtpRecord.mysql.stub';
    }

    protected function getNameInput()
    {
        return $this->argument('name') ?: 'OtpRecord';
    }

    protected function getPath($name)
    {
        $name = str_replace(['\\', '/'], '', $name);
        return $this->laravel['path'] . '/Models/' . $name . '.php';
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the model class', null],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing model if it exists'],
        ];
    }

    public function handle()
    {
        $name = $this->getNameInput();

        $path = $this->getPath($name);

        if ($this->files->exists($path) && !$this->option('force')) {
            $this->error("Model [{$name}.php] already exists! Use -f to overwrite.");
            return 1;
        }

        $this->makeDirectory($path);

        $stub = $this->files->get($this->getStub());

        $namespace = $this->getDefaultNamespace($this->rootNamespace());
        $className = class_basename($name);

        $stub = str_replace('{{ namespace }}', $namespace, $stub);
        $stub = str_replace('{{ class }}', $className, $stub);

        $this->files->put($path, $stub);

        $this->components->info("Model created successfully: app/Models/{$className}.php");

        $fullClass = "{$namespace}\\{$className}";
        $this->components->warn("Remember to update config/otp.php:");
        $this->line("    'model' => {$fullClass}::class,");

        return 0;
    }
}
