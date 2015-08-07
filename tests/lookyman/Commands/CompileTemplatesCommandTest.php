<?php

namespace lookyman\Commands\Tests;

use lookyman\Commands\CompileTemplatesCommand;
use lookyman\Commands\Tests\Mock\EchoOutput;
use Kdyby\Console\ContainerHelper;
use Latte\Engine;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Utils\Finder;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

class CompileTemplatesCommandTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider provideOptions
	 */
	public function testRun($templateDir, $tmpDir, $input, $options, $expectedOutputRegex)
	{
		$this->expectOutputRegex($expectedOutputRegex);

		$templateFactory = $this->getMock(ITemplateFactory::class);
		$templateFactory->expects($this->once())
			->method('createTemplate')
			->will($this->returnValue(
				new Template((new Engine)->setTempDirectory($tmpDir))
			));

		$container = $this->getMock(Container::class);
		$container->expects($this->once())
			->method('getByType')
			->will($this->returnValue($templateFactory));

		$command = new CompileTemplatesCommand($templateDir);
		$command->setHelperSet(new HelperSet([
			'container' => new ContainerHelper($container),
		]));

		$command->run(new ArrayInput($input, new InputDefinition($options)), new EchoOutput);
		$this->assertCount(1, Finder::find('Commands-templates-compileTemplatesCommand-latte-Template*.php')->from($tmpDir));
	}

	/**
	 * @return array
	 */
	public function provideOptions()
	{
		return [
			[
				__DIR__ . '/templates',
				TEMP_DIR . '/silent',
				[],
				[new InputOption('verbose', 'v', InputOption::VALUE_NONE)],
				'#^$#',
			],
			[
				__DIR__ . '/templates',
				TEMP_DIR . '/verbose',
				['--verbose' => NULL],
				[new InputOption('verbose', 'v', InputOption::VALUE_NONE)],
				'#^Processing template ".*compileTemplatesCommand\.latte" OK\n$#'
			],
		];
	}

}
