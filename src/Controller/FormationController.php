<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Administrateur;
use App\Entity\Etudiant;

use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formation')]
class FormationController extends AbstractController
{

    ///////////////////// Admin functions ///////////////////
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('formation/AdminView/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the existing Administrateur with ID 1
        $administrateur = $entityManager->getRepository(Administrateur::class)->find(1);
        
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);
    
        // Set the existing Administrateur as idAdministrateur
        $formation->setIdAdministrateur($administrateur);
        
        // Set the idEtudiant to 1
        $formation->setIdEtudiant(1);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formation);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('formation/AdminView/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }
    


    #[Route('/{IdFormation}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('formation/AdminView/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{IdFormation}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formation->getIdFormation(), $request->request->get('_token'))) {
            // Remove associated Avis entities
            foreach ($formation->getAvis() as $avis) {
                $entityManager->remove($avis);
            }
    
            // Remove the Formation
            $entityManager->remove($formation);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }
    

    //////////////////////// Client Functions /////////////////////
    #[Route('/Client/{user_id}', name: 'Client_formation_index', methods: ['GET'])]
    public function indexClient(FormationRepository $formationRepository , int $user_id): Response
    {
        return $this->render('formation/ClientView/index.html.twig', [
            'formations' => $formationRepository->findAll(),
            'user_id' => $user_id,

        ]);
    }

}
