<?php

namespace CuongNX\LaravelFlexibleOtp\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeOtpListenerCommand extends GeneratorCommand
{
    protected $name = 'otp:make-listener';
    protected $description = 'Create a ready-to-use OTP sending listener with pre-built providers (SpeedSMS, Zalo ZNS, Mail)';

    protected $type = 'Listener';

    protected function getStub()
    {
        return __DIR__ . '/stubs/SendOtpListener.stub';
    }

    protected function getPath($name)
    {
        $name = str_replace(['\\', '/'], '', $name);
        return $this->laravel['path'] . '/Listeners/' . $name . '.php';
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the listener class', 'SendOtpListener'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite existing listener if it exists'],
        ];
    }

    public function handle()
    {
        $name = $this->argument('name') ?: 'SendOtpListener';

        $path = $this->getPath($name);

        if ($this->files->exists($path) && !$this->option('force')) {
            $this->error("Listener [{$name}.php] already exists! Use -f to overwrite.");
            return 1;
        }

        $this->makeDirectory($path);

        $stub = $this->files->get($this->getStub());

        $className = class_basename($name);

        $stub = str_replace('{{ class }}', $className, $stub);

        $this->files->put($path, $stub);

        $this->components->info("OTP Listener created successfully: app/Listeners/{$className}.php");

        $this->components->warn("Next steps:");
        $this->line("1. Configure your providers in config/services.php (speedsms, zalo, etc.)");
        $this->line("2. The listener is auto-discovered in Laravel 11+");
        $this->line("3. Test with: Otp::generate('0123456789', send: true, provider: 'speedsms')");

        return 0;
    }
}