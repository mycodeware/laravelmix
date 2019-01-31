<?php
namespace Jet\Publicam\JetEngage\V1\Http\Traits;

use Aws\S3\S3Client;

use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Input;

use Symfony\Component\HttpFoundation\FileBag;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;

use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\FileUploader;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\FileCompressor;

/**
 * File PlatformTrait
 * @package Jet\Publicam\Api\YesFlix\Traits
 */
trait FileTrait
{
    /**
     * Upload Media content to local repo
     * @param string $prefix Thumb Image prefix
     * @param string $contentId Content Id
     * @param int $storeId Store Id
     * @param array $fileParams File Parameters
     * @param string $fileElement File Element Name
     * @return string
     */
    protected function uploadToLocalFolder(
        $prefix,
        $contentId,
        $storeId,
        $fileParams,
        $fileElement
    ) {
        if ($fileParams['attachment_type'] != 'image' &&
            $fileParams['attachment_type'] != 'video' &&
            $fileParams['attachment_type'] != 'audio'
        ) {
            throw new \RuntimeException('Unsupported Media Type');
        }

        $dirPath = base_path().env('TEMP_DIR');
        
        /**
         * Check if upload directory exists or not
         * If doesn't exists create the same
         */
        if (Utils::folderExists($dirPath) === false) {
            mkdir($dirPath, 0777, true);
        }

        //Generate a unique file name to upload
        $tmpName = md5($storeId."_".time()."_".rand());
        $fileNameWithoutExtension = $prefix."_".$contentId."_".$tmpName;

        $compressor = null;
        if ($fileParams['attachment_type'] === 'image') {
            $uploadFile = new FileUploader(
                $dirPath,
                //(new FileBag($fileParams[$fileElement]))->get('media'),
                Input::file($fileElement),
                true,
                $fileNameWithoutExtension
            );
            $compressor = new FileCompressor;
        } else {
            $uploadFile = new FileUploader(
                $dirPath,
                (new FileBag($fileParams[$fileElement]))->get('media'),
                false,
                $fileNameWithoutExtension
            );
        }

        try {
            $file = $uploadFile->upload($compressor);
            return $file->getPathname();
        } catch (\RuntimeException $exception) {
            throw $exception;
        }
    }

    /**
     * Upload media to S3 bucket
     * @param S3Client $s3Client
     * @param string $prefix
     * @param string $contentId
     * @param int $storeId
     * @param array $fileParams
     * @param string $fileElement
     * @param string $contentType
     * @param string|null $existingFileName
     * @return string
     * @throws \Exception
     */
    protected function uploadToS3Bucket(
        S3Client $s3Client,
        string $prefix,
        string $contentId,
        int $storeId,
        array $fileParams,
        string $fileElement,
        string $contentType,
        string $existingFileName = null
    ) {
        $fileParts = pathinfo($fileParams['media'][$fileElement]['name']);

        //Generate a unique file name to upload
        $tmpName = md5($storeId."_".time()."_".rand());

        $fileNameWithoutExtension = $prefix."_".$contentId."_".$tmpName;

        $newFileName = $fileNameWithoutExtension.'.'.$fileParts['extension'];

        $fileName = evn('S3_FOLDER_NAME').$newFileName;

        $source = $fileParams['media'][$fileElement]['tmp_name'];

        $moveFile = base_path().env('TEMP_DIR').'/'.$fileNameWithoutExtension.'_temp.'.$fileParts['extension'];

        $destination = base_path().env('TEMP_DIR').'/'.$prefix."_".$contentId."_".$tmpName.'.'.$fileParts['extension'];

        // If temp directory doesn't exists create one
        if (! file_exists(base_path().env('TEMP_DIR'))) {
            mkdir(base_path().env('TEMP_DIR'), 0777, true);
        }

        if ($contentType == 'image') {
            $ffmpeg = trim(shell_exec('which ffmpeg'));

            if (empty($ffmpeg)) {
                $moveFile = $destination;
            }

            if (move_uploaded_file($source, $moveFile) === false) {
                echo Utils::json([
                    'code' => 400,
                    'status' => 'failed',
                    'message' => 'Failed to upload Media'
                ]);
                exit();
            }

            if (! empty($ffmpeg)) {
                $compressImages = escapeshellcmd("$ffmpeg -i $moveFile -vf scale=iw:-1 $destination");
                shell_exec($compressImages);
                unlink($moveFile);
            }
        } elseif ($contentType == 'audio') {
            if (move_uploaded_file($source, $destination) === false) {
                echo Utils::json([
                    'code' => 400,
                    'status' => 'failed',
                    'message' => 'Failed to upload Media'
                ]);
                exit();
            }
        }

        if ($existingFileName !== null && $existingFileName != "") {
            $getFileParts = explode("/", $existingFileName);

            $extractFileNameAndExtension = pathinfo($getFileParts[4]);

            $getExistingFileVersion = explode("_", $extractFileNameAndExtension['filename']);

            if (count($getExistingFileVersion) > 3) {
                $versionNo = $getExistingFileVersion[3] + 1;

                $newFileName = $getExistingFileVersion[0].
                    "_".
                    $getExistingFileVersion[1].
                    "_".
                    $getExistingFileVersion[2].
                    "_$versionNo.".
                    $extractFileNameAndExtension['extension'];
            } else {
                $newFileName = $extractFileNameAndExtension['filename']."_1.".$extractFileNameAndExtension['extension'];
            }

            $fileName = evn('S3_FOLDER_NAME').$newFileName;
        }

        $chunkEntity = array(
            'Bucket' => evn('Publicam_Bucket'),
            'Key'    => $fileName,
            'Body'   => fopen($destination, 'r+'),
            'ACL'    => 'public-read'
        );

        try {
            $result = $s3Client->putObject($chunkEntity);

            unlink($destination);
            return Utils::getPlatformDetail('Cloudfront')['CDN_PUBLICAM'].$newFileName;

        } catch (\Throwable $exception) {
            throw $exception;
        }
    }
}
