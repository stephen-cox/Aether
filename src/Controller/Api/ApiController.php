<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Junker\JsendResponse\JSendSuccessResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Root API controller class.
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/", name="root")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Symfony Request object.
     *
     * @return Junker\JsendResponse\JSendResponse
     */
    public function index(Request $request): Response
    {
        $data = [
            'message' => 'Hello',
            'api_version' => $request->attributes->get('version'),
        ];

        return new JSendSuccessResponse($data);
    }
}
