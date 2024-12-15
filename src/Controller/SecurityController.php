<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\Helpers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'pagina' => 'login',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/olvido_contrasena', name: 'app_olvido_cotrasena')]
    public function olvidoContrasena(AuthenticationUtils $authenticationUtils): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/olvido_contrasena.html.twig', [
            'pagina' => 'olvido_contrasena',
            'last_username' => $lastUsername,
        ]);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route(path: '/ajax/cambiar_contrasena', name: 'app_ajax_cambiar_contrasena')]
    public function ajaxCambiarContrasena(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, MailerInterface $mailer): JsonResponse
    {
        $status = false;
        $msg = '';
        $respuestas = array_map([Helpers::class, 'sanitizeTextField'], $_POST);

        $user = $this->getUser();

        if ($user != null) {
            $puede_cambiar = false;
            if ($user->isTempPass()) {
                $puede_cambiar = true;
            } else {
                if ($userPasswordHasher->isPasswordValid($user, $respuestas['actual_password'])) {
                    $puede_cambiar = true;
                } else {
                    $msg = 'La contraseña actual no coincide.';
                }
            }
            if ($puede_cambiar) {
                if ($respuestas['new_password_1'] == $respuestas['new_password_2']) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $respuestas['new_password_1']
                        )
                    );
                    $user->setTempPass(false);
                    $em->persist($user);
                    $em->flush();

                    $email = (new TemplatedEmail())
                        ->from(new Address('contacto@barregaray.cl', 'Matrimonio Barrenechea Etchegaray'))
                        ->to($user->getEmail())
                        ->subject('Hiciste un cambio de contraseña')
                        ->htmlTemplate('emails/cambio_contra.html.twig')
                        ->locale('es')
                        ->context([
                            'url' => 'https://www.barregaray.cl',
                        ]
                    );

                    $mailer->send($email);

                    $status = true;
                }
            }
        }

        return new JsonResponse([
            'status' => $status,
            'msg' => $msg,
        ]);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route(path: '/ajax/olvido_contrasena', name: 'app_ajax_olvido_cotrasena')]
    public function ajaxOlvidoContrasena(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, MailerInterface $mailer): JsonResponse
    {
        $status = false;
        $respuestas = array_map([Helpers::class, 'sanitizeTextField'], $_POST);

        $repoUser = $em->getRepository(User::class);

        $user = $repoUser->findOneBy([
            'email' => $respuestas['_username']
        ]);

        $pass = '';
        if ($user != null) {
            $pass = Helpers::generateRandomString(6, '0123456789');
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $pass
                )
            );
            $user->setTempPass(true);

            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('contacto@barregaray.cl', 'Matrimonio Barrenechea Etchegaray'))
                ->to($respuestas['_username'])
                ->subject('Olvidaste la contraseña')
                ->htmlTemplate('emails/olvido_contra.html.twig')
                ->locale('es')
                ->context([
                    'url' => 'https://www.barregaray.cl',
                    'pass' => $pass,
                ]
            );

            $mailer->send($email);

            $status = true;
        }

        return new JsonResponse([
            'status' => $status,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
