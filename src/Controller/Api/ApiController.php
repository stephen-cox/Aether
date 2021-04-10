<?php

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
