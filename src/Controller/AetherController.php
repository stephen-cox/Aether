<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AetherController extends AbstractFOSRestController
{
    private function serialize($param)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($param, 'json');
    }

    /**
     * @Route("/", name="root")
     */
    public function index(): Response
    {
        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AetherController.php',
        ];
        $view = $this->view($data, 200);
        return $this->handleView($view);
    }
}
