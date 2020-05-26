<?php

namespace App\Controller;

use App\Entity\GroupUsers;
use App\Entity\User;
use App\Form\GroupUsersType;

use App\Repository\GroupUsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GroupUsersController
 * @package App\Controller
 * @Route("/group",name="group_")
 */
class GroupUsersController extends AbstractController
{
  private $groupRepository;
  private $entityManager;


  public function __construct(EntityManagerInterface $entityManager, GroupUsersRepository $groupRepository)
  {
    $this->groupRepository = $groupRepository;
    $this->entityManager = $entityManager;

  }
  /**
     * @Route("/list", name="list")
     */
    public function index()
    {
        return $this->render('group/index.html.twig', [
            'controller_name' => 'GroupController',
        ]);
    }

   /**
     * @Route("/new/{id}",name="new")
    */
    public function new ($id,Request $request){
      $group = new GroupUsers();
      $form = $this->createForm(GroupUsersType::class,$group);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()){
         $group->setCreatorId($id);
        // dump($group->getUsers()[0]);die;
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addFlash('success', "new group has been created");

        return $this->redirectToRoute('user');
      }

      return $this->render('user/new.html.twig', [
        'form' => $form->createView(),
      ]);
    }
}
