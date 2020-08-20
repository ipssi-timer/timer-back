<?php

namespace App\Controller\API;

use App\Entity\Entry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class APIEntryController extends AbstractController
{
    private $em;
    private $serializer;


    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer){

        $this->em = $entityManager;
        $this->serializer = $serializer;

    }
    /**
     * @Route("/a/p/i/entry", name="a_p_i_entry")
     */
    public function index()
    {
        return $this->render('api_entry/index.html.twig', [
            'controller_name' => 'APIEntryController',
        ]);
    }

    /**
     * @Route("api/newEntry/{start}/{end}/{project}",name="api_entry",methods={"GET"})
     */
    public function new($start,$end,$project){
        $entry = new Entry();
        $entry->setStartsAt($start);
        $entry->setEndsAt($end);
        $entry->setUser($this->getUser());
        $entry->setProject($project);
        $this->em->persist($entry);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/updateEntry/{end}/",name="api_update_entry",methods={"PUT"})
     */
    public function updateEntry($end){
        $entry = new Entry();
        $entry->setEndsAt($end);
        $this->em->persist($entry);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
