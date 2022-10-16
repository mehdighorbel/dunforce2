<?php

namespace App\Controller;

use App\Service\AppParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/")
 */
class HomeController extends AbstractController
{
    private $appParams;

    public function __construct(
        AppParams $appParams
    )
    {
        $this->appParams = $appParams;
    }


    /**
     * Description :: retourner tous les organisations qui existent dans le fichier organizations.yaml
     * @Route("/", name="home_page")
     */
    public function index(): Response
    {
        return $this->render('organization/list-organization.html.twig', [
            'organizationsParams'=> $this->appParams->getAllParams()
        ]);
    }

    /**
     * Description :: ajouter ou modifier une organisation
     * @Route("/post-edit", name="add_edit_organization")
     */
    public function postEditOrganization(Request $request)
    {
        // position de l'organisation dans le fichier .yaml
        $position = $request->query->get('position');

        // recuperer tous les organisations du fichier yaml a l'aide d'un service
        $organization = $this->appParams->getAllParams();

        if ($position != null){
            return $this->render('organization/post-edit.html.twig', [
                'organization'=> $organization['organizations'][$position] ,
                'position'=> $position
            ]);
        }else{
            return $this->render('organization/post-edit.html.twig', [
                'position'=> count($organization['organizations'])
            ]);
        }

    }
    /**
     * Description :: sauvegarder les données d'une organisation
     * @Route("/save-organization-data",name="save_organization_data_ajax")
     */
    public function editOrganizationProfileAjax(Request $request)
    {
        // récupérer les données du formulaire organisation
        $data = $request->request->get('organization');

        // récupérer les données du formulaire des utilisateur (form repeater)
        $users = $request->request->get('user');

        if ($data != "") {
            // récupérer la position de l'organisation
            $position = $data['position'] ;
            $isNew = $data['isNew'];
            $organization = $this->appParams->getAllParams();
            if ($position != null and $isNew == false) {
                // dans le cas de modification, il faut supprimer l'organisation
                array_splice($organization['organizations'], $position, 1);
            }

            // preparer l'array des utilisateurs
            $arrayUsers = [];
            if ($users and count($users) == 1) {
                if (array_key_exists('name', $users[0]) && $users[0]['name'] != "" or array_key_exists('password', $users[0]) && $users[0]['password'] != "" or array_key_exists('role', $users[0]) && $users[0]['role'] != "") {
                    $user = ['name' => array_key_exists('name', $users[0]) ? $users[0]['name'] : '', 'password' => array_key_exists('password', $users[0]) ? $users[0]['password'] : '', 'role' => array_key_exists('role', $users[0]) ? $users[0]['role'] : []];
                    array_push($arrayUsers, $user);
                }
            } else if ($users and count($users) > 1) {
                foreach ($users as $item) {
                    if (array_key_exists('name', $item) && $item['name'] != "" or array_key_exists('password', $item) && $item['password'] != "" or array_key_exists('role', $item) && $item['role'] != "") {
                        $user = ['name' => array_key_exists('name', $item) ? $item['name'] : '', 'password' => array_key_exists('password', $item) ? $item['password'] : '', 'role' => array_key_exists('role', $item) ? $item['role'] : []];
                        array_push($arrayUsers, $user);
                    }
                }
            }

            // préparer l'array de l'organisation inclu les utilisateurs
            $organization['organizations'][$position] = ["name" => $data['name'], "description" => $data['description'],  'users' => $arrayUsers];

            // sauvegarder l'organisation dans le fichier .yaml
            $this->appParams->setAllParams($organization);
        }
        return new JsonResponse([
            'status' => 200,
            'message' => "Organisation modifier avec succès."
        ]);
    }

    /**
     * Description :: supprimer une organization
     * @Route("/delete-organization", name="delete_organization_profile")
     */
    public function deleteOrganizationProfile(Request $request)
    {
        // récupérer la position de l'organisation
        $position = $request->query->get('position');
        if ($position != null) {
            // recuperer tous les organisations du fichier yaml a l'aide d'un service
            $organization =$this->appParams->getAllParams();

            // supprimer l'organisation ayant la position déjà récupérée
            array_splice($organization['organizations'],$position,1);
            $this->appParams->setAllParams($organization);
        }
        return $this->redirectToRoute('home_page');
    }
}
