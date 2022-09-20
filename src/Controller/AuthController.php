<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use App\My\Regex;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Throwable;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private Security $security,
        private SerializerInterface $serializerInterface,
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    # Registrovanje novog korisnika
    #[Route('/api/register', name: 'register', methods: ["POST"])]
    public function register(Request $request, TokenGeneratorInterface $tokenGeneratorInterface): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $token = $tokenGeneratorInterface->generateToken();
                $user->setEmail($request->request->all()["email"]);
                if (isset($request->request->all()["name"])) {
                    $user->setName($request->request->all()["name"]);
                }
                if (isset($request->request->all()["surname"])) {
                    $user->setSurname($request->request->all()["surname"]);
                }
                $user->setConfirmationCode($token);
                $user->setConfirmed(false);
                $password = $request->request->all()["password"];
                $newHashedPassword = $this->hasher->hashPassword($user, $password);
                $user->setPassword($newHashedPassword);
                $this->userRepository->add($user, true);

                $url = $this->generateUrl("account_confirmation", ["token" => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $email = new Email();
                $email->from(new Address('accounts@example.com', 'Account confirmation'))
                    ->to($request->request->all()["email"])
                    ->subject("Account confirmation")
                    ->text("Link to confirm your account: $url");

                $dsn = Transport::fromDsn($_ENV["MAILER_DSN"]);
                $mailer = new Mailer($dsn);
                $mailer->send($email);
            } catch (\Throwable $th) {
                return new Response($th->getMessage());
            }
        } else {
            return new Response($form->getErrors(true));
        }

        return new JsonResponse("Uspesno ste se registrovali. Na Vas mejl je stigao link preko kojeg mozete aktivirati nalog", Response::HTTP_CREATED);
    }

    # Ovde aktiviramo korisnika, odnosno potvrdjujemo njegov identitet
    #[Route('/api/confirmation', name: 'account_confirmation', methods: ["GET"])]
    public function confirmation(Request $request): Response
    {
        $token = $request->query->get('token');
        $user = $this->em->getRepository(User::class)->findOneBy(["confirmationCode" => $token, "confirmed" => 0]);
        if ($user) {
            $user->setConfirmed(true);
        } else {
            return new Response("Nismo pronasli korisnika ili je nalog vec aktiviran");
        }
        $this->em->flush();

        return new Response("Uspesno ste aktivirali nalog.");
    }
}
