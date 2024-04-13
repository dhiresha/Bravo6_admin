<?php
// src/Utils/FileUtils.php

namespace App\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FileUtils {

    /**
     * Create directories recursively based on an array of directory names and subdirectories.
     */
    public static function createDirectories(array $directories, string $basePath, Filesystem $filesystem, OutputInterface $output)
    {
        foreach ($directories as $directory => $subdirectories) {
            if (is_int($directory)) {
                $directory = $subdirectories;
                $subdirectories = [];
            }

            $currentPath = $basePath . '/' . $directory;
            self::createDirectory($currentPath, $filesystem, $output);

            if (is_array($subdirectories)) {
                self::createDirectories($subdirectories, $currentPath, $filesystem, $output);
            }
        }
    }

    /**
     * Create a directory if it doesn't already exist and output a message indicating
     * the status of the directory creation.
     */
    public static function createDirectory(string $directoryPath, Filesystem $filesystem, OutputInterface $output)
    {
        try {
            if (!$filesystem->exists($directoryPath)) {
                $filesystem->mkdir($directoryPath);
                $output->writeln('Directory created: ' . $directoryPath);
            } else {
                $output->writeln('Directory already exists: ' . $directoryPath);
            }
        } catch (IOExceptionInterface $exception) {
            $output->writeln('An error occurred while creating your directory at ' . $exception->getPath());
            throw $exception;
        }
    }

	/**
	 * The function `ensureDirectoryExists` creates a directory if it does not already exist.
	 * 
	 * @param string path The `path` parameter in the `ensureDirectoryExists` function is a string that
	 * represents the directory path that you want to ensure exists. If the directory does not exist, the
	 * function will create it using the Symfony Filesystem component.
	*/
	public static function ensureDirectoryExists(string $path)
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            $filesystem->mkdir($path);
        }
    }
}
