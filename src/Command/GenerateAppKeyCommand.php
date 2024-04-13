<?php

// src/Command/GenerateAppKeyCommand.php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Utils\FileUtils;

#[AsCommand(
    name: 'app:generate-app-key',
    description: 'Add a short description for your command',
)]
class GenerateAppKeyCommand extends Command
{
    protected static $defaultName = 'app:generate-app-key';
	private $appKeyDir;

	public function __construct(string $appKeyDir)
    {
        parent::__construct();
		$this->appKeyDir = $appKeyDir;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates an app key for this project')
            ->setHelp('This command allows you to create the app key to be used for your api...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        FileUtils::ensureDirectoryExists($this->appKeyDir);

        $apiKeyPath = $this->appKeyDir . '/.dh_app_key';
        $apiKey = bin2hex(random_bytes(32));

        file_put_contents($apiKeyPath, $apiKey);
        $io->success("API key generated and saved to {$apiKeyPath}");

        return Command::SUCCESS;
    }
}