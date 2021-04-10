<?php

namespace Aether\AetherFilesystem\Controller\Api;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Root API controller class.
 */
class ApiController extends AbstractFOSRestController
{
    /**
     * @Route("/fs", name="root")
     */
    public function index(Request $request)
    {
        $response = [
            'status' => 'success',
            'data' => [
                'message' => 'Hello',
                'api_version' => $request->attributes->get('version'),
            ]
        ];
        $view = $this->view($response, 200);
        return $this->handleView($view);
    }
}
