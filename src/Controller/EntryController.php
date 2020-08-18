<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/entry", name="entry")
 */

class EntryController extends AbstractController
{
    /**
     * @Route("/list", name="list")
     */
    public function index()
    {
        return $this->render('entry/index.html.twig', [
            'controller_name' => 'EntryController',
        ]);
    }
}
