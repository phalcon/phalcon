<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use PHPUnit\Framework\SkippedTestSuiteError;

class Integration extends Module
{
    /**
     * Checks if an extension is loaded and if not, skips the test
     *
     * @param string $extension The extension to check
     */
    public function checkExtensionIsLoaded(string $extension)
    {
        if (true !== extension_loaded($extension)) {
            $this->skipTest(
                sprintf("Extension '%s' is not loaded. Skipping test", $extension)
            );
        }
    }

    /**
     * Returns a directory string with the trailing directory separator
     */
    public function getDirSeparator(string $directory): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $directory
     */
    public function safeDeleteDirectory(string $directory)
    {
        $files = glob($directory . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                $this->safeDeleteDirectory($file);
            } else {
                unlink($file);
            }
        }

        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    /**
     * @param string $filename
     */
    public function safeDeleteFile(string $filename)
    {
        if (file_exists($filename) && is_file($filename)) {
            gc_collect_cycles();
            unlink($filename);
        }
    }

    /**
     * Throws the SkippedTestError exception to skip a test
     *
     * @param string $message The message to display
     */
    public function skipTest(string $message)
    {
        throw new SkippedTestSuiteError($message);
    }
}
