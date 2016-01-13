<?php

namespace lookyman\Commands\Tests\DI;

use lookyman\Commands\CompileTemplatesCommand;
use lookyman\Commands\DI\CommandsExtension;
use Kdyby\Console\DI\ConsoleExtension;
use Nette\Utils\AssertionException;
use Nette\DI\Compiler;
use Nette\DI\Config\Helpers;
use Nette\DI\ContainerLoader;

class CommandsExtensionTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @expectedException \Nette\DI\MissingServiceException
	 */
	public function testDisabledAutowiring()
	{
		$this->createContainer($this->getDefaultConfig())
			->getByType(CompileTemplatesCommand::class);
	}

	public function testFindService()
	{
		$container = $this->createContainer($this->getDefaultConfig());
		$this->assertSame(
			['commands.compileTemplates'],
			$container->findByType(CompileTemplatesCommand::class)
		);
		$this->assertSame(
			['commands.compileTemplates' => true],
			$container->findByTag(ConsoleExtension::TAG_COMMAND)
		);
	}

	/**
	 * @dataProvider provideConfigs
	 * @param array $config
	 * $param string|null $expectedException
	 */
	public function testConfig(array $config, $expectedException = null)
	{
		if ($expectedException) {
			$this->setExpectedException($expectedException);
		}
		$this->createContainer(Helpers::merge($this->getDefaultConfig(), $config));
	}

	/**
	 * @return array
	 */
	public function provideConfigs()
	{
		return [
			[ // defaults
				[],
				null,
			],
			[ // compileTemplates section must be array
				[
					'commands' => [
						'compileTemplates' => false,
					],
				],
				AssertionException::class,
			],
			[ // source must be string or array
				[
					'commands' => [
						'compileTemplates' => [
							'source' => false,
						],
					],
				],
				AssertionException::class,
			],
			[ // source is string
				[
					'commands' => [
						'compileTemplates' => [
							'source' => '%appDir%',
						],
					],
				],
				null,
			],
			[ // source must be array of strings
				[
					'commands' => [
						'compileTemplates' => [
							'source' => [
								false,
							],
						],
					],
				],
				AssertionException::class,
			],
			[ // source is array of strings
				[
					'commands' => [
						'compileTemplates' => [
							'source' => [
								'%appDir%',
							],
						],
					],
				],
				null,
			],
		];
	}

	/**
	 * @param array $config
	 * @return \Nette\DI\Container
	 */
	private function createContainer(array $config)
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load($config, function (Compiler $compiler) use ($config) {
			$compiler->addExtension('commands', new CommandsExtension);
			$compiler->addConfig($config);
		});
		$container = new $class;
		$container->initialize();
		return $container;
	}

	/**
	 * @return array
	 */
	private function getDefaultConfig()
	{
		return [
			'parameters' => [
				'appDir' => TEMP_DIR,
			],
		];
	}

}
