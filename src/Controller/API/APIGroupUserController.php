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
use Symfony\Component\Serializer\SerializerInterface;

class APIGroupUserController extends AbstractController
{
  private $em;
  private $serializer;


  public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer){

    $this->em = $entityManager;
    $this->serializer = $serializer;

  }

  /**
     * @Route("/api/group/user/{id}", name="api_group_user", methods={"GET"})
     */
    public function index($id,Response $request)
    {
          $group = $this->em->getRepository(GroupUsers::class)->find($id);
          $data = $this->serializer->serialize($group, 'json');

          return new Response($data, 200, [
            'Content-Type' => 'application/json'
          ]);
    }

  /**
   * @Route("api/newGroup/{name}",name="new_group", methods={"GET"})
   */
  public function new ($name){
      $group = new GroupUsers();
      $group->setCreatorId($this->getUser()->getId());
      $group->setName($name);
      $this->em->persist();
      $this->em->flush();
      $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
        return new Response($data, 200, [
          'Content-Type' => 'application/json'
        ]);

  }

  /**
   * @Route("api/deleteGroup/{id}", name="delete_group", methods={"DELETE"})
   */
  public function delete ($id )
  {
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
   * @Route("api/updateGroupName/{id}/{name}",name="update_name",methods={"POST"})
   */
  public function updateName($id,$name){
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $group->setName($name);
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }

  /**
   * @Route("api/updateGroupCreator/{id}/{creator_id}",name="update_creator",methods={"PUT"})
   */
  public function updateCreator($id,$creator_id){
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $group->setCreatorId($creator_id);
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * @Route("api/AddUserupdateGroup/{id}/{user_id}",name="update_add_user",methods={"PATCH"})
   */
  public function newUser($id,$user_id){
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $user = $this->em->getRepository(User::class)->find($user_id);
    $group->addUser($user);
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data,200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * @Route("api/updateGroup/deleteUSer/{id}/{user_id}",name="delete_user",methods={"DELETE"})
   */
  public function deleteUser($id,$user_id){
    $group = $this->em->getRepository(GroupUsers::class)->find($id);
    $user = $this->em->getRepository(User::class)->find($user_id);

    if($this->getUser()->getId() != $group->getCreatorId() ){
      $data = $this->serializer->serialize(array('message'=>'you are not admin'), 'json');
      return new Response($data, 403, [
        'Content-Type' => 'application/json'
      ]);
    }
    $group->removeUser($user);
    $this->em->persist($group);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

}
