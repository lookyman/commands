<?php

namespace lookyman\Commands;

use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\PresenterFactory;
use Nette\Reflection\ClassType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestPresenterMapGeneratorCommand extends Command
{

	protected function configure()
	{
		$this->setName('lookyman:generate-test-presenter-map')
			->setDescription('');
	}

	public function run(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getHelper('container')->getContainer();
		$presenterFactory = $container->getByType(IPresenterFactory::class);
		if (!$presenterFactory instanceof PresenterFactory) {
			return;
		}

		$data = [];
		foreach ($container->findByType(IPresenter::class) as $service) {
			$presenter = $container->getService($service);
			$presenterName = $presenterFactory->unformatPresenterClass(get_class($presenter));
			if (empty($presenterName)) {
				continue;
			}

			$ref = new ClassType($presenter);
			foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC | !\ReflectionMethod::IS_STATIC | !\ReflectionMethod::IS_ABSTRACT) as $method) {
				if (!preg_match('#^render(.+)#i', $method->getName(), $match)) {
					continue;
				} elseif ($method->hasAnnotation('autoTestSkip')) {
					continue;
				}

				$data[] = [
					'presententer' => $presenterName,
					'method' => lcfirst($match[1]),
					'args' => $method->getAnnotation('autoTestArgs'),
				];
			}
		}

		$this->exportMap($data);
	}

	private function exportMap(array $data)
	{
		var_dump($data);
	}

}
