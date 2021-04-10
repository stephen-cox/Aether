<?php

namespace Aether\Filesystem\Controller\Api;

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
     * @Route("/", name="root")
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
