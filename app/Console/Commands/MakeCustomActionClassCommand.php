<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCustomActionClassCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action-class
            {name : The name of the Action class}
            {--with= : Comma-separated list of optionally create related classes (Currently supported: Command, Job)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action class (with optional Command/Job)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        /** @var string|null $option */
        $option = $this->option('with');

        /** @var list<string> $withOptions */
        $withOptions = [];

        if (! is_null($option)) {
            $withOptions = explode(',', $option);
        }

        $namespace = "App\\Actions\\{$name}";
        $path = app_path("Actions/{$name}/{$name}.php");

        File::ensureDirectoryExists(dirname($path));

        $stub = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Support\Facades\Log;

class {$name}
{
    public function __construct()
    {
        //
    }

    public function __invoke(): void
    {
        //
    }

    /**
     * @param  array<mixed>  \$context
     *
     * @phpstan-ignore method.unused (A fresh Action Class won't automatically use this method. delete this ignore tag line when your're ready to use it to prevent PHPStan warnings)
     */
    private function logInfo(string \$message, array \$context = []): void
    {
        Log::info(\$message, \$context);
    }

    /**
     * @param  array<mixed>  \$context
     *
     * @phpstan-ignore method.unused (A fresh Action Class won't automatically use this method. delete this ignore tag line when your're ready to use it to prevent PHPStan warnings)
     */
    private function logError(string \$message, array \$context = []): void
    {
        Log::error(\$message, \$context);
    }
}

PHP;

        if (! File::exists($path)) {
            File::put($path, $stub);
            $this->info("Action class created: {$path}");
        } else {
            $this->error("Action class already exists: {$path}");
        }

        if (! empty($withOptions)) {
            foreach ($withOptions as $option) {
                $option = ucfirst(Str::camel($option));

                match ($option) {
                    'Command' => $this->makeCommand($name),
                    'Job' => $this->makeJob($name),
                    default => $this->warn("Unknown option: {$option}"),
                };
            }
        }
    }

    private function makeCommand(string $name): void
    {
        $commandName = "{$name}Command";
        $this->call('make:command', ['name' => $commandName]);

        $path = app_path("Console/Commands/{$commandName}.php");
        if (! File::exists($path)) {
            return;
        }

        $content = File::get($path);

        // Tambah trait & import action
        if (! str_contains($content, 'CapturesCommandOutput')) {
            $content = str_replace(
                'use Illuminate\Console\Command;',
                "use App\\Actions\\{$name}\\{$name};\nuse App\\Traits\\CapturesCommandOutput;\nuse Illuminate\Console\Command;",
                $content
            );

            /** @var string $content */
            $content = preg_replace(
                '/class\s+'.preg_quote($commandName, '/').'\s+extends\s+Command\s*\{/',
                "class {$commandName} extends Command\n{\n    use CapturesCommandOutput;\n",
                $content
            );
        }

        /**
         * Update handle
         *
         * @var string $content
         */
        $content = preg_replace(
            '/public function handle\(\)\s*\{[^}]*\}/s',
            "public function handle(): void\n    {\n        (new {$name}(\$this))();\n    }",
            $content
        );

        File::put($path, $content);
        $this->info("Updated Command: {$commandName}");

        // Update constructor Action
        $this->updateActionConstructorForCommand($name);
    }

    private function makeJob(string $name): void
    {
        $jobName = "{$name}Job";
        $this->call('make:job', ['name' => $jobName]);

        $path = app_path("Jobs/{$jobName}.php");
        if (! File::exists($path)) {
            return;
        }

        $content = File::get($path);

        // Tambah import action
        if (! str_contains($content, "use App\\Actions\\{$name}\\{$name};")) {
            $content = str_replace(
                "use Illuminate\Contracts\Queue\ShouldQueue;\nuse Illuminate\Foundation\Queue\Queueable;",
                "use App\\Actions\\{$name}\\{$name};\nuse Illuminate\Contracts\Queue\ShouldQueue;\nuse Illuminate\Foundation\Queue\Queueable;",
                $content
            );
        }

        /**
         * Update handle
         *
         * @var string $content
         */
        $content = preg_replace(
            '/public function handle\(\)\s*:\s*void\s*\{[^}]*\}/s',
            "public function handle(): void\n    {\n        (new {$name})();\n    }",
            $content
        );

        File::put($path, $content);
        $this->info("Updated Job: {$jobName}");
    }

    private function updateActionConstructorForCommand(string $name): void
    {
        $path = app_path("Actions/{$name}/{$name}.php");
        if (! File::exists($path)) {
            return;
        }

        $content = File::get($path);

        // Tambah use
        if (! str_contains($content, 'Illuminate\Console\Command')) {
            $content = str_replace(
                "use Illuminate\Support\Facades\Log;",
                "use Illuminate\Console\Command;\nuse Illuminate\Support\Facades\Log;",
                $content
            );
        }

        /**
         * Ubah constructor
         *
         * @var string $content
         */
        $content = preg_replace(
            '/public function __construct\(\)\s*\{[^}]*\}/s',
            "public function __construct(private ?Command \$console = null)\n    {\n        //\n    }",
            $content
        );

        // Update logInfo dan logError
        $content = str_replace(
            'Log::info($message, $context);',
            "\$this->console?->info(\$message);\n        Log::info(\$message, \$context);",
            $content
        );

        $content = str_replace(
            'Log::error($message, $context);',
            "\$this->console?->error(\$message);\n        Log::error(\$message, \$context);",
            $content
        );

        File::put($path, $content);
        $this->info('Updated Action constructor and logging for Command integration');
    }
}
