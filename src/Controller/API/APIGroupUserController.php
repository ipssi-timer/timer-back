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


class APIGroupUserController extends AbstractController
{
  private $em;
  private $serializer;
  private $validator;
  private $encoders;
  private $normalizers;



    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer,ValidatorInterface $validator){

        $this->em = $entityManager;
        $this->validator = $validator ;
        $this->encoders = array(new XmlEncoder(), new JsonEncoder());
        $this->normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($this->normalizers, $this->encoders);



    }

    /**
     * get all groups
     * @Route("/api/v1/groups/list", name="group_list_get", methods={"POST"})
     *@param Request $request
     *@return Response
     */
    public function groupsList(Request $request)
    {
        $group = $this->em->getRepository(GroupUsers::class)->findAll();
        $data = $this->serializer->serialize($group, 'json',[
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }



    /**
     * edit group
   * @Route("/api/v1/group/edit", name="groupUser_get", methods={"POST"})
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
     * @param Request $request
     * @return Response
   */
    public function index(Request $request)
    {
          $id = $request->query->get('id');
          $group = $this->em->getRepository(GroupUsers::class)->find($id);
          $data = $this->serializer->serialize($group, 'json',[
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
          }]);

          return new Response($data, 200, [
            'Content-Type' => 'application/json'
          ]);
    }

 /**
  * get group users
   * @Route("/api/v1/group/user/list", name="group_user_list", methods={"POST"})
  * @param Request $request
  * @return Response
   */
    public function list(Request $request)
    {

          $data = $this->serializer->serialize($this->getUser()->getGroups(), 'json',[
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              }]);

          return new Response($data, 200, [
            'Content-Type' => 'application/json'
          ]);
    }


  /**
   * create new group
   * @Route("api/v1/group/new",name="groupUser_new", methods={"POST"})
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
   * @param Request $request
   * @return Response
   */
  public function new (Request $request){
      $name = $request->query->get('name');
      $group = new GroupUsers();
    if(empty($this->getUser())){
      $data = $this->serializer->serialize(array('message'=>'not connected'), 'json');
      return new Response($data, 503, [
        'Content-Type' => 'application/json'
      ]);
    }
      $group->setCreatorId($this->getUser()->getId());
      $group->setName($name);
      $group->addUser($this->getUser());
      $error = $this->validator->validate($group);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
      $this->em->persist($group);
      $this->em->flush();
      $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
          'Content-Type' => 'application/json'
        ]);

  }

  /**
   * delete group
   * @Route("api/v1/group/delete", name="groupUser_delete", methods={"DELETE"})
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
   * @param Request $request
   * @return Response
   */
  public function delete (Request $request)
  {
      $id = $request->query->get('id');
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    if(empty($group)){
      $data = $this->serializer->serialize(array('message'=>'Empty Data'), 'json');
      return new Response($data, 400, [
        'Content-Type' => 'application/json'
      ]);
    }

    $this->em->remove($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }


  /**
   * update group name
   * @Route("api/v1/group/update/name",name="groupUser_update_name",methods={"POST"})
   *  @SWG\Response(
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
   * @param Request $request
   * @return Response
   */
  public function updateName(Request $request){
    $id = $request->query->get('id');
    $name = $request->query->get('name');
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $group->setName($name);
      $error = $this->validator->validate($group);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }

  /**
   * update group creator
   * @Route("api/v1/group/update/creator",name="groupUser_update_creator",methods={"PUT"})
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
   * @param Request $request
   * @return Response
   */



  public function updateCreator(Request $request){


      $id = $request->query->get('id');
      $creator = $request->query->get('creator');
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $is_in_group = false;
    foreach ($group->getUsers() as $key=>$value ){
        if ($value->getId() == $creator){
            $is_in_group = true;
        }
    }
    if (!$is_in_group){
        $error = $this->serializer->serialize(array('error'=>'this user is not in group '),'json');

        return new Response($error, 500, [
            'Content-Type' => 'application/json'
        ]);
    }
    $group->setCreatorId($creator);
      $error = $this->validator->validate($group);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * add new user to a group
   * @Route("api/v1/group/add/user",name="groupUser_add_user",methods={"PATCH"})
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
   * @param Request $request
   * @return Response
   */

  // add new user to group
  public function newUser(Request $request)
  {
    $id = $request->query->get('id');
    $user_id = $request->query->get('user_id');
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $user = $this->em->getRepository(User::class)->find($user_id);
    $group->addUser($user);
      $error = $this->validator->validate($group);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data,200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * delete user from group
   * @Route("api/v1/group/delete/user",name="groupUser_delete_user",methods={"DELETE"})
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
   * @param Request $request
   * @return Response
   */

  // delete user from group
  public function deleteUser(Request $request)
  {
    $id = $request->query->get('id');
    $user_id = $request->query->get('user_id');
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $user = $this->em->getRepository(User::class)->find($user_id);

    if($this->getUser()->getId() != $group->getCreatorId() && $this->getUser()->getId() != $user_id ){
      $data = $this->serializer->serialize(array('message'=>'you are not admin'), 'json');
      return new Response($data, 403, [
        'Content-Type' => 'application/json'
      ]);
    }
    if($user_id == $group->getCreatorId() && count($group->getUsers()) <= 1){
        $this->em->remove($group);
        $this->em->flush();
        $data = $this->serializer->serialize(array('message'=>'group delete 0 users'), 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


    $group->removeUser($user);
    if($user_id == $group->getCreatorId() && count($group->getUsers())  > 1){
          $group->setCreatorID($group->getUsers()[0]->getId());

    }
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

}
