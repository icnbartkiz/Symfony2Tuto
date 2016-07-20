<?php
/**
 * Created by PhpStorm.
 * User: ibart
 * Date: 09/07/2016
 * Time: 13:59
 */

namespace ICN\PlatformBundle\Controller;

use ICN\PlatformBundle\Entity\Advert;
use ICN\PlatformBundle\Form\AdvertType;
use ICN\PlatformBundle\Form\AdvertEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if( $page < 1 )
        {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        $nbPerPage =3;

        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em
            ->getRepository("ICNPlatformBundle:Advert")
            ->getAdverts($page, $nbPerPage)
        ;

        $nbPages = ceil(count($listAdverts)/$nbPerPage);

        if( $page > $nbPages )
        {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        return $this->render('ICNPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages' => $nbPages,
            'page' => $page
        ));
    }

    public function viewAction($id)
    {
        // On récupère le repository
        $em = $this->getDoctrine()->getManager();

        // On récupère l'entité correspondante à l'id $id
        $advert = $em->getRepository('ICNPlatformBundle:Advert')
            ->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository('ICNPlatformBundle:Application')
            ->findBy(array('advert' => $advert))
        ;

        // On récupère maintenant la liste des AdvertSkill
        $listAdvertSkills = $em
            ->getRepository('ICNPlatformBundle:AdvertSkill')
            ->findBy(array('advert' => $advert))
        ;

        return $this->render(
            "ICNPlatformBundle:Advert:view.html.twig",
            array(
                'advert' => $advert,
                'listApplications' => $listApplications,
                'listAdvertSkills' => $listAdvertSkills
            )
        );
    }

    public function addAction(Request $request)
    {
        // Création de l'entité
        $advert = new Advert();

        $form = $this->get('form.factory')->create(new AdvertType, $advert);

        if($request->isMethod('POST'))
        {
            if($form->handleRequest($request)->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();
            }

           $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistree.');

            return $this->redirectToRoute('icn_platform_view', array('id' => $advert->getId()));
        }

        return $this->render( "ICNPlatformBundle:Advert:add.html.twig", array(
            'form' => $form->createView()
        ) );
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert= $em->getRepository('ICNPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create(new AdvertEditType, $advert);


        // La méthode findAll retourne toutes les catégories de la base de données
        $listCategories = $em->getRepository('ICNPlatformBundle:Category')->findAll();

        // On boucle sur les catégories pour les lier à l'annonce
        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }


        if($request->isMethod('POST'))
        {
            if($form->handleRequest($request)->isValid())
            {
                // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
                // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

                // Étape 2 : On déclenche l'enregistrement
                $em->flush();
            }

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiee.');

            return $this->redirectToRoute('icn_platform_view', array('id' => $advert->getId()));
        }

        return $this->render( "ICNPlatformBundle:Advert:edit.html.twig", array(
            'form' => $form->createView(),
            'advert' => $advert
        ) );
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('ICNPlatformBundle:Advert')->find($id);

        if (null === $advert) {

            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");

        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirect($this->generateUrl('icn_platform_home'));
        }

        return $this->render( "ICNPlatformBundle:Advert:delete.html.twig", array(
            'advert' => $advert,
            'form'   => $form->createView()
        ));
    }

    public function menuAction($limit=3)
    {

        $listAdverts = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository("ICNPlatformBundle:Advert")
            ->findBy(
                array(),
                array("date" => "desc"),
                $limit,
                0
            )
        ;

        return $this->render('ICNPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));

    }

}