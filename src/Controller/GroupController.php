<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use App\Form\GroupType;

use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GroupController
 * @package App\Controller
 * @Route("/group",name="group_")
 */
class GroupController extends AbstractController
{
  private $groupRepository;
  private $entityManager;

  /**
   * UserController constructor.
   * @param $userRepository
   */
  public function __construct(EntityManagerInterface $entityManager, GroupRepository $groupRepository)
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
      $group = new Group();
      $form = $this->createForm(GroupType::class,$group);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()){
         $group->setCreatorId($id);
      //   dump($group);die;
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
