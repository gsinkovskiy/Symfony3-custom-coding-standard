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
use Symfony\Component\Console\Style\SymfonyStyle;

class PostSnifferResultCommand extends Command
{
	protected function configure()
	{
		$this
			// the name of the command (the part after "bin/console")
			->setName('itw:code-sniffer')

			// the short description shown while running "php bin/console list"
			->setDescription('Run phpcs and post sniffer results to bitbucket.')
			->addArgument('username', InputArgument::REQUIRED, 'Bitbucket username')
			->addArgument('password', InputArgument::REQUIRED, 'Bitbucket password')
			->addArgument('owner', InputArgument::REQUIRED, 'Bitbucket repo owner')
			->addArgument('repo', InputArgument::REQUIRED, 'Bitbucket repo slug')
			->addArgument('commit', InputArgument::REQUIRED, 'Bitbucket commit hash')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$commit = $input->getArgument('commit');
		$cmd = 'git diff-tree --no-commit-id --name-only -r '.$commit;
		exec($cmd, $out, $return);

		if ( $return !== 0 ) {
			$io->error('Error executing git: ' . $cmd);
			$io->writeln($out);

			return 1;
		}

		$out = array_filter($out, function ($file) {
			return substr($file, -4) === '.php';
		});

        if (count($out) == 0) {
            $io->note('Nothing to sniff');

            return 0;
        }

		$listFile = 'phpcs_files';
		$return = file_put_contents($listFile, implode("\n", $out));

		if ( $return === false ) {
			$io->error('Error writing ' . $listFile . ':');
			$io->writeln($out);

			return 1;
		}

		unset($out);
		$cmd = 'vendor/bin/phpcs --config-set installed_paths vendor/gsinkovskiy/itworks-coding-standard';
		exec($cmd, $out, $return);

		if ( $return !== 0 ) {
			$io->error('Error configuring phpcs: ' . $cmd);
			$io->writeln($out);

			return 1;
		}

		unset($out);
		$reportFile = 'phpcs.json';
		$cmd = 'vendor/bin/phpcs --report-json='.$reportFile.' --standard=ITWorks --file-list=' . $listFile;
		exec($cmd, $out, $return);

		if ( !is_readable($reportFile) ) {
			$io->error('Error running phpcs: ' . $cmd);
			$io->writeln($out);

			return 1;
		}

		$reportFile = realpath($reportFile);
		$projectPath = dirname($reportFile);
		$projectPathLength = mb_strlen($projectPath) + 1;
		$report = json_decode(file_get_contents($reportFile), true);
		$changeSets = new Changesets();
		$changeSets->setCredentials(new Basic($input->getArgument('username'), $input->getArgument('password')));
		$owner = $input->getArgument('owner');
		$repo = $input->getArgument('repo');
		$hasErrors = false;

		foreach ($report['files'] as $filename => $fileInfo) {
			$filename = substr($filename, $projectPathLength);

			if (count($fileInfo['messages']) > 0) {
				$io->section('File: ' . $filename);
			}

			foreach ($fileInfo['messages'] as $messageInfo) {
				$hasErrors = true;
				$outMessage = '<info>Line ' . $messageInfo['line'] . ':</info> ';
				$outMessage .= $messageInfo['message'] . ' at column ' . $messageInfo['column'];
				$io->writeln($outMessage);

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

		unlink($reportFile);
		unlink($listFile);

		return $hasErrors ? 1 : 0;
	}
}
