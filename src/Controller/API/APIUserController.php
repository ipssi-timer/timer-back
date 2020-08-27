<?php

namespace App\Controller\API;

use App\Entity\GroupUsers;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

class APIUserController extends AbstractController
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
     * get actual user information
     * @Route("/api/v1/user", name="api_user",methods={"POST"})
     * @return Response
     */

    // get actual user
    public function index()
    {
      if(empty($this->getUser())){
        $data = $this->serializer->serialize(array('message'=>'not connected'), 'json');
        return new Response($data, 503, [
          'Content-Type' => 'application/json'
        ]);
      }
      $data = $this->serializer->serialize($this->getUser(), 'json',[
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          }]);
      return new Response($data, 200, [
        'Content-Type' => 'application/json'
      ]);
    }

  /**
   * create new user
   * @Route("api/v1/user/register", name="new",methods={"POST"})
   *  @SWG\Response(
   *     response="200",
   *     description="success",
   *)
   * @SWG\Parameter(
   *     name="birthDate",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @SWG\Parameter(
   *     name="firstName",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @SWG\Parameter(
   *     name="lastName",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @SWG\Parameter(
   *     name="email",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @SWG\Parameter(
   *     name="pseudo",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @SWG\Parameter(
   *     name="password",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // create new user
  public function newAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
  {
      $firstName = $request->query->get('firstName');
      $lastName = $request->query->get('lastName');
      $pseudo = $request->query->get('pseudo');
      $password = $request->query->get('password');
      $email = $request->query->get('email');
      $birthDate = $request->query->get('birthDate');

      if (!preg_match("/^([0-2][0-9]|(3)[0-1])(-)(((0)[0-9])|((1)[0-2]))(-)\d{4}$/",$birthDate) ) {
              $data = $this->serializer->serialize(array('message'=>'birthDate Invalide ! format accepté dd-mm-yyyy'), 'json');
              return new Response($data, 200, [
                'Content-Type' => 'application/json'
              ]);
      }

      $user = new User();
      $user->setEmail($email);
      $user->setLastName($lastName);
      $user->setFirstName($firstName);


      $user->setBirthDate(\DateTime::createFromFormat('d-m-Y', $birthDate));
      $user->setPseudo($pseudo);
      $password = $passwordEncoder->encodePassword($user,$password);
      $user->setPassword($password);
      $user->setRoles('ROLE_USER');

      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
      $this->em->persist($user);
      $this->em->flush();

    $data = $this->serializer->serialize(array('data'=>$user->getId()), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * delete user account
   * @Route("api/v1/user/delete", name="delete",methods={"DELETE"})
   *@SWG\Response(
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

  // delete user
  public function delete (Request $request, EntityManagerInterface $entityManager)
  {
      $id = $request->query->get('id');
    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $groups = $this->em->getRepository(GroupUsers::class)->findAll();
    foreach ($groups as $group){
        if ($group->getCreatorID() == $user->getId() && count($group->getUsers()) <= 1) {

            $this->em->remove($group);
            $this->em->persist($group);
        }
        if($group->getCreatorID() == $user->getId()  && count($group->getUsers())  > 1){
                $group->setCreatorID($group->getUsers()[0]->getId());
                $this->em->persist($group);
        }
    }

    $entityManager->remove($user);
    $entityManager->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);
  }

  /**
   * update user password
   * @Route("api/v1/user/update/password",name="update_password",methods={"PUT"})
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
   *     name="password",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update password
  public function update(Request $request,UserPasswordEncoderInterface $passwordEncoder)
  {
      $id = $request->query->get('id');
      $password = $request->query->get('password');
      $user = $this->em->getRepository(User::class)->find($id);
     if(empty($user)){
        $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
        return new Response($data, 400, [
          'Content-Type' => 'application/json'
        ]);
      }
      $password = $passwordEncoder->encodePassword($user, $password);
      $user->setPassword($password);
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
      $this->em->persist($user);
      $this->em->flush();
      $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
      return new Response($data, 200, [
        'Content-Type' => 'application/json'
      ]);

  }
  /**
   * user update pseudo
   * @Route("api/v1/user/update/pseudo",name="update_pseudo",methods={"PUT"})
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
   *     name="pseudo",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update pseudo
  public function updatePseudo(Request $request){
      $id = $request->query->get('id');
      $pseudo = $request->query->get('pseudo');

    $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setPseudo($pseudo);
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * update user email
   * @Route("api/v1/user/update/email",name="update_email",methods={"PUT"})
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
   *     name="email",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update email
  public function updateEmail(Request $request){
      $id = $request->query->get('id');

      $email = $request->query->get('email');

      $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setEmail($email);
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * update user firstName
   * @Route("api/v1/user/update/firstName",name="update_firstName",methods={"PUT"})
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
   *     name="firstName",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update first name
  public function updateFirstName(Request $request,$id,$firstName){
      $id = $request->query->get('id');
      $firstName = $request->query->get('firstName');
      $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setFirstName($firstName);
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }
  /**
   * update user lastName
   * @Route("api/v1/user/update/lastName",name="update_lastName",methods={"PUT"})
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
   *     name="lastName",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update last name
  public function updateLastName(Request $request){
      $id = $request->query->get('id');
      $lastName = $request->query->get('lastName');
      $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
    $user->setLastName($lastName);
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }

  /**
   * update user birthDate
   * @Route("api/v1/user/update/birthDate",name="update_birthDate",methods={"PUT"})
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
   *     name="birthDate",
   *     type="string",
   *     in="query",
   *     required=true,
   * )
   * @param Request $request
   * @return Response
   */

  // update birthDate
  public function updatebirthDate(Request $request){
      $id = $request->query->get('id');
      $birthDate = $request->query->get('birthDate');
      $user = $this->em->getRepository(User::class)->find($id);
    if(empty($user)){
      $data = $this->serializer->serialize(array('message'=>'user not found'), 'json');
      return new Response($data, 404, [
        'Content-Type' => 'application/json'
      ]);
    }
      $user->setBirthDate(\DateTime::createFromFormat('d-m-Y', $birthDate));
      $error = $this->validator->validate($user);
      if(count($error)){
          $error = $this->serializer->serialize($error,'json');
          return new Response($error, 500, [
              'Content-Type' => 'application/json'
          ]);
      }
    $this->em->persist($user);
    $this->em->flush();
    $data = $this->serializer->serialize(array('message'=>'OK'), 'json');
    return new Response($data, 200, [
      'Content-Type' => 'application/json'
    ]);

  }



}
