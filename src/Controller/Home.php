<?php


namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Home extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $em){
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

    }



}