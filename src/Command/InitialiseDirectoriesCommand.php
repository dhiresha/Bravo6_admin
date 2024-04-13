<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Utils\FileUtils;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:initialise-directories',
    description: 'Add a short description for your command',
)]
class InitialiseDirectoriesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:initialise-directories';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Initialises all needed directories/folders or files for this project')
            ->setHelp('This command allows you to create the uno_images directory...');
    }

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$filesystem = new Filesystem();
		$directories = [
			'uno_uploads' => [
				'uno_media',
				'user_data' => [
					'images'
				]
			]
		];

		FileUtils::createDirectories($directories, '.', $filesystem, $output);

		return Command::SUCCESS;
	}
}
