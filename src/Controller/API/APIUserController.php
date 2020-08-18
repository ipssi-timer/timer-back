<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class APIUserController extends AbstractController
{
  private $em;
  private $serializer;


  public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer){

    $this->em = $entityManager;
    $this->serializer = $serializer;

  }
    /**
     * @Route("/api/user", name="api_user",methods={"GET"})
     */
    public function index()
    {
      if(empty($this->getUser())){
        $data = $this->serializer->serialize(array('message'=>'not connected'), 'json');
        return new Response($data, 503, [
          'Content-Type' => 'application/json'
        ]);
      }
      $data = $this->serializer->serialize($this->getUser(), 'json');
      return new Response($data, 200, [
        'Content-Type' => 'application/json'
      ]);
    }

  /**
   * @Route("api/newUser/{firstName}/{lastName}/pseudo/{birthDate}/{email}/{password}", name="new",methods={"GET"})
   */
  public function newAction($firstName,$lastName,$pseudo,$birthDate,$email,$password, UserPasswordEncoderInterface $passwordEncoder)
  {

      $user = new User();
      $user->setEmail($email);
      $user->setLastName($lastName);
      $user->setFirstName($firstName);
      $user->setBirthDate($birthDate);
      $user->setPseudo($pseudo);
      $password = $passwordEncoder->encodePassword($user,$password);
      $user->setPassword($password);
      $this->em->persist($user);
      $this->em->flush();

    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * @Route("api/delete_user/{id}", name="delete",methods={"DELETE"})
   */
  public function delete ($id, EntityManagerInterface $entityManager)
  {
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $entityManager->remove($user);
    $entityManager->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * @Route("api/updateUser/password/{id}/{password}"),name="update_password",methods={"PUT"})
   */
  public function update($id,$password,UserPasswordEncoderInterface $passwordEncoder){
      $user = $this->em->getRepository(User::class)->find($id);
     if(empty($user)){
        $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
        return new Response($data, 404, [
          'Content-Type' => 'application/json'
        ]);
      }
      $password = $passwordEncoder->encodePassword($user, $password);
      $user->setPassword($password);
      $this->em->persist($user);
      $this->em->flush();
      $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
      return new Response($data, 200, [
        'Content-Type' => 'application/json'
      ]);

  }
  /**
   * @Route("api/updateUser/pseudo/{id}/{pseudo}"),name="update_pseudo",methods={"PUT"})
   */
  public function updatePseudo($id,$pseudo){
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setPseudo($pseudo);
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * @Route("api/updateUser/email/{id}/{email}"),name="update_email",methods={"PUT"})
   */
  public function updateEmail($id,$email){
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setEmail($email);
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * @Route("api/updateUser/firstName/{id}/{firstName}"),name="update_firstName",methods={"PUT"})
   */
  public function updateFirstName($id,$firstName){
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setPassword($firstName);
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * @Route("api/updateUser/lastName/{id}/{lastName}"),name="update_lastName",methods={"PUT"})
   */
  public function updateLastName($id,$lastName){
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setPassword($lastName);
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }

  /**
   * @Route("api/updateUser/birthDate/{id}/{birthDate}"),name="update_birthDate",methods={"PUT"})
   */
  public function updatebirthDate($id,$birthDate){
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setPassword($birthDate);
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }

}
