<?php
namespace ITWorks\PHPCI\Plugin;

use PHPCI;
use PHPCI\Builder;
use PHPCI\Model\Build;
use Symfony\Component\Yaml\Parser as YamlParser;
use PHPCI\Plugin as BaseInterface;
use b8\Store\Factory;
use PHPCI\Store\BuildErrorStore;
use PHPCI\Model\BuildError;

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
	 * @var string
	 */
	protected $projectCallsign;

	/**
	 * @var string
	 */
	protected $clientUrl;

	/**
	 * @var string
	 */
	protected $clientToken;

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

		if (!isset($options['project_callsign'])) {
			throw new \LogicException('Option "project_callsign" should be set.');
		}

		if (!isset($options['url'])) {
			throw new \LogicException('Option "url" should be set.');
		}

		if (!isset($options['token'])) {
			throw new \LogicException('Option "token" should be set.');
		}

		$this->projectCallsign = $options['project_callsign'];
		$this->clientUrl = $options['url'];
		$this->clientToken = $options['token'];
	}

	/**
	 * Executes Symfony2 commands
	 *
	 * @return boolean plugin work status
	 */
	public function execute()
	{
		$errors = $this->store->getErrorsForBuild($this->build->getId());
		$result = [];
		$result['buildTargetPHID'] = $this->build->getCommitterEmail();
		$result['type'] = $this->build->isSuccessful() ? 'pass' : 'fail';
		$result['lint'] = [];

		/** @var BuildError $error */
		foreach ($errors as $error) {
			$result['lint'][] = [
				'name' => $error->getMessage(),
				'code' => 'CI'.$error->getId(),
				'severity' => $error->getSeverity() <= BuildError::SEVERITY_HIGH ? 'error': 'warning',
				'path' => $error->getFile(),
				'line' => intval($error->getLineStart()),
			];
		}

		$command = "echo '".json_encode($result)."' | arc call-conduit --conduit-uri {$this->clientUrl} --conduit-token {$this->clientToken} harbormaster.sendmessage";

		return $this->phpci->executeCommand($command);
	}

}
