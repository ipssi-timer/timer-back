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
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class APIProjectController extends AbstractController
{
    private $em;
    private $serializer;
    private $validator;


    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer, ValidatorInterface $validator){

        $this->em = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;

    }

    /**
     * @Route("/api/v1/project", name="project_get", methods={"POST"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     */
    public function index(Request $request)
    {
        $id = $request->query->get('id');
        $project = $this->em->getRepository(Project::class)->find($id);
        $data = $this->serializer->serialize($project, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/v1/project/new",name="project_new", methods={"POST"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="name",
     *     type="string",
     *     in="query",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="description",
     *     type="string",
     *     in="query",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="groupId",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     */
    public function new (Request $request){

        $name = $request->query->get('name');
        $description = $request->query->get('description');
        $groupId = $request->query->get('groupId');

        $project = new Project();

        $groupUser = $this->em->getRepository(GroupUsers::class)->find($groupId);

        $project->setName($name);
        $project->setDescription($description);
        $project->setProjectgroup($groupUser);
        $project->setCreator($this->getUser()->getId());
        $error = $this->validator->validate($project);
        if(count($error)) {
            $error = $this->serializer->serialize($error,'json');
            return new Response($error, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/v1/project/delete", name="project_delete", methods={"DELETE"})
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     */
    public function delete (Request $request)
    {
        $id = $request->query->get('id');
        $project = $this->em->getRepository(Project::class)->find($id);


        $this->em->remove($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/updateName",name="project_update_name",methods={"POST"})
     *  @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="name",
     *     type="string",
     *     in="query",
     *     required=true,
     * )
     */
    public function updateProjectName(Request $request){
        $id = $request->query->get('id');
        $name = $request->query->get('name');
        $project = $this->em->getRepository(Project::class)->find($id);
        $project->setName($name);
        $error = $this->validator->validate($project);
        if(count($error)){
            $error = $this->serializer->serialize($error,'json');
            return new Response($error, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/project/updateDescription",name="project_update_description",methods={"POST"})
     *  @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="description",
     *     type="string",
     *     in="query",
     *     required=true,
     * )
     */
    public function updateProjectDescription(Request $request){
        $id = $request->query->get('id');
        $description = $request->query->get('description');
        $project = $this->em->getRepository(Project::class)->find($id);
        $project->setName($description);
        $error = $this->validator->validate($project);
        if(count($error)){
            $error = $this->serializer->serialize($error,'json');
            return new Response($error, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

}
