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


/**
 * @Route("/api/v1/", requirements={"_locale": "en|es|fr"}, name="api_entry_")
 */
class APIEntryController extends AbstractController
{
    private $em;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->em = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Récupère une entrée (temps de travail)
     * @Route("entry/get", name="get",methods={"POST"})
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @SWG\Parameter(
     *     name="entry_id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @param  Request  $request
     * @return Response
     */
    public function getOne( Request $request )
    {
        $entry = $this->em->getRepository( Entry::class )
            ->find( $request->query->get( 'entry_id' ) );

        $data = $this->serializer->serialize( $entry, 'json', [
            'circular_reference_handler' => function( $object ) {
                return $object->getId();
            }
        ] );

        return $this->json( [
            'data' => $entry,
            'ok' => true
        ] );
    }

    /**
     * Récupère toutes les entrées
     * @Route("entries", name="get-all",methods={"POST"})
     * @SWG\Response(
     *     response="200",
     *     description="success",
     *)
     * @param  Request  $request
     * @return Response
     */
    public function getAll( Request $request )
    {
        $data = $this->serializer->serialize( $this->getUser()->getEntries(), 'json', [
            'circular_reference_handler' => function( $object ) {
                return $object->getId();
            }
        ] );

        return $this->json([
            'data' => $data,
            'ok' => true
        ]);
    }

    /**
     * Ajoute une nouvelle entrée
     * @Route("entry/new",name="new",methods={"POST"})
     * @SWG\Response(
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
     * @param  Request  $request
     * @return Response
     */
    public function new( Request $request )
    {
        $start = $this->em->query->get( 'start' );
        $end = $this->em->query->get( 'end' );
        $project_id = $this->em->query->get( 'project_id' );
        $project = $this->em->getRepository( Project::class )->find( $project_id );

        $entry = new Entry();
        $entry->setStartsAt( $start );
        $entry->setEndsAt( $end );
        $this->em->persist( $entry );
        $this->em->flush();

        $this->getUser()->attachEntry( $entry );
        $project->addEntry( $entry );
        $this->em->persist( $this->getUser() );
        $this->em->persist( $project );
        $this->em->flush();

        return $this->json([
            'message' => 'Temps de travail enregistrée !',
            'ok' => true
        ]);
    }


    /**
     * Met à jour un temps de travail
     * @Route("entry/update",name="update",methods={"PUT"})
     * @SWG\Response(
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
     * @param  Request  $request
     * @return Response
     */
    public function update( Request $request )
    {

        $end = $request->query->get( 'end' );
        $id = $request->query->get( 'entry_id' );
        $entry = $this->em->getRepository( Entry::class )->find( $id );
        $entry->setEndsAt( $end );
        $this->em->persist( $entry );
        $this->em->flush();

        return $this->json([
            'message' => 'Temps de travail mis à jour !',
            'ok' => true
        ]);
    }


}
