<?php

namespace lookyman\Commands\DI;

use lookyman\Commands\CompileTemplatesCommand;
use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Helpers;
use Nette\Utils\Validators;

/**
 * @author Lukáš Unger <looky.msc@gmail.com>
 */
class CommandsExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'compileTemplates' => []
	];

	public $compileTemplatesDefaults = [
		'source' => '%appDir%',
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		Validators::assertField($config, 'compileTemplates', 'array');
		$this->setupCompileTemplatesCommand($builder, $config['compileTemplates']);
	}

	private function setupCompileTemplatesCommand(ContainerBuilder $builder, array $config)
	{
		$config = $this->validateConfig($this->compileTemplatesDefaults, $config, 'compileTemplates');

		Validators::assertField($config, 'source', 'string|array');
		$builder->addDefinition($this->prefix('compileTemplates'))
			->setClass(CompileTemplatesCommand::class, [array_map(function ($directory) use ($builder) {
				Validators::assert($directory, 'string');
				return Helpers::expand($directory, $builder->parameters);
			}, (array) $config['source'])])
			->addTag(ConsoleExtension::TAG_COMMAND)
			->setAutowired(false);
	}

}
