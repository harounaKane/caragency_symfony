<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Voiture;
use App\Form\CommentaireType;
use App\Form\ReservationType;
use App\Form\VoitureType;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/voiture')]
final class VoitureController extends AbstractController
{
    #[Route(name: 'app_voiture_index', methods: ['GET'])]
    public function index(VoitureRepository $voitureRepository): Response
    {
        return $this->render('voiture/index.html.twig', [
            'voitures' => $voitureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_voiture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $voiture = new Voiture();
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($voiture);
            $entityManager->flush();

            return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('voiture/new.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/detail', name: 'app_voiture_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Voiture $voiture, EntityManagerInterface $entityManager, #[CurrentUser()] ?User $user): Response
    {
        $reservation = new Reservation(); 
        $reservation->setDateReservation(new \DateTimeImmutable());

        $form = $this->createForm(ReservationType::class, $reservation);

        
        $form->remove("date_reservation");
        $form->remove("prix");
        $form->remove("vehicule");

        $form->handleRequest($request);

        $reservation->setVehicule($voiture);
        $reservation->setUser($user);

        if ($form->isSubmitted() && $form->isValid()) {

            $debut = $reservation->getDateDebut();
            $fin = $reservation->getDateFin();

            $nbJours = $debut->diff($fin)->format('%a');

            $reservation->setPrix( $voiture->getPrixJournalier() * $nbJours );

            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('app_user', [], Response::HTTP_SEE_OTHER);
        }

        $comment = new Commentaire();

        $formComment = $this->createForm(CommentaireType::class, $comment);

        $formComment->remove('date');
        $formComment->remove('vehicule');

        $formComment->handleRequest($request);

        $comment->setVehicule($voiture);

        //

        return $this->render('voiture/show.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
            'formComment' => $formComment
        ]);
    }

    #[Route('/{id}/edit', name: 'app_voiture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('voiture/edit.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_voiture_delete', methods: ['POST'])]
    public function delete(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$voiture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($voiture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_voiture_index', [], Response::HTTP_SEE_OTHER);
    }
}
