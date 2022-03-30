<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WhatsappService;
use App\Entity\Messages;
use Doctrine\Persistence\ManagerRegistry;


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
                        "wa_id": "34697110110"
                    }
                ],
                "messages": [
                    {
                        "from": "34697110110",
                        "id": "ABGGNGIoFGQvAgo-sAr3kcI5DI30",
                        "text": {
                            "body": "1"
                        },
                        "timestamp": "1640174341",
                        "type": "text"
                    }
                ]
            }
     */


    /*
     * This is for the webhook that facebook calls
     */

    #[Route('/hook-endpoint', name: 'hook_endpoint')]
    // POST
    public function whatsappHook(MessageBusInterface $bus, Request $request,): Response
    {
        //We cannot wait to process it, so we send it for async processing

        $content = $request->getContent();

        $bus->dispatch(new WhatsappNotification($content));

        return $this->json([
            //Facebook doesnt care about our message only the status code - 200 or 201
            'message' => 'Message ok!',
        ]);
    }


    /*
     * This is called form out own server
     * FORMAT: {number:"34697110110"}
     */

    #[Route('/chatwith-endpoint', name: 'chatwith_endpoint')]
    // POST
    public function index(
        WhatsappService $whatsappService,
        Request $request,
        ManagerRegistry $doctrine,
        LoggerInterface $logger): Response
    {
        $content = $request->getContent();
        $json = json_decode($content); //decode JSON and obtain data
        $status = "KO";
        $message = " ";
        $messageType = "";

        //create boolean to check if the message arrives during business hours
        $workSchedule = true;

        //Conditional to check whether the message arrives during work schedule or not
        //if it is not a number, return error message
        if (!is_numeric($json->number)) {
            $message = 'This is not a number';
        }
        //if it is a number and arrives within the schedule return wipe_in_hous template
        elseif ($workSchedule == true) {

            try{
                $whatsappService->sendWhatsApp(
                    $json->number, //Number
                    [], //Placeholders
                    'wipe_in_hous', //template
                    'es', //language
                    'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');

                $status = "OK";
                $messageType = "IH";
            }
            catch(\Exception $exception){
                $logger->error($exception->getMessage());
            }

        }
        //if it is a number and arrives out of hours return template wipe_out_hours
        elseif ($workSchedule == false) {

            try{
                $whatsappService->sendWhatsApp(
                    $json->number, //Number
                    [], //Placeholders
                    'wipe_out_hours', //template
                    'es', //language
                    'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');
                $status = "OK";
                $messageType = "OH";
            }
            catch (\Exception $exception){
                $logger->error($exception->getMessage());
            }

        } else {
            //any other option, return error
            $message = "error";
        }

        if($status == "OK"){
            try {
                $entityManager = $doctrine->getManager();
                $messages = new Messages();
                $messages->setWaId($json->number);
                $messages->setWhatsappMessage("template");
                $messages->setMessageType($messageType);
                $entityManager->persist($messages);
                $entityManager->flush();
            }
            catch (\Exception $exception){
                dd($exception);
                $logger->error($exception->getMessage());
            }
        }

        return $this->json([
            'status' => $status,
            'message' => $message
        ]);
    }
}
