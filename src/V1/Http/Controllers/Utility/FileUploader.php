<?php
namespace Jet\Publicam\JetEngage\V1\Http\Controllers\Utility;

use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileUploader Service
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Haridarshan Gorana  <hari.darshan@jetsynthesys.com>
 * @since           July 12, 2018
 * @copyright       2018 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class FileUploader
{
    /** @var string */
    private $targetDirectory;

    /** @var UploadedFile */
    private $file;

    /** @var null|string */
    private $fileName;

    /** @var bool */
    private $compress;

    /**
     * FileUploader constructor.
     * @param string $targetDirectory
     * @param UploadedFile $file
     * @param bool $compress
     * @param null|string $fileName
     */
    public function __construct(
        string $targetDirectory,
        UploadedFile $file,
        bool $compress = false,
        string $fileName = null
    ) {
        $this->targetDirectory = $targetDirectory;
        $this->file = $file;
        $this->compress = $compress;

        $fileName = md5(uniqid()).'.'.$this->file->getClientOriginalExtension();

        if (null !== $fileName) {
            $fileName = pathinfo($fileName)['filename'].'.'.$this->file->getClientOriginalExtension();
        }

        $this->setFileName($fileName);
    }

    /**
     * Upload file to target directory
     * @param null|FileCompressor $compressor
     * @return File
     */
    public function upload(FileCompressor $compressor = null): File
    {
        if ($this->compress === true) {
            if (null === $compressor) {
                throw new \RuntimeException('No FileCompressor provided');
            }

            $tempFile = clone $this;

            $tempFileName = pathinfo(
                $this->getFileName()
            )['filename'].'_temp.'.$this->file->getClientOriginalExtension();
            $tempFile->setFileName($tempFileName);
            $tempFile->disableCompression();

            $tempFile = $tempFile->upload();

            $status = $compressor->compress(
                $this->getTargetDirectory(),
                $tempFile->getFilename(),
                $this->getFileName()
            );

            if ($status === false) {
                throw new \RuntimeException('Error while uploading file', 631);
            }

            return new File(
                $this->getTargetDirectory()."/".$this->getFileName(),
                true
            );
        }

        return $this->file->move($this->getTargetDirectory(), $this->getFileName());
    }

    /**
     * Set File Name
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Get File Name
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get Target Directory
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * Get File
     * @return UploadedFile
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /**
     * Enable Compression
     * @return void
     */
    public function enableCompression(): void
    {
        $this->compress = true;
    }

    /**
     * Disable Compression
     * @return void
     */
    public function disableCompression(): void
    {
        $this->compress = false;
    }
}
