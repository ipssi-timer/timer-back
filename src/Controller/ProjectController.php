<?php

namespace App\Controller;

use App\Entity\GroupUsers;
use App\Entity\User;
use App\Entity\Project;

use App\Form\ProjectType;
use App\Repository\GroupUsersRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ProjectController
 * @package App\Controller
 * @Route("/project",name="project")
 */
class ProjectController extends AbstractController
{

    private $projectRepository;
    private $entityManager;

    /**
     * projectController constructor.
     * @param $groupUsersRepository
     */
    public function __construct(EntityManagerInterface $entityManager, GroupUsersRepository $groupUsersRepository)
    {
        $this->groupUserRepository = $groupUsersRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/list", name="listproject")
     */
    public function index()
    {
        return $this->render('group/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    /**
     * @Route("/new/{id}",name="newproject")
     */
    public function newProject ($id,Request $request)

    {

        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $project->setCreatorId($id);

            $this->entityManager->persist($project);
            $this->entityManager->flush();

            $this->addFlash('success', "Le projet a bien été créé !");

            return $this->redirectToRoute('projectlist');
        }

        return $this->render('project/new.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/update/{id}", name="updateproject")
     */

    public function updateProject ($id, Project $project, Request $request)

    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($project);
            $this->entityManager->flush();

            $this->addFlash('success', "Le projet a bien été modifié !");

            return $this->redirectToRoute('projectlist');
        }

        return $this->render('project/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="deleteproject")
     */

    public function deletepProject ($id, Project $project)
    {

    }
}
