<?php
namespace ITWorks\PHPCI\Plugin;

use PHPCI;
use PHPCI\Builder;
use PHPCI\Model\Build;
use Symfony\Component\Yaml\Parser as YamlParser;
use PHPCI\Plugin as BaseInterface;
use b8\Store\Factory;
use PHPCI\Store\BuildErrorStore;

class Phabricator implements BaseInterface
{

	/**
	 * @var Builder
	 */
	protected $phpci;

	/**
	 * @var Build
	 */
	protected $build;

	/**
	 * @var BuildErrorStore
	 */
	protected $store;

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

		$this->store = Factory::getStore('BuildError');
	}

	/**
	 * Executes Symfony2 commands
	 *
	 * @return boolean plugin work status
	 */
	public function execute()
	{
		$errors = $this->store->getErrorsForBuild($this->build->getId());

		/** @var BuildError $error */
		foreach ($errors as $error) {

		}
	}

}
