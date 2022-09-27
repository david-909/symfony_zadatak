<?php

namespace App\Controller;

use App\Entity\PasswordResets;
use App\Entity\User;
use App\Form\UserType;
use App\My\Regex;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class PasswordResetController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    #[Route('/api/requestpassword', name: 'api_request_reset', methods: ["POST"])]
    public function requestReset(Request $request, TokenGeneratorInterface $tokenGeneratorInterface)
    {
        $userEmail = $request->request->all()["email"];

        $user = $this->em->getRepository(User::class)->findOneBy(["email" => $userEmail]);
        if ($user) {
            $token = $tokenGeneratorInterface->generateToken();
            $reset = new PasswordResets();
            $reset->setRequestedAt(Carbon::now("Europe/Belgrade"));
            $reset->setToken($token);
            $reset->setUser($user);
            $reset->setDidReset(0);
            $this->em->persist($reset);
            $this->em->flush();
        } else {
            return new JsonResponse(["message" => "Nismo pronasli korisnika sa tim mejlom"]);
        }
        #$url = $this->generateUrl("api_password_reset", ["token" => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $url = "http://127.0.0.1:4200/reset?token=" . $token;
        $email = new Email();
        $email->from(new Address('passwordreset@example.com', 'Password reset'))
            ->to($userEmail)
            ->subject("Password reset")
            ->text("Link to reset your password: $url");

        $dsn = Transport::fromDsn($_ENV["MAILER_DSN"]);
        $mailer = new Mailer($dsn);
        $mailer->send($email);

        return new JsonResponse(["token" => $token, "message" => "Poslali smo mejl preko kog mozete podesiti novu lozinku"], Response::HTTP_OK);
    }

    #[Route('/api/resetpassword', name: 'api_password_reset', methods: ["POST"])]
    public function resetPassword(Request $request): Response
    {
        $token = $request->query->get("token");
        $passwordReset = $this->em->getRepository(PasswordResets::class)->findOneBy(["token" => $token]);
        $user = $this->em->getRepository(User::class)->findOneBy(["id" => $passwordReset->getUser()->getId()]);
        $form = $this->createForm(UserType::class, $user, ["method" => "PUT"]);
        $form->handleRequest($request);
        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $newHashedPassword = $this->hasher->hashPassword($user, $request->request->all()["password"]);
                $passwordReset->setDidReset(1);
                $user->setPassword($newHashedPassword);
                $this->em->flush();
                return new JsonResponse("Uspesno ste promenili lozinku.", Response::HTTP_OK);
            } catch (\Throwable $th) {
                return new Response($th->getMessage());
            }
        } else {
            return new Response($form->getErrors(true));
        }
    }
}
