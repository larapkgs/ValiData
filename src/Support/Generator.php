<?php

declare(strict_types=1);

namespace LaraPkgs\ValiData\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use RuntimeException;

final class Generator
{
    protected string $fileInput;

    protected string $stubPath;

    protected string $type = 'Component';

    protected bool $forceType = false;

    protected string $basePath;

    protected string $baseNamespace;

    protected ?string $directory = null;

    protected bool $overwrite = false;

    public function __construct(string $fileInput, string ...$stubPaths)
    {
        $this->fileInput = Str::trim($fileInput, '/');
        $this->stubPath = $this->resolveStubPath(...$stubPaths);
        $this->applyDefaults();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function resolveStubPath(string ...$stubPaths): string
    {
        foreach ($stubPaths as $stubPath) {
            if (File::exists($stubPath)) {
                return $stubPath;
            }
        }

        throw new InvalidArgumentException('No valid stub could be resolved.');
    }

    protected function applyDefaults(): self
    {
        $this->basePath = App::path();
        $this->baseNamespace = App::getNamespace();

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function forceType(bool $forceType = true): self
    {
        $this->forceType = $forceType;

        return $this;
    }

    public function basePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function baseNamespace(string $baseNamespace): self
    {
        $this->baseNamespace = $baseNamespace;

        return $this;
    }

    public function directory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function overwrite(bool $overwrite = true): self
    {
        $this->overwrite = $overwrite;

        return $this;
    }

    public function isOverwriting(): bool
    {
        return $this->overwrite;
    }

    /**
     * @param  array<string, mixed>  $configInput
     */
    public function applyConfig(array $configInput): self
    {
        /** @var string $basePath */
        $basePath = $configInput['base_path'] ?? $this->basePath;
        $this->basePath = $basePath;

        /** @var string $baseNamespace */
        $baseNamespace = $configInput['base_namespace'] ?? $this->baseNamespace;
        $this->baseNamespace = $baseNamespace;

        /** @var string $directory */
        $directory = $configInput['directory'] ?? $this->directory;
        $this->directory = $directory;

        /** @var string $type */
        $type = $configInput['type'] ?? $this->type;
        $this->type = $type;

        /** @var bool $forceType */
        $forceType = $configInput['force_type'] ?? $this->forceType;
        $this->forceType = $forceType;

        /** @var bool $overwrite */
        $overwrite = $configInput['overwrite'] ?? $this->overwrite;
        $this->overwrite = $overwrite;

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function generate(?bool $overwrite = null): self
    {
        $overwrite ??= $this->overwrite;

        if ($this->exists() && ! $overwrite) {
            throw new RuntimeException("A file already exists at the target path: [{$this->resolveFilePath()}].");
        }

        if (! File::isDirectory($this->resolveDirectoryPath())) {
            File::makeDirectory($this->resolveDirectoryPath(), 0777, true, true);
        }

        $contents = $this->renderStub();

        File::put($this->resolveFilePath(), $contents);

        return $this;
    }

    public function exists(): bool
    {
        return File::exists($this->resolveFilePath());
    }

    public function getPath(bool $normalize = true): string
    {
        $path = Str::of($this->resolveFilePath());
        $basePath = App::basePath();

        if ($normalize === false || $path->doesntStartWith($basePath)) {
            return $path->toString();
        }

        return $path->after($basePath)->trim('/')->toString();
    }

    protected function renderStub(): string
    {
        return Collection::make($this->getStubData())
            ->reduce(function (string $rendered, string $value, string $key) {
                $search = Str::of($key)->prepend('{{ ')->append(' }}')->__toString();

                return Str::replace($search, $value, $rendered);
            }, File::get($this->stubPath));
    }

    /**
     * @return array<string, string>
     */
    protected function getStubData(): array
    {
        return [
            'namespace' => $this->resolveNamespace(),
            'class' => $this->resolveClassName(),
        ];
    }

    protected function hasSubDirectory(): bool
    {
        return Str::contains($this->fileInput, '/');
    }

    protected function resolveClassName(): string
    {
        return Str::of($this->fileInput)
            ->afterLast('/')
            ->trim('/')
            ->replaceEnd('.php', '')
            ->replaceEnd($this->type, '')
            ->when($this->forceType, fn (Stringable $className) => $className->append(Str::studly($this->type)))
            ->toString();
    }

    protected function resolveFilePath(): string
    {
        return Str::of($this->resolveDirectoryPath())
            ->append('/', $this->resolveClassName(), '.php')
            ->replace('/', DIRECTORY_SEPARATOR)
            ->toString();
    }

    protected function resolveDirectory(): ?string
    {
        return $this->hasSubDirectory()
            ? Str::of($this->fileInput)->explode('/')->slice(0, -1)->join('/')
            : $this->directory;
    }

    protected function resolveDirectoryPath(): string
    {
        return Str::of($this->basePath)
            ->trim('/')
            ->prepend('/')
            ->when($this->resolveDirectory(), fn (Stringable $path, string $directory) => $path->append('/', $directory))
            ->replace('/', DIRECTORY_SEPARATOR)
            ->toString();
    }

    protected function resolveNamespace(): string
    {
        return Str::of($this->baseNamespace)
            ->trim('\\')
            ->when($this->resolveDirectory(), fn (Stringable $path, string $directory) => $path->append('\\', $directory))
            ->replace('/', '\\')
            ->toString();
    }
}
