<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;

use App\Entity\GroupUsers;
use App\Form\GroupUsersType;
use App\Repository\GroupUsersRepository;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;



class AdminController extends AbstractController
{

    private $groupRepository;
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager, GroupUsersRepository $groupRepository, UserRepository $userRepository, ProjectRepository $projectRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->projectRepository = $projectRepository;

    }


    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("admin/listgroups", name="admin_listgroups")
     */
    public function groupsList()
    {
        $groups = $this->groupRepository->findAll();
        return $this->render('admin/list.html.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * @Route("admin/listusers", name="admin_listusers")
     */
    public function usersList()
    {
        dump($this->getUser());
        $userList = $this->userRepository->findAll();
        return $this->render('admin/list.html.twig', [
            'users' => $userList,
            'use' => $this->getUser(),
        ]);
    }

    /**
     * @Route("admin/listproject", name="admin_listprojects")
     */
    public function projectList ()
    {
        $projects = $this->projectRepository->findAll();
        return $this->render('admin/list.html.twig', [
            'projects' => $projects,
        ]);
    }
}
