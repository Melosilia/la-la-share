<?php

namespace App\Controller;

use App\Entity\Musics;
use App\Form\MusicType;
use App\Repository\MusicsRepository;
use App\Service\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MusicsController extends AbstractController
{
    
    /**
     * @Route("/musics", name="musics")
     */
    public function index(MusicsRepository $musicsRepository): Response
    {
        $musics = $musicsRepository->findAll();

        return $this->render('musics/musics.html.twig', [
            'musics' => $musics
        ]);
    }

    /**
     * @Route("/musics/create", name="music_create")
     * @IsGranted("ROLE_USER", message="Seuls les membres peuvent ajouter des musiques")
     */
    public function musicCreate(Request $request, UploadService $uploadService)
    {
        $musics = new Musics();
        $form = $this->createForm(MusicType::class, $musics);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $audio = $form->get('audio')->getData();
            if ($image) {
                $fileName = $uploadService->uploadImage($image, $musics);
                $musics->setImage($fileName);
            }
            if ($audio) {
                $fileName = $uploadService->uploadMusic($audio, $musics);
                $musics->setAudio($fileName);
            }
            
            //dd($musics);
            $em = $this->getDoctrine()->getManager();
            $em->persist($musics);
            $em->flush();

            $this->addFlash('success', "La musique a bien été ajoutée");
            return $this->redirectToRoute('musics');
        }

        return $this->render('musics/add.html.twig', [
            'form' => $form->createView(),
            'musics' => $musics
        ]);
    }

    /**
    * @Route("/all-musics", name="all_musics")
    */
    public function allMusics(Request $request, MusicsRepository $musicsRepository): Response
    {         
        // $musics = $musicsRepository->searchMusic($request->request->all());
        $musics = $musicsRepository->findAll();
        return $this->render('musics/allMusics.html.twig', [
            'musics' => $musics
        ]);     
    }
}
