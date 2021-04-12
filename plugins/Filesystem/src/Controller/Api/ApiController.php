<?php

namespace Aether\Filesystem\Controller\Api;

use Aether\Filesystem\Service\Filesystem;
use Junker\JsendResponse\JSendErrorResponse;
use Junker\JsendResponse\JSendFailResponse;
use Junker\JsendResponse\JSendSuccessResponse;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToRetrieveMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Root API controller class.
 */
class ApiController
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem->getFilesystem();
    }

    /**
     * @Route("/", name="status", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        // Return status of API.
        $data = [
            'message' => 'Hello',
            'api_version' => $request->attributes->get('version'),
        ];
        return new JSendSuccessResponse($data);
    }

    /**
     * @Route("/{path}/{action}", name="get", methods={"GET"})
     */
    public function get(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);
        $action = $action;

        // Get directory listing.
        if ($action === 'dir') {
            try {
                $recursive = $request->query->get('recursive', FALSE);
                $listing = $this->filesystem->listContents($path, $recursive);
                $data = [
                    'action' => $action,
                    'listing' => $listing->toArray(),
                    'path' => $path,
                ];
                return new JSendSuccessResponse($data);
            }
            catch (FilesystemException $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Get file contents.
        elseif ($action == 'file') {
            try {

                // File doesn't exists.
                if (!$this->filesystem->fileExists($path)) {
                    $data = [
                        'content' => '',
                        'path' => $path,
                        'message' => 'File not found',
                    ];
                    return new JSendFailResponse($data, 404);
                }

                // Read file.
                try {
                    $file = $this->filesystem->read($path);
                    $data = [
                        'action' => $action,
                        'content' => $file,
                        'path' => $path,
                    ];
                    return new JSendSuccessResponse($data);
                }
                catch (FilesystemException | UnableToReadFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            }
            catch (FilesystemException | UnableToRetrieveMetadata $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Action not valid or not set.
        $data = [
            'action' => $action,
            'content' => '',
            'message' => 'Action must be define.',
            'path' => $path,
        ];
        return new JSendFailResponse($data, 400);
    }

    /**
     * @Route("/{path}/{action}", name="put", methods={"PUT"})
     */
    public function put(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);
        $content = $request->request->get('content', '');

        // Create directory.
        if ($action == 'dir') {
            try {
                $this->filesystem->createDirectory($path);
                $data = [
                    'path' => $path,
                ];
                return new JSendSuccessResponse($data, 201);
            }
            catch (FilesystemException | UnableToCreateDirectory $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }

        }

        // Create file.
        elseif ($action == 'file') {
            try {

                // File already exists.
                if ($this->filesystem->fileExists($path)) {
                    $data = [
                        'content' => '',
                        'path' => $path,
                        'message' => 'File already exists. Use PATCH to update file',
                    ];
                    return new JSendFailResponse($data, 400);
                }

                // Create file.
                try {
                    $this->filesystem->write($path, $content);
                    $data = [
                        'path' => $path,
                    ];
                    return new JSendSuccessResponse($data, 201);

                }
                catch (FilesystemException | UnableToWriteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            }
            catch (FilesystemException | UnableToRetrieveMetadata $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Action not valid or not set.
        $data = [
            'action' => $action,
            'content' => '',
            'message' => 'Action must be define.',
            'path' => $path,
        ];
        return new JSendFailResponse($data, 400);
    }

    /**
     * @Route("/{source}/{action}", name="post", methods={"POST"})
     */
    public function post(Request $request, $source, $action): Response
    {
        $source = base64_decode($source);
        $destination = base64_decode($request->request->get('destination'));
        $replace = (bool) $request->request->get('replace', FALSE);

        // Check destination.
        if (!$destination) {
            $data = [
                'source' => $source,
                'destination' => $destination,
                'message' => 'A destination for the file must be set.',
            ];
            return new JSendFailResponse($data, 400);
        }
        if (!$replace and $this->filesystem->fileExists($destination)) {
            $data = [
                'source' => $source,
                'destination' => $destination,
                'message' => 'Destination file already exists',
            ];
            return new JSendFailResponse($data, 400);
        }

        // Check source.
        if (!$this->filesystem->fileExists($source)) {
            $data = [
                'source' => $source,
                'destination' => $destination,
                'message' => 'Source file not found.',
            ];
            return new JSendFailResponse($data, 400);
        }

        // Copy.
        if ($action == 'copy') {
            try {
                $this->filesystem->copy($source, $destination);
                $data = [
                    'source' => $source,
                    'destination' => $destination,
                ];
                return new JSendSuccessResponse($data, 200);
            }
            catch (FilesystemException | UnableToCopyFile $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Move.
        elseif ($action == 'move') {
            try {
                $this->filesystem->move($source, $destination);
                $data = [
                    'source' => $source,
                    'destination' => $destination,
                ];
                return new JSendSuccessResponse($data, 200);
            }
            catch (FilesystemException | UnableToMoveFile $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Action not valid or not set.
        $data = [
            'action' => $action,
            'content' => '',
            'message' => 'Action must be define.',
            'source' => $source,
            'destination' => $destination,
        ];
        return new JSendFailResponse($data, 400);
    }

    /**
     * @Route("/{path}/{action}", name="patch", methods={"PATCH"})
     */
    public function patch(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);
        $content = $request->request->get('content', '');

        // Update file.
        if ($action == 'file') {
            try {

                // File doesn't exist.
                if (!$this->filesystem->fileExists($path)) {
                    $data = [
                        'content' => '',
                        'path' => $path,
                        'message' => 'File not found. Use PUT to create a file',
                    ];
                    return new JSendFailResponse($data, 404);
                }

                // Update file.
                try {
                    $this->filesystem->write($path, $content);
                    $data = [
                        'path' => $path,
                    ];
                    return new JSendSuccessResponse($data, 201);

                }
                catch (FilesystemException | UnableToWriteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            }
            catch (FilesystemException | UnableToRetrieveMetadata $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Action not valid or not set.
        $data = [
            'action' => $action,
            'content' => '',
            'message' => 'Action must be define.',
            'path' => $path,
        ];
        return new JSendFailResponse($data, 400);
    }

    /**
     * @Route("/{path}/{action}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);

        // Delete directory.
        if ($action == 'dir') {
            try {
                $this->filesystem->deleteDirectory($path);
                $data = [
                    'path' => $path,
                ];
                return new JSendSuccessResponse($data, 200);

            }
            catch (FilesystemException | UnableToDeleteDirectory $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Delete file.
        if ($action == 'file') {
            try {

                // File doesn't exist.
                if (!$this->filesystem->fileExists($path)) {
                    $data = [
                        'path' => $path,
                        'message' => 'File not found.',
                    ];
                    return new JSendFailResponse($data, 404);
                }

                // Delete file.
                try {
                    $this->filesystem->delete($path);
                    $data = [
                        'path' => $path,
                    ];
                    return new JSendSuccessResponse($data, 200);

                }
                catch (FilesystemException | UnableToDeleteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            }
            catch (FilesystemException | UnableToRetrieveMetadata $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Action not valid or not set.
        $data = [
            'action' => $action,
            'content' => '',
            'message' => 'Action must be define.',
            'path' => $path,
        ];
        return new JSendFailResponse($data, 400);
    }
}
