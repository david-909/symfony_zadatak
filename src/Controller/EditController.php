<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class EditController extends AbstractController
{

    public function __construct(EntityManagerInterface $em, private Security $security)
    {
        $this->em = $em;
    }

    #[Route('/api/edit', name: 'api_edit', methods: ["PUT"])]
    public function index(Request $request)
    {
        $userEmail = $this->security->getToken()->getUser()->getUserIdentifier();
        $user = $this->em->getRepository(User::class)->findOneBy(["email" => $userEmail]);
        $form = $this->createForm(UserType::class, $user, ["method" => "PUT"]);
        $form->handleRequest($request);
        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() and $form->isValid()) {
            if ($request->request->has("name"))
                $user->setName($request->request->all()["name"]);
            if ($request->request->has("surname"))
                $user->setSurname($request->request->all()["surname"]);
            $this->em->flush();
            $this->em->persist($user);

            return new Response("Uspesno ste promenili Vase podatke.");
        }

        return new Response($form->getErrors(true));
    }

    #[Route('/api/user', name: 'api_user', methods: ["GET"])]
    public function user(Request $request): Response
    {
        $userEmail = $this->security->getToken()->getUser()->getUserIdentifier();
        $user = $this->em->getRepository(User::class)->findOneBy(["email" => $userEmail]);
        $userData = new stdClass();
        $userData->name = $user->getName();
        $userData->surname = $user->getSurname();

        return new JsonResponse($userData);
    }
}
