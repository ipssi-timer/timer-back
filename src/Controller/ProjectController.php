<?php

namespace App\Controller;

use App\Entity\GroupUsers;
use App\Entity\User;
use App\Entity\Project;

use App\Form\ProjectType;
use App\Repository\GroupUsersRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ProjectController
 * @package App\Controller
 * @Route("/project",name="project_")
 */
class ProjectController extends AbstractController
{
    private $projectRepository;
    private $entityManager;

    /**
     * projectController constructor.
     * @param $groupUsersRepository
     */
    public function __construct(EntityManagerInterface $entityManager, GroupUsersRepository $groupUsersRepository, ProjectRepository $projectRepository)
    {
        $this->groupUserRepository = $groupUsersRepository;
        $this->projectRepository = $projectRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/list", name="list")
     */
    public function listProject ()
    {
        $projects = $this->projectRepository->findAll();
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/new",name="new")
     */
    public function newProject (Request $request)

    {

        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $project->setCreatorId($this->getUser());

            $this->entityManager->persist($project);
            $this->entityManager->flush();

            $this->addFlash('success', "Le projet a bien été créé !");

            return $this->redirectToRoute('project_list');
        }

        return $this->render('project/form.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/update/{id}", name="update")
     */

    public function updateProject (Project $project, Request $request)

    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($project);
            $this->entityManager->flush();

            $this->addFlash('success', "Le projet a bien été modifié !");

            return $this->redirectToRoute('project_list');
        }

        return $this->render('project/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */

    public function deleteProject (Project $project, Request $request)
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
        $this->addFlash('success', "Le projet a bien été supprimé !");
        return $this->redirectToRoute('project_list');
    }
}
