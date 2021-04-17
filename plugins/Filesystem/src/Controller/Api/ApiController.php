<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * Flysystem filesystem.
     *
     * @var \League\Flysystem\Filesystem
     */
    private $filesystem;

    /**
     * Initialise Filesystem API controller.
     *
     * @param \League\Flysystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem->getFilesystem();
    }

    /**
     * Get filesystem API status.
     *
     * @Route("/", name="status", methods={"GET"})
     *
     * @api {get} /api/v1/fs Get API status
     * @apiName GetStatus
     * @apiVersion 1.0.0
     * @apiGroup Filesystem
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {Object[]} data Data object.
     * @apiSuccess {String} data.message Message from API.
     * @apiSuccess {String} data.api_version API version requested.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "success",
     *       "data": [
     *         "message": "Hello",
     *         "api_version": "v1",
     *       ]
     *     }
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     *
     * @return Junker\JsendResponse\JSendResponse
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
     * Get directory listing.
     *
     * @Route("/{path}/dir", name="get_dir", methods={"GET"})
     *
     * @apiName GetDirectoryListing
     * @apiVersion 1.0.0
     * @apiGroup Filesystem
     *
     * @apiParam (Path Parameters) {String} path Base64 encoded path of directory.
     * @apiParam (Query Parameters) {Boolean} recursive List subdirectories and files.
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {Object[]} data Data object.
     * @apiSuccess {Object[]} data.listing File and directory listing.
     * @apiSuccess {Object[]} data.listing.item Directory listing item.
     * @apiSuccess {String} data.listing.item.type Listing type; either 'dir' or 'file'.
     * @apiSuccess {String} data.listing.item.path Path to listing item.
     * @apiSuccess {String} data.path Path of listing.
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "success",
     *       "data": [
     *         "listing": [
     *           {
     *             "type": "file",
     *             "path": "test.txt",
     *           },
     *           {
     *             "type": "dir",
     *             "path": "test/test.txt",
     *           },
     *         ],
     *         "path": "/",
     *       ]
     *     }
     *
     * @apiError (Error 500) InternalServerError There was an error in the filesystem.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 500 Internal Server Error
     *     {
     *       "status": "error",
     *       "message": "Unable to read filesystem."
     *     }
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $path
     *   Base64 encoded path.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function getDir(Request $request, $path): Response
    {
        $path = base64_decode($path);

        try {
            $recursive = $request->query->get('recursive', false);
            $listing = $this->filesystem->listContents($path, $recursive);
            $data = [
                'listing' => $listing->toArray(),
                'path' => $path,
            ];
            return new JSendSuccessResponse($data);
        } catch (FilesystemException $exception) {
            return new JSendErrorResponse($exception->getMessage());
        }
    }

    /**
     * Get file.
     *
     * @Route("/{path}/file", name="get_file", methods={"GET"})
     *
     * @api {get} /api/v1/fs/:path/file Get file
     * @apiName GetFile
     * @apiVersion 1.0.0
     * @apiGroup Filesystem
     *
     * @apiParam (Path Parameters) {String} path Base64 encoded path of directory.
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {Object[]} data Data object.
     * @apiSuccess {String} data.content Base64 encoded file contents.
     * @apiSuccess {String} data.path Path of fetched file.
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": "success",
     *       "data": [
     *         "content": "RmlsZSBjb250ZW50cyB0ZXN0Lgo=",
     *         "path": "/test.txt",
     *       ]
     *     }
     *
     * @apiError (Error 404) FileNotFound Requested file wasn't found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 File Not Found
     *     {
     *       "status": "fail",
     *       "data": [
     *         "content": "",
     *         "path": "/test.txt",
     *         "message": "File not found",
     *       ]
     *     }
     *
     * @apiError (Error 500) InternalServerError There was an error in the filesystem.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 500 Internal Server Error
     *     {
     *       "status": "error",
     *       "message": "Unable to read filesystem."
     *     }
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $path
     *   Base64 encoded path.
     * @param string                                    $action
     *   Type of action to perform; either file or dir.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function getFile(Request $request, $path): Response
    {
        $path = base64_decode($path);

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
                    'content' => base64_encode($file),
                    'path' => $path,
                ];

                return new JSendSuccessResponse($data);
            } catch (FilesystemException | UnableToReadFile $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        } catch (FilesystemException | UnableToRetrieveMetadata $exception) {
            return new JSendErrorResponse($exception->getMessage());
        }
    }

    /**
     * @Route("/{path}/{action}", name="put", methods={"PUT"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $path
     *   Base64 encoded path.
     * @param string                                    $action
     *   Type of action to perform; either file or dir.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function put(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);
        $content = $request->request->get('content', '');

        // Create directory.
        if ('dir' === $action) {
            try {
                $this->filesystem->createDirectory($path);
                $data = [
                    'path' => $path,
                ];

                return new JSendSuccessResponse($data, 201);
            } catch (FilesystemException | UnableToCreateDirectory $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Create file.
        if ('file' === $action) {
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
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            } catch (FilesystemException | UnableToRetrieveMetadata $exception) {
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
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $source
     *   Base64 encoded source file.
     * @param string                                    $action
     *   Type of action to perform; either copy or move.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function post(Request $request, $source, $action): Response
    {
        $source = base64_decode($source);
        $destination = base64_decode($request->request->get('destination'));
        $replace = (bool) $request->request->get('replace', false);

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
        if ('copy' === $action) {
            try {
                $this->filesystem->copy($source, $destination);
                $data = [
                    'source' => $source,
                    'destination' => $destination,
                ];

                return new JSendSuccessResponse($data, 200);
            } catch (FilesystemException | UnableToCopyFile $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Move.
        if ('move' === $action) {
            try {
                $this->filesystem->move($source, $destination);
                $data = [
                    'source' => $source,
                    'destination' => $destination,
                ];

                return new JSendSuccessResponse($data, 200);
            } catch (FilesystemException | UnableToMoveFile $exception) {
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
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $path
     *   Base64 encoded path.
     * @param string                                    $action
     *   Type of action to perform; only file currently implemented.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function patch(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);
        $content = $request->request->get('content', '');

        // Update file.
        if ('file' === $action) {
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
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            } catch (FilesystemException | UnableToRetrieveMetadata $exception) {
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
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     * @param string                                    $path
     *   Base64 encoded path.
     * @param string                                    $action
     *   Type of action to perform; either file or dir.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function delete(Request $request, $path, $action): Response
    {
        $path = base64_decode($path);

        // Delete directory.
        if ('dir' === $action) {
            try {
                $this->filesystem->deleteDirectory($path);
                $data = [
                    'path' => $path,
                ];

                return new JSendSuccessResponse($data, 200);
            } catch (FilesystemException | UnableToDeleteDirectory $exception) {
                return new JSendErrorResponse($exception->getMessage());
            }
        }

        // Delete file.
        if ('file' === $action) {
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
                } catch (FilesystemException | UnableToDeleteFile $exception) {
                    return new JSendErrorResponse($exception->getMessage());
                }
            } catch (FilesystemException | UnableToRetrieveMetadata $exception) {
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
