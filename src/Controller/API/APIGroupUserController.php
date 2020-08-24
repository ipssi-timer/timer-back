<?php

namespace App\Controller\API;

use App\Entity\GroupUsers;
use App\Entity\User;
use App\Form\GroupUsersType;
use App\Repository\GroupUsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api/v1/", requirements={"_locale": "en|es|fr"}, name="api_group_")
 */
class APIGroupUserController extends AbstractController
{
    private $em;
    private $serializer;
    private $validator;
    private $encoders;
    private $normalizers;


    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->em = $entityManager;
        $this->validator = $validator;
        $this->encoders = [ new XmlEncoder(), new JsonEncoder() ];
        $this->normalizers = [ new ObjectNormalizer() ];
        $this->serializer = new Serializer( $this->normalizers, $this->encoders );
    }

    /**
     * Récupère tous les groupes
     * @Route("groups", name="get-all", methods={"POST"})
     * @param  Request  $request
     * @return Response
     */
    public function getAll( Request $request )
    {
        $groups = $this->em->getRepository( GroupUsers::class )->findAll();
        $data = $this->serializer->serialize( $groups, 'json', [
            'circular_reference_handler' => function( $object ) {
                return $object->getId();
            }
        ] );

        return $this->json([
            'data' => $groups,
            'ok' => true
        ]);
    }


    /**
     * Met à jour un groupe
     * @Route("group/update", name="update", methods={"POST"})
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
     * @param  Request  $request
     * @return Response
     */
    public function getOne( Request $request )
    {
        $id = $request->query->get( 'id' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );
        $data = $this->serializer->serialize( $group, 'json', [
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
     * Créer un groupe
     * @Route("group/new",name="new", methods={"POST"})
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
     * @param  Request  $request
     * @return Response
     */
    public function new( Request $request )
    {
        $name = $request->query->get( 'name' );
        $group = new GroupUsers();

        if( empty( $this->getUser() ) ) {
            return $this->json([
                'message' => 'Pas connecté',
                'ok' => false
            ]);
        }

        $group->setCreatorId( $this->getUser()->getId() );
        $group->setName( $name );
        $group->addUser( $this->getUser() );

        $error = $this->validator->validate( $group );

        if( count( $error ) ) {
            return $this->json([
                'message' => $error,
                'ok' => false
            ]);
        }

        $this->em->persist( $group );
        $this->em->flush();

        return $this->json([
            'message' => 'Groupe créé !',
            'ok' => true
        ]);

    }

    /**
     * Supprime un groupe
     * @Route("group/delete", name="delete", methods={"DELETE"})
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
     * @param  Request  $request
     * @return Response
     */
    public function delete( Request $request )
    {
        $id = $request->query->get( 'id' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );
        if( empty( $group ) ) {
            return $this->json([
                'message' => 'Groupe inconnu',
                'ok' => false
            ]);
        }

        $this->em->remove( $group );
        $this->em->flush();

        return $this->json([
            'message' => 'Groupe supprimé !',
            'ok' => true
        ]);
    }


    /**
     * Met à jour le nom du groupe
     * @Route("group/update/name",name="update-name",methods={"POST"})
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
     *     name="id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @param  Request  $request
     * @return Response
     */
    public function updateName( Request $request )
    {
        $id = $request->query->get( 'id' );
        $name = $request->query->get( 'name' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );
        $group->setName( $name );
        $error = $this->validator->validate( $group );

        if( count( $error ) ) {
            return $this->json([
                'message' => $error,
                'ok' => false
            ]);
        }

        $this->em->persist( $group );
        $this->em->flush();

        return $this->json([
            'message' => 'Groupe mis à jour !',
            'ok' => true
        ]);
    }

    /**
     * Change l'administrateur du groupe
     * @Route("group/update/creator",name="update-creator",methods={"PUT"})
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
     * @SWG\Parameter(
     *     name="creator",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @param  Request  $request
     * @return Response
     */
    public function updateCreator( Request $request )
    {
        $id = $request->query->get( 'id' );
        $creator = $request->query->get( 'creator' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );

        $isInGroup = false;

        foreach( $group->getUsers() as $key => $value ) {
            if( $value->getId() === $creator ) {
                $isInGroup = true;
            }

            if (true === $isInGroup) break;
        }

        if( !$isInGroup ) {
            return $this->json([
                'message' => "L'utilisateur n'est pas dans le groupe.",
                'ok' => false
            ]);
        }

        $group->setCreatorId( $creator );
        $error = $this->validator->validate( $group );

        if( count( $error ) ) {
            return $this->json([
                'message' => $error,
                'ok' => false
            ]);
        }

        $this->em->persist( $group );
        $this->em->flush();

        return $this->json([
            'message' => 'Groupe mis à jour !',
            'ok' => true
        ]);
    }

    /**
     * Ajoute un utilisateur dans un groupe
     * @Route("group/add/user",name="add-user",methods={"PATCH"})
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
     * @SWG\Parameter(
     *     name="user_id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @param  Request  $request
     * @return Response
     */
    public function addUser( Request $request )
    {
        $id = $request->query->get( 'id' );
        $user_id = $request->query->get( 'user_id' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );
        $user = $this->em->getRepository( User::class )->find( $user_id );
        $group->addUser( $user );
        $error = $this->validator->validate( $group );
        if( count( $error ) ) {
            $error = $this->serializer->serialize( $error, 'json' );
            return new Response( $error, 500, [
                'Content-Type' => 'application/json'
            ] );
        }
        $this->em->persist( $group );
        $this->em->flush();
        $data = $this->serializer->serialize( [ 'message' => 'OK' ], 'json' );
        return new Response( $data, 200, [
            'Content-Type' => 'application/json'
        ] );
    }

    /**
     * Enleve un utilisateur d'un groupe
     * @Route("group/remove/user",name="remove-user",methods={"DELETE"})
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
     * @SWG\Parameter(
     *     name="user_id",
     *     type="integer",
     *     in="query",
     *     required=true,
     * )
     * @param  Request  $request
     * @return Response
     */
    public function removeUser( Request $request )
    {
        $id = $request->query->get( 'id' );
        $userId = $request->query->get( 'user_id' );
        $group = $this->em->getRepository( GroupUsers::class )->find( $id );
        $user = $this->em->getRepository( User::class )->find( $userId );

        if( $this->getUser()->getId() !== $group->getCreatorId() && $this->getUser()->getId() !== $userId ) {
            return $this->json([
                'message' => "Vous n'êtes pas administrateur de ce groupe !",
                'ok' => false
            ]);
        }
        if( $userId === $group->getCreatorId() && count( $group->getUsers() ) <= 1 ) {
            $this->em->remove( $group );
            $this->em->flush();
            return $this->json([
                'message' => "Aucun utilisateur enlevé du groupe",
                'ok' => true
            ]);
        }

        $group->removeUser( $user );
        if( $userId === $group->getCreatorId() && count( $group->getUsers() ) > 1 ) {
            $group->setCreatorID( $group->getUsers()[0]->getId() );
        }

        $this->em->persist( $group );
        $this->em->flush();

        return $this->json([
            'message' => "Utilisateur enlevé du groupe",
            'ok' => true
        ]);
    }

}
