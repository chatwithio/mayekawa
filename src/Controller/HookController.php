<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WhatsappService;

class HookController extends AbstractController
{
    /*
     * This is the webhook recieved from whatapp/facebook
     * FORMAT:
     * {
                "contacts": [
                    {
                        "profile": {
                            "name": "Ward"
                        },
                        "wa_id": "34622814642"
                    }
                ],
                "messages": [
                    {
                        "from": "34622814642",
                        "id": "ABGGNGIoFGQvAgo-sAr3kcI5DI30",
                        "text": {
                            "body": "Test from ward"
                        },
                        "timestamp": "1640174341",
                        "type": "text"
                    }
                ]
            }
     */

    #[Route('/hook-endpoint', name: 'hook_endpoint')]
    // POST
    public function whatsappHook(MessageBusInterface $bus, MessageService $messageService): Response
    {
        //We cannot wait to process it, so we send it for async processing

        $bus->dispatch(new WhatsappNotification('Whatsapp me!'));

        return $this->json([
            'message' => 'Message sent!',
        ]);
    }


    /*
     * This is called form out own server
     * FORMAT: {number:"34622824642"}
     *
     */

    #[Route('/chatwith-endpoint', name: 'chatwith_endpoint')]
    // POST
    public function index(WhatsappService $whatsappService): Response
    {

        $status = "KO";
        $message = '';

        $whatsappService->sendWhatsApp(
            '34622814642', //El numero
            [], //Placeholders, ej name
            'wipe_in_hous',
            'es',
            'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');


        return $this->json([
            'status' => $status,
            'message' => $message
        ]);
    }
}
