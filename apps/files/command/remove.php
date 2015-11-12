<?php
/**
 * Copyright (c) 2015 Morris Jobke <hey@morrisjobke.de>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files\Command;

use OCP\Files\NotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Remove extends Command {

	protected function configure() {
		$this
			->setName('files:remove')
			->setDescription('remove a file/folder a user')
			->addArgument(
					'user_id',
					InputArgument::REQUIRED,
					'specifies the user from which files should be deleted'
			)
			->addArgument(
					'path',
					InputArgument::REQUIRED,
					'specifies the path of the file/folder that should be deleted'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$userId = $input->getArgument('user_id');
		$path = $input->getArgument('path');

		try {
			$userFolder = \OC::$server->getUserFolder($userId);
		} catch (NotFoundException $e) {
			$output->writeln('The user folder of "' . $userId . '" can\'t be found.');
			return;
		}

		try {
			$file = $userFolder->get($path);
		} catch (NotFoundException $e) {
			$output->writeln('The path "' . $path . '" can\'t be found in the users folder.');
			return;
		}

		// enforce an update of the etag
		$file->touch();

		$file->delete();
	}
}
