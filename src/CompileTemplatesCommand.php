<?php

namespace lookyman\Commands;

use Nette\Application\UI\ITemplateFactory;
use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompileTemplatesCommand extends Command
{

	/** @var string[] */
	private $source;

	/**
	 * @param string|array $source
	 */
	public function __construct($source)
	{
		parent::__construct();
		$this->source = (array) $source;
	}

	protected function configure()
	{
		$this->setName('lookyman:compile-templates')
			->setDescription('Compiles all templates.')
			->addOption(
				'verbose',
				'v',
				InputOption::VALUE_NONE,
				'If set, the command will inform about it\'s progress status.'
			);
	}

	public function run(InputInterface $input, OutputInterface $output)
	{
		$latte = $this->getHelper('container')
			->getByType(ITemplateFactory::class)
			->createTemplate()
			->getLatte();

		foreach (Finder::find('*.latte')->from($this->source) as $name => $file) {
			if ((bool) $input->getOption('verbose')) {
				$output->write('Processing template "' . $name . '"');
			}

			try {
				$latte->warmupCache($name);
				if ((bool) $input->getOption('verbose')) {
					$output->writeln(' OK');
				}

			} catch (\Exception $e) {
				if ((bool) $input->getOption('verbose')) {
					$output->writeln(' FAILED');
				}
			}
		}
	}

}