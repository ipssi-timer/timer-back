<?php

namespace App\Controller\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Swagger\Annotations as SWG;



/**
 * @Route("/api/v1/login")
 */
class SecurityController extends AbstractController
{

    /**
     * @Route("", name="api_login", methods={"POST"})
     *@SWG\Response(
     *     response="400",
     *     description="Bad data",
     *)
     * @SWG\Parameter(
     *     name="email",
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

     */
    public function login(Request $request)
    {
        $user = $this->getUser();
        return $this->json([
            //'username' => $user->getUsername(),
            // 'roles' => $user->getRoles()
            'user' => $user
        ]);
    }
}
