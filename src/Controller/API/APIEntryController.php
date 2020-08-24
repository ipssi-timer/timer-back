<?php

namespace App\Controller\API;

use App\Entity\Entry;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

class APIEntryController extends AbstractController
{
    private $em;
    private $serializer;
    private $validator;

  public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer,ValidatorInterface $validator){

    $this->em = $entityManager;
    $this->serializer = $serializer;
    $this->validator = $validator ;

  }
    /**
     * @Route("/api/v1/entry/index", name="a_p_i_entry",methods={"POST"})
     *  @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="entry_id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     */
    public function index(Request $request)
    {
        $entry = $this->em->getRepository(Entry::class)->find($request->query->get('entry_id'));
        $data = $this->serializer->serialize($entry, 'json',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }]);
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/api/v1/entry/user/list", name="api_entry_list",methods={"POST"})
     *  @SWG\Response(
     *     response="200",
     *     description="success",
     *)

     */
    public function list(Request $request)
    {
        $data = $this->serializer->serialize($this->getUser()->getEntries(), 'json',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }]);
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("api/v1/entry/new",name="api_entry",methods={"POST"})
        *  @SWG\Response(
        *     response="200",
        *     description="success",
        *)
        * @SWG\Parameter(
        *     name="start",
        *     type="string",
        *     in="query",
        *     required=true,
        * )
        * @SWG\Parameter(
        *     name="end",
        *     type="string",
        *     in="query",
        *     required=true,
        * )
        * @SWG\Parameter(
        *     name="project_id",
        *     type="integer",
        *     in="query",
        *     required=true,
        * )
     */
    public function new(Request $request){
        $start = $this->em->query->get('start');
        $end = $this->em->query->get('end');
        $project_id= $this->em->query->get('project_id');
        $project = $this->em-getRepository(Project::class)->find($project_id);

        $entry = new Entry();
        $entry->setStartsAt($start);
        $entry->setEndsAt($end);
        $this->em->persist($entry);
        $this->em->flush();

        $this->getUser()->addEntry($entry);
        $project->addEntry($entry);
        $this->em->persist($this->getUser());
        $this->em->persist($project);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * @Route("api/v1/update/entry",name="api_update_entry",methods={"PUT"})
     *  @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="end",
     *     type="string",
     *     in="query",
     *     required=true,
     * )
     * @SWG\Parameter(
     *     name="entry_id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     */
    public function updateEntry(Request $request){

        $end = $request->query->get('end');
        $id = $request->query->get('entry_id');
        $entry = $this->em->getRepository(Entry::class)->find($id);
        $entry->setEndsAt($end);
        $this->em->persist($entry);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


}
