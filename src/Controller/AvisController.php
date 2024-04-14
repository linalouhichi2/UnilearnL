<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Etudiant;
use App\Entity\Formation;
use App\Repository\FormationRepository;

use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/avis')]
class AvisController extends AbstractController
{       
    ////////////////// Client View ////////////////
    #[Route('/{user_id}/{idFormation}', name: 'app_avis_index', methods: ['GET'])]
public function index(AvisRepository $avisRepository, FormationRepository $formationRepository, int $idFormation , int $user_id): Response
{
    $avis = $avisRepository->findBy(['idFormation' => $idFormation]);

    return $this->render('avis/ClientView/index.html.twig', [
        'avis' => $avis,
        'idFormation' => $idFormation,
        'user_id' => $user_id,

    ]);
}
    
#[Route('/Client/List/{user_id}', name: 'Client_avis_list', methods: ['GET'])]
public function indexClientAvis(AvisRepository $avisRepository , int $user_id ): Response
{
    $avis = $avisRepository->findBy(['idEtudiant' => $user_id]);

    return $this->render('avis/ClientView/indexClient.html.twig', [
        'avis' => $avis,
        'user_id' => $user_id,
        

    ]);
}


#[Route('/new/{user_id}/{idFormation}', name: 'app_avis_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, int $idFormation, int $user_id): Response
{
    $avi = new Avis();
    $avi->setDate(new \DateTime()); 

    // Fetch the Formation entity using $idFormation
    $formation = $entityManager->getRepository(Formation::class)->find($idFormation);
    if (!$formation) {
        throw $this->createNotFoundException('Formation not found');
    }

    $avi->setIdFormation($formation); 

    // Fetch the Etudiant entity using $user_id
    $etudiant = $entityManager->getRepository(Etudiant::class)->find($user_id);
    if (!$etudiant) {
        throw $this->createNotFoundException('Etudiant not found');
    }

    $avi->setIdEtudiant($etudiant); 

    $form = $this->createForm(AvisType::class, $avi);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($avi);
        $entityManager->flush();

        return $this->redirectToRoute('app_avis_index', ['user_id' => $user_id, 'idFormation' => $idFormation ], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('avis/ClientView/new.html.twig', [
        'avi' => $avi,
        'form' => $form,
        'user_id' => $user_id,
        'idFormation' => $idFormation,
    ]);
}


#[Route('/{idAvis}/edit/{idFormation}/{user_id}', name: 'app_avis_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, AvisRepository $avisRepository, EntityManagerInterface $entityManager, int $idAvis, int $idFormation, int $user_id): Response
{
    // Fetch the existing Avis entity by idAvis
    $avi = $avisRepository->find($idAvis);
    if (!$avi) {
        throw $this->createNotFoundException('Avis not found');
    }

    $form = $this->createForm(AvisType::class, $avi);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Update the date if needed
        $avi->setDate(new \DateTime());

        // Ensure idFormation and idEtudiant remain unchanged
        $avi->setIdFormation($entityManager->getReference(Formation::class, $idFormation));
        $avi->setIdEtudiant($entityManager->getReference(Etudiant::class, $user_id));

        $entityManager->flush();

        return $this->redirectToRoute('Client_avis_list', ['user_id' => $user_id], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('avis/ClientView/edit.html.twig', [
        'avi' => $avi,
        'form' => $form,
        'idFormation' => $idFormation,
        'user_id' => $user_id,
    ]);
}



#[Route('/delete/{idAvis}/{user_id}', name: 'app_avis_delete', methods: ['POST'])]
public function delete(Request $request, Avis $avi, EntityManagerInterface $entityManager, int $user_id): Response
{
    if ($this->isCsrfTokenValid('delete' . $avi->getIdAvis(), $request->request->get('_token'))) {
        $entityManager->remove($avi);
        $entityManager->flush();
    }

    return $this->redirectToRoute('Client_avis_list', ['user_id' => $user_id], Response::HTTP_SEE_OTHER);
}




    ////////////////////// Admin Functions //////////////////////

    #[Route('/Admin/Avis/{idFormation}', name: 'admin_avis_index', methods: ['GET'])]
    public function indexAdmin(AvisRepository $avisRepository, FormationRepository $formationRepository, int $idFormation): Response
    {
        $avis = $avisRepository->findBy(['idFormation' => $idFormation]);
    
        return $this->render('avis/AdminView/indexAdmin.html.twig', [
            'avis' => $avis,
            'idFormation' => $idFormation,
    
        ]);
    } 

    #[Route('/Admin/delete/{idAvis}/{idFormation}', name: 'admin_avis_delete', methods: ['POST'])]
    public function deleteAdmin(Request $request, Avis $avi, EntityManagerInterface $entityManager, int $idFormation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $avi->getIdAvis(), $request->request->get('_token'))) {
            $entityManager->remove($avi);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('admin_avis_index', ['idFormation' => $idFormation], Response::HTTP_SEE_OTHER);
    }
    


}
