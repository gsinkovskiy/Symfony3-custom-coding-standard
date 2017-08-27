<?php

namespace ITWorks\Commands;

use Bitbucket\API\Api;
use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories\Changesets;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PostSnifferResultCommand extends Command
{
	protected function configure()
	{
		$this
			// the name of the command (the part after "bin/console")
			->setName('itw:post-sniffer-result')

			// the short description shown while running "php bin/console list"
			->setDescription('Post sniffer results to bitbucket.')
			->addArgument('report', InputArgument::REQUIRED, 'Path to JSON report')
			->addArgument('username', InputArgument::REQUIRED, 'Bitbucket username')
			->addArgument('password', InputArgument::REQUIRED, 'Bitbucket password')
			->addArgument('owner', InputArgument::REQUIRED, 'Bitbucket repo owner')
			->addArgument('repo', InputArgument::REQUIRED, 'Bitbucket repo slug')
			->addArgument('commit', InputArgument::REQUIRED, 'Bitbucket commit hash')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$reportFile = $input->getArgument('report');

		if ( !is_readable($reportFile) ) {
			throw new \InvalidArgumentException('File "'.$reportFile.' is not exists or not readable.');
		}

		$reportFile = realpath($reportFile);
		$projectPath = dirname($reportFile);
		$projectPathLength = mb_strlen($projectPath) + 1;
		$report = json_decode(file_get_contents($reportFile), true);
		$changeSets = new Changesets();
		$changeSets->setCredentials(new Basic($input->getArgument('username'), $input->getArgument('password')));
		$owner = $input->getArgument('owner');
		$repo = $input->getArgument('repo');
		$commit = $input->getArgument('commit');

		foreach ($report['files'] as $filename => $fileInfo) {
			$filename = substr($filename, $projectPathLength);

			foreach ($fileInfo['messages'] as $messageInfo) {
				$message = 'PHPCS ' . $messageInfo['type'] . ' at column ' . $messageInfo['column'] . ': ';
				$message .= $messageInfo['message'] . ' (' . $messageInfo['source'] . ')';
				$changeSets->comments()->create(
					$owner,
					$repo,
					$commit,
					$message,
					['filename' => $filename, 'line_from' => $messageInfo['line']]
				);
			}
		}
	}
}
