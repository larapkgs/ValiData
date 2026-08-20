<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use LaraPkgs\ValiData\Support\Generator;

function getStubPath(string $stub): string
{
    return realpath(__DIR__ . '/../Stubs/' . $stub);
}

function getGeneratorConfig(array $config = []): array
{
    return array_merge([
        'base_path' => App::path(),
        'base_namespace' => App::getNamespace(),
        'directory' => 'Components',
    ], $config);
}

function cleanup(array $config = []): void
{
    $config = getGeneratorConfig($config);
    $directory = $config['base_path'] . DIRECTORY_SEPARATOR . $config['directory'];
    if (File::isDirectory($directory)) {
        File::deleteDirectory($directory);
    }
}

beforeEach(function () {
    cleanup();
    $this->stub = getStubPath('component.stub');
});

afterEach(function () {
    cleanup();
});

it('expects file input and a stub path on instantiation', function () {
    $generator = new Generator('TestComponent', $this->stub);

    expect($generator)->toBeInstanceOf(Generator::class);
});

it('allows a variadic list of stubs', function () {
    $generator = new Generator('TestComponent', $this->stub, getStubPath('published-component.stub'));

    expect($generator)->toBeInstanceOf(Generator::class);
});

it('throws an exception if no resolvable stubs are given', function () {
    expect(fn () => new Generator('TestComponent'))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => new Generator('TestComponent', 'missing-component.stub'))
        ->toThrow(InvalidArgumentException::class);
});

describe('Generator::generate()', function () {
    it('generates a file based on the given stub', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Components/Test.php')))
            ->toContain('namespace ' . $config['base_namespace'] . 'Components' . ';')
            ->toContain('final class Test')
            ->toContain('return \'component\';');
    });

    it('throws an exception if the intended file path already exists ', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator->generate())->not->toThrow(RuntimeException::class);
        expect(fn () => $generator->generate())->toThrow(RuntimeException::class);
    });

    it('allows overwriting an existing file when the overwrite parameter is set to true', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator->generate())->not->toThrow(RuntimeException::class);
        expect($generator->generate(true))->not->toThrow(RuntimeException::class);
    });

    it('uses the first stub that can be resolved', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', getStubPath('published-component.stub'), $this->stub)->applyConfig($config);

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Components/Test.php')))
            ->toContain('namespace ' . $config['base_namespace'] . 'Components' . ';')
            ->toContain('final class Test')
            ->toContain('return \'published component\';');
    });

    it('allows defining a sub directory', function () {
        cleanup(['directory' => 'Users']);

        $config = getGeneratorConfig();
        $generator = new Generator('Users/Test', $this->stub)->applyConfig($config);

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Users/Test.php')))
            ->toContain('namespace ' . $config['base_namespace'] . 'Users' . ';')
            ->toContain('final class Test')
            ->toContain('return \'component\';');

        cleanup(['directory' => 'Users']);
    });

    it('prevents double .php file extension', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test.php', $this->stub)->applyConfig($config);

        $generator->generate();

        expect(File::exists($config['base_path'] . ('/Components/Test.php')))->toBeTrue();
    });
});

describe('Generator::applyDefaults()', function () {
    it('allows to apply a custom configuration', function () {
        $config = [
            'base_path' => app_path('Domain/Users'),
            'base_namespace' => 'App\\Domain\\Users\\',
            'directory' => 'Actions',
            'type' => 'Action',
            'force_type' => true,
            'overwrite' => true,
        ];

        cleanup($config);
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator)->isOverwriting()->toBeTrue();

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Actions/TestAction.php')))
            ->toContain('namespace ' . $config['base_namespace'] . 'Actions;');

        cleanup($config);
    });
});

describe('Generator::basePath()', function () {
    it('allows to apply a custom base path', function () {
        $basePath = app_path('Domain/Users');
        cleanup(['base_path' => $basePath]);

        $config = getGeneratorConfig();
        $generator = new Generator('Test.php', $this->stub)->applyConfig($config)->basePath($basePath);

        $generator->generate();

        expect(File::exists($basePath . ('/Components/Test.php')))->toBeTrue();

        cleanup(['base_path' => $basePath]);
    });
});

describe('Generator::baseNamespace()', function () {
    it('allows to apply a custom base namespace', function () {
        $baseNamespace = 'App\\Domain\\';

        $config = getGeneratorConfig();
        $generator = new Generator('Test.php', $this->stub)->applyConfig($config)->baseNamespace($baseNamespace);

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Components/Test.php')))
            ->toContain('namespace ' . $baseNamespace . 'Components;');
    });
});

describe('Generator::directory()', function () {
    it('allows to apply a custom directory', function () {
        $directory = 'Domain';
        cleanup(['directory' => $directory]);

        $config = getGeneratorConfig();
        $generator = new Generator('Test.php', $this->stub)->applyConfig($config)->directory($directory);

        $generator->generate();

        expect(File::get($config['base_path'] . '/' . $directory . '/Test.php'))
            ->toContain('namespace ' . $config['base_namespace'] . $directory . ';');

        cleanup(['directory' => $directory]);
    });
});

describe('Generator::type()', function () {
    it('sets the type of file to be generated', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config)->type('Validator');

        expect($generator)->getType()->toBe('Validator');
    });
});

describe('Generator::getType()', function () {
    it('provides the type of file to be generated', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator)->getType()->toBe('Component');
    });
});

describe('Generator::forceType()', function () {
    it('allows the type of file to be appended to the end of the file name', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config)
            ->type('Validator')
            ->forceType();

        $generator->generate();

        expect(File::get($config['base_path'] . ('/Components/TestValidator.php')))
            ->toContain('final class TestValidator');
    });
});

describe('Generator::overwrite()', function () {
    it('allows to overwrite files by default', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config)->overwrite();

        expect($generator->generate())->not->toThrow(RuntimeException::class);
        expect(fn () => $generator->generate())->not->toThrow(RuntimeException::class);
    });
});

describe('Generator::isOverwriting()', function () {
    it('indicates if files are going to be overwritten', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator->isOverwriting())->toBeFalse();

        $generator->overwrite();

        expect($generator->isOverwriting())->toBeTrue();
    });
});

describe('Generator::exists()', function () {
    it('indicates if the intended file path already exists', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator)->exists()->toBeFalse();

        $generator->generate();

        expect($generator)->exists()->toBeTrue();
    });
});

describe('Generator::getPath()', function () {
    it('provides the normalized path where the file is (going to be) stored', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator)->getPath()->toBe('app/Components/Test.php');
    });

    it('provides the fully qualified path path where the file is (going to be) stored', function () {
        $config = getGeneratorConfig();
        $generator = new Generator('Test', $this->stub)->applyConfig($config);

        expect($generator)->getPath(false)->toBe(App::path('Components/Test.php'));
    });
});
