<?php
namespace Jet\Publicam\JetEngage\V1\Http\Controllers\Utility;

use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileCompressor Service
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Haridarshan Gorana  <hari.darshan@jetsynthesys.com>
 * @since           July 12, 2018
 * @copyright       2018 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class FileCompressor
{
    /** @var null|string */
    private $ffmpeg;

    /**
     * FileCompressor constructor.
     */
    public function __construct()
    {
        $this->ffmpeg = trim(shell_exec('which ffmpeg'));
    }

    /**
     * FFmpeg exists or not
     * @return bool
     */
    public function isExists()
    {
        return $this->ffmpeg !== null ? true : false;
    }

    /**
     * Compress Images using ffmpeg command
     * @param string $directory
     * @param string $input
     * @param string $output
     * @return bool
     */
    public function compress($directory, $input, $output): bool
    {
        $input = $this->createFullFilePath($directory, $input);
        $output = $this->createFullFilePath($directory, $output);

        $cmd = escapeshellcmd("{$this->ffmpeg} -y -i $input -vf scale=iw:-1 $output");
        @shell_exec($cmd);
        unlink($input);

        if (file_exists($output)) {
            return true;
        }

        return false;
    }

    /**
     * Create Full File Path
     * @param $directory
     * @param $file
     * @return string
     */
    private function createFullFilePath($directory, $file)
    {
        $directory = preg_replace('{/$}', '', $directory);

        return $directory."/".$file;
    }
}
