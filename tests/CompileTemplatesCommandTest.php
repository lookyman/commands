<?php

namespace lookyman\Commands\Tests;

use lookyman\Commands\CompileTemplatesCommand;
use Kdyby\Console\ContainerHelper;
use Latte\Engine;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Utils\Finder;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompileTemplatesCommandTest extends \PHPUnit_Framework_TestCase
{

	public function testRun()
	{
		$templateFactory = $this->getMock(ITemplateFactory::class);
		$templateFactory->expects($this->once())
			->method('createTemplate')
			->will($this->returnValue(
				new Template((new Engine)->setTempDirectory(TEMP_DIR))
			));

		$container = $this->getMock(Container::class);
		$container->expects($this->once())
			->method('getByType')
			->will($this->returnValue($templateFactory));

		$command = new CompileTemplatesCommand(__DIR__ . '/templates');
		$command->setHelperSet(new HelperSet([
			'container' => new ContainerHelper($container),
		]));

		$command->run($this->getMock(InputInterface::class), $this->getMock(OutputInterface::class));
		$this->assertCount(1, Finder::find('tests-templates-compileTemplatesCommand-latte-Template*.php')->from(TEMP_DIR));
	}

}
