<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    /**
     * On va récupérer les paramètres via services.yaml
     */
    private $isMaintenance;
    private $messageMaintenance;

    public function __construct($isMaintenance, $messageMaintenance)
    {
        $this->isMaintenance = $isMaintenance;
        $this->messageMaintenance = $messageMaintenance;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        // Pas la requête principale ?
        // cf : https://symfony.com/doc/current/event_dispatcher.html#request-events-checking-types
        if (!$event->isMasterRequest()) {
            return;
        }

        // Ne pas afficher la maintenance sur les requêtes en AJAX (WDT, API JSON)
        if ($event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        
        // On n'exécute pas l'écouteur si on est dans le Profiler
        $requestUri = $event->getRequest()->getRequestUri();
        // On affiche le message de maintenance partout sauf dans le profiler
        if (preg_match('/_profiler/', $requestUri)) {
            return;
        }

        // dump('Subscriber Maintenance appelé');
        // Maintenance désactivée ?
        if (!$this->isMaintenance) {
            return;
        }
        
        // La réponse se trouve dans $event->getResponse()
        $response = $event->getResponse();
        // On récupère le contenu de la réponse
        $responseContent = $response->getContent();
        // On crée un nouveau contenu à partir du contenu de la réponse
        $maintenance = str_replace('<body>', '<body><div class="container mt-3 alert alert-danger">'.$this->messageMaintenance.'</div>', $responseContent);
        // On remplace le contenu d'origine avec le nouveau contenu
        $response->setContent($maintenance);

        // Pas besoin de return !
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => [
                ['onKernelResponse', -115]
            ],
        ];
    }
}
