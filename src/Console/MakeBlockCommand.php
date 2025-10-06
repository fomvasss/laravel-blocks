<?php

namespace Fomvasss\Blocks\Console;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeBlockCommand extends GeneratorCommand
{
    use CreatesMatchingTest;

    protected $name = 'make:block';

    protected $description = 'Create a new Block';

    protected $type = 'Block';

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/block.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Blocks';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $className = class_basename($name);
        $baseName = preg_replace('/(BlockHandler|Block)$/', '', $className);
        $blockType = Str::snake($baseName);

        return str_replace('{{ block_type }}', $blockType, $stub);
    }
}
