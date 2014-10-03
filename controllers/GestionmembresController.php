<?php

/* Gestion des membres par l'administrateur
 */

class GestionmembresController extends AdminController
{
    public function index()
    {
        $requested_ids = func_get_args();

        $membres_manager = $this->loadModel('MembresManagerModel');

        $fields = 'id, pseudo, nom, email, sexe, ville, cp, adresse, statut';

        $data['membres'] = $membres_manager->get_items( 'membres', $requested_ids, $fields );

        $data['msg'] = $this->test_events_msg();

        $this->renderView('gestionmembres/index', $data);
    }

    public function setadmin()
    {
        $membres_manager = $this->loadModel('MembresManagerModel');

        // par défaut, seul l'utilisateur Manitou peut déclencher cette méthode
        if ( !$membres_manager->user_is_godlike() ) {
            Session::set('events.gestionmembres.msg', 'forbidden_access');
            header('location: /gestionmembres');
            return;
        }

        // si on accède à cette méthode depuis un formulaire, alors on a du travail
        if ( !empty($_POST['id'] ) ) {

            $modif_return = $membres_manager->modify_item(false);

            Session::set('events.gestionmembres.msg', $modif_return);
            header('location: /gestionmembres/index/' . intval($_POST['id']) );

        } else { // sinon, on se contente de renvoyer la page standard

            header('location: /gestionmembres');
            return;
        }
    }

    // suppression d'un membre
    public function supprimer($id_membre)
    {
        if ( empty($id_membre) ) {
            header('location: /gestionmembres');
            return;
        }

        $membres_manager = $this->loadModel('MembresManagerModel');

        // si le test échoue, c'est que la validation n'a pas été envoyée
        if ( !empty($_POST['id']) ) {

            $clean_id = intval($_POST['id']);

            $membre_cible = $membres_manager->get_items( 'membres', [$clean_id], 'statut' );

            if ( $membre_cible[0]['statut'] == '1' && !$membres_manager->user_is_godlike() ) {
                Session::set('events.gestionmembres.msg', 'forbidden_access');
                header('location: /gestionmembres');
                return;
            }

            $delete_return = $membres_manager->delete_item( 'membres', intval($_POST['id']) );

            Session::set('events.gestionmembres.msg', $delete_return);
            header('location: /gestionmembres');

        } else {
            // la validation n'a donc pas été envoyée, on affiche un message d'alerte
            $data = (int) $id_membre;

            $this->renderView('gestionmembres/supprimer', $data);
        }
    }

    protected function test_events_msg()
    {
        if ( Session::get('events.gestionmembres.msg') ) {
            switch ( Session::flashget('events.gestionmembres.msg') ) {
                case 'valid_modify_item' : $msg = 'Le membre a désormais le statut d\'administrateur.'; break;
                case 'valid_delete_item' : $msg = 'Le membre a été supprimé avec succès.'; break;
                case 'unknown_item_id'   : $msg = 'Aucun membre n\'a été supprimé.'; break;
                case 'forbidden_access'  : $msg = 'Vous n\'êtes pas autorisé à effectuer cette action.'; break;
                default                  : $msg = 'Une erreur inconnue s\'est produite.';
            }
        } else {
            $msg = null;
        }

        return $msg;
    }
}
