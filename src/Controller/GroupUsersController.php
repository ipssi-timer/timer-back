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
        $groups = $this->groupRepository->findAll();
        return $this->render('group/index.html.twig', [
            'groups' => $groups,
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
        dump($group->getUsers()->getValues());
        foreach($group->getUsers()->getValues() as $user){
          $group->addUser($user);
        }
        $this->entityManager->persist($group);
        $this->entityManager->flush();

        $this->addFlash('success', "new group has been created");

        return $this->redirectToRoute('group_list');
      }

      return $this->render('user/new.html.twig', [
        'form' => $form->createView(),
      ]);
    }


  /**
   * @Route("/delete/{id}", name="delete")
   * @ParamConverter("groupUsers", options={"mapping"={"id"="id"}})
   */
  public function delete (GroupUsers $group, EntityManagerInterface $entityManager)
  {
    $entityManager->remove($group);
    $entityManager->flush();
    $this->addFlash('success', "the GROUP has been deleted");
    return $this->redirectToRoute('group_list');
  }


  /**
   * @Route("/update/{id}",name="update")
   * @ParamConverter("groupUsers", options={"mapping"={"id"="id"}})
   */
  public function update(GroupUsers $groupUsers,Request $request){
          $form= $this->createForm(GroupUsersType::class,$groupUsers);
          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){
            dump($groupUsers->getUsers()->getValues());
            $creator = $this->entityManager->getRepository(User::class)->findOneByPseudo($groupUsers->getCreatorId());

            foreach ($groupUsers->getUsers()->getValues() as $user) {
              $groupUsers->addUser($user);
              if ($user == $creator) {
                $groupUsers->setCreatorId($creator->getId());
                $this->addFlash('success', "group admin has been updated");
                continue;
              }

            }

            $this->entityManager->persist($groupUsers);
            $this->entityManager->flush();
            $this->addFlash('success', "group has been updated");

            return $this->redirectToRoute('group_list');
          }
          return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
          ]);
  }

}
