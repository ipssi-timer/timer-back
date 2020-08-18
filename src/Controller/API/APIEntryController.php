<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class APIEntryController extends AbstractController
{
    /**
     * @Route("/a/p/i/entry", name="a_p_i_entry")
     */
    public function index()
    {
        return $this->render('api_entry/index.html.twig', [
            'controller_name' => 'APIEntryController',
        ]);
    }
}
