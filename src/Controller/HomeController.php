<?php

namespace App\Controller;

use App\Entity\Persona;
use App\Entity\User;
use App\Services\Helpers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'pagina' => 'inicio'
        ]);
    }

    /**
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/ajax/guardar_asistente', name: 'app_home_guardar_asistente')]
    public function ajaxGuardarAsistente(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, MailerInterface $mailer): JsonResponse
    {
        $status = false;
        $existe_email_user = false;

        $respuestas = array_map([Helpers::class, 'sanitizeTextField'], $_POST);

        $pass = '';
        $vb = false;

        if (isset($respuestas['name']) && isset($respuestas['email'])) {
            $repoUser = $em->getRepository(User::class);

            $vb = in_array($respuestas['email'], ['vaiciant@gmail.com', 'catalina.etchegaray@mail.udp.cl']);

            $user_db = $repoUser->findOneBy([
                'email' => $respuestas['email']
            ]);

            if ($user_db == null) {
                $pass = Helpers::generateRandomString(6, '0123456789');
                $user_db = new User();
                if ($vb) {
                    $user_db->setRoles(['ROLE_ADMIN']);
                }
                $user_db->setEmail($respuestas['email']);
                $user_db->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user_db,
                        $pass
                    )
                );
                $user_db->setTempPass(true);

                $em->persist($user_db);
                $em->flush();
            }

            $persona_principal = new Persona();
            $persona_principal->setNombreCompleto($respuestas['name']);
            $persona_principal->setTipo('invitado');
            $persona_principal->setAlergias($respuestas['alergias']);
            $persona_principal->setMesaPreferencia($respuestas['mesa']);
            $persona_principal->setUser($user_db);
            $em->persist($persona_principal);

            if (isset($respuestas['switch_acompanante'])) {
                $persona_acompanante = new Persona();
                $persona_acompanante->setNombreCompleto($respuestas['nombre_completo_acompanante']);
                $persona_acompanante->setTipo('acompanante');
                $persona_acompanante->setAlergias($respuestas['alergias_acompanante']);
                $persona_acompanante->setMesaPreferencia($respuestas['mesa']);
                $persona_acompanante->setUser($user_db);
                $em->persist($persona_acompanante);
            }

            $em->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('contacto@barregaray.cl', 'Matrimonio Barrenechea Etchegaray'))
                ->to($respuestas['email'])
                ->subject('¡Gracias por confirmar tu asistencia!')
                ->htmlTemplate('emails/signup.html.twig')
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
            'pass' => $pass,
            'vb' => $vb
        ]);
    }
}
