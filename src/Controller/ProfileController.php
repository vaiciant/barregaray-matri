<?php

namespace App\Controller;

use App\Entity\Cambios;
use App\Entity\User;
use App\Services\Helpers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/perfil', name: 'app_profile')]
    public function index(EntityManagerInterface $em): Response
    {
        $asistentes_tabla = [];
        $repoUser = $em->getRepository(User::class);

        if($this->isGranted('ROLE_ADMIN')) {
            $all_users = $repoUser->findAll();
        } else {
            $all_users = [$this->getUser()];
        }

        foreach ($all_users as $user) {
            foreach ($user->getPersonas() as $i => $persona) {
                $datos_user = [];
                $datos_user['mail'] = $user->getEmail();
                $datos_user['nombre'] = $persona->getNombreCompleto();
                $datos_user['alergias'] = $persona->getAlergias();
                $datos_user['mesa'] = $persona->getMesaPreferencia();
                $datos_user['status'] = $persona->getStatus();
                $asistentes_tabla[] = $datos_user;
            }
        }

        return $this->render('profile/index.html.twig', [
            'asistentes_tabla' => $asistentes_tabla
        ]);
    }

    #[Route('/ajax/pedir_cambio', name: 'app_profile_pedir_cambio')]
    public function ajaxPedirCambios(EntityManagerInterface $em): JsonResponse
    {
        $status = false;
        $msg = 'error';

        $respuestas = array_map([Helpers::class, 'sanitizeTextField'], $_POST);

        if (isset($respuestas['modificacion'])) {
            $user = $this->getUser();
            if ($user != null) {
                $cambio = new Cambios();
                $cambio->setUser($user);
                $cambio->setTexto($respuestas['modificacion']);
                $em->persist($cambio);
                $em->flush();

                foreach ($user->getPersonas() as $i => $persona) {
                    $persona->setStatus('cambio');
                    $em->persist($persona);
                    $em->flush();
                }

                $status = true;
                $msg = 'creado|'.$cambio->getId();
            }
        }

        return new JsonResponse([
            'status' => $status,
            'msg' => $msg,
        ]);
    }
}
