<?php

namespace lookyman\Commands\Tests\Mock;

use Symfony\Component\Console\Output\Output;

class EchoOutput extends Output
{

	/**
	 * @param string $message
	 * @param bool $newline
	 */
	protected function doWrite($message, $newline)
	{
		echo $message;
		if ($newline) {
			echo "\n";
		}
	}

}
