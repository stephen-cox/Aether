<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aether\About\Controller\Api;

use Aether\Filesystem\Service\Filesystem;
use Junker\JsendResponse\JSendErrorResponse;
use Junker\JsendResponse\JSendFailResponse;
use Junker\JsendResponse\JSendSuccessResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Root API controller class.
 */
class ApiController
{

    /**
     * @Route("/", name="about", methods={"GET"})
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
}
