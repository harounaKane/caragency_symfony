<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/compte', name: 'app_user')]
    public function index(): Response
    {
    //    $reservations = $reservationRepository->findBy(["user" => $user->getId()]);, ["reservations" => $reservations]

        return $this->render('user/index.html.twig');
    }

    #[Route('/update/compte/{id}', name: 'app_user_update', methods: ['GET', 'POST'])]
    public function update(Request $request, User $user): Response
    {

        $form = $this->createForm(RegistrationFormType::class, $user);

        // faire les traitements pour la mise à jour

        return $this->render('user/edit.html.twig', [
            "registrationForm" => $form
        ]);
    }
}
