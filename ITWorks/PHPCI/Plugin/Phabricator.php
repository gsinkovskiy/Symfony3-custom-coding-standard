<?php
namespace ITWorks\PHPCI\Plugin;

use PHPCI;
use PHPCI\Builder;
use PHPCI\Model\Build;
use Symfony\Component\Yaml\Parser as YamlParser;
use PHPCI\Plugin as BaseInterface;

class Phabricator implements BaseInterface
{

	protected $directory;
	protected $phpci;
	protected $build;
	protected $commandList = array();

	/**
	 * Set up the plugin, configure options, etc.
	 *
	 * @param Builder $phpci
	 * @param Build $build
	 * @param array $options
	 */
	public function __construct(Builder $phpci, Build $build, array $options = array())
	{
		$this->phpci = $phpci;
		$this->build = $build;
		$this->directory = $phpci->buildPath;
		if (isset($options['commands'])) {
			$this->commandList = $options['commands'];
		}
	}

	/**
	 * Executes Symfony2 commands
	 *
	 * @return boolean plugin work status
	 */
	public function execute()
	{
		$success = true;
		foreach ($this->commandList as $command) {
			if (!$this->runSingleCommand($command)) {
				$success = false;
				break;
			}
		}
		return $success;
	}

	/**
	 * Run one command
	 *
	 * @param string $command command for cymfony
	 *
	 * @return boolean
	 */
	public function runSingleCommand($command)
	{
		$cmd = 'php ' . $this->directory . 'bin/console ';

		return $this->phpci->executeCommand($cmd . $command, $this->directory);
	}

}
