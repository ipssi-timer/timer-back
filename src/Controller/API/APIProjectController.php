<?php

namespace App\Controller\API;

use App\Entity\GroupUsers;
use App\Entity\Project;
use App\Entity\User;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class APIProjectController extends AbstractController
{
    private $em;
    private $serializer;


    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer){

        $this->em = $entityManager;
        $this->serializer = $serializer;

    }

    /**
     * @Route("/api/project/get/{id}", name="api_project_get", methods={"GET"})
     */
    public function index($id,Response $request)
    {
        $project = $this->em->getRepository(Project::class)->find($id);
        $data = $this->serializer->serialize($project, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/new/{name}/{description}/{groupId}",name="project_new", methods={"GET"})
     */
    public function new ($name, $description, $groupId){
        $project = new Project();

        $groupUser = $this->em->getRepository(GroupUsers::class)->find($groupId);


        $project->setName($name);
        $project->setDescription($description);
        $project->setProjectgroup($groupUser);
        $project->setCreator($this->getUser()->getId());
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/delete/{id}", name="project_delete", methods={"DELETE"})
     */
    public function delete ($id)
    {
        $project = $this->em->getRepository(Project::class)->find($id);


        $this->em->remove($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/updateName/{id}/{name}",name="project_update_name",methods={"POST"})
     */
    public function updateProjectName($id,$name){
        $project = $this->em->getRepository(Project::class)->find($id);
        $project->setName($name);
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/updateDescription/{id}/{description}",name="project_update_description",methods={"POST"})
     */
    public function updateProjectDescription($id,$description){
        $project = $this->em->getRepository(Project::class)->find($id);
        $project->setName($description);
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

}
