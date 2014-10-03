<?php

/* Gestion des membres par l'administrateur
 */

class GestionmembresController extends AdminController
{
    public function index()
    {
        $requested_ids = func_get_args();

        $membres_manager = $this->loadModel('MembresManagerModel');

        $data['membres'] = $membres_manager->get_items( 'membres', $requested_ids );

        $data['msg'] = $this->test_events_msg();

        $this->renderView('gestionmembres/index', $data);
    }

    protected function test_events_msg()
    {
        if ( Session::get('events.gestionmembres.msg') ) {
            switch ( Session::flashget('events.gestionmembres.msg') ) {
                default : $msg = 'Une erreur inconnue s\'est produite.';
            }
        } else {
            $msg = null;
        }

        return $msg;
    }
}
