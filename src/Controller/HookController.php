<?php

namespace App\Controller;

use App\Message\WhatsappNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\WhatsappService;
use App\Entity\Messages;

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
    public function whatsappHook(MessageBusInterface $bus, WhatsappService $messageService): Response
    {

        $messageManager = $messageService->getManager();
        $message = new Messages();
        $message->setWaId('34622814642');
        $message->setWhatsappMessage("texto");
        $message->setMessageType("1");
        //$message->setCreated("1640174341");

        $messageManager->persist($message);

        $messageManager->flush();


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
    public function index(WhatsappService $whatsappService, Request $request): Response
    {
        $content = $request->getContent();  
        $json = json_decode($content); //decode JSON and obtain data 
       $status = "KO";
       $message = " ";
       
      // if(!is_numeric($json->number)){      //check that it is a number  
       // $message = 'This is not a number';     //return error message
       //}else{
        $status = "OK"; //change status to success
        
        $work_schedule = false; //create boolean to check if the message arrives during business hours
         
        //Conditional to check whether the message arrives during work schedule or not
        if(!is_numeric($json->number)){    //if it is not a number, return error message 
            $message='This is not a number';
        }elseif($work_schedule==true){     //if it is a number and arrives within the schedule return wipe_in_hous template
           
            $whatsappService->sendWhatsApp(
                '34697110110', //Number
                [], //Placeholders
                'wipe_in_hous', //template 
                'es', //language
                'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85'); 
        }elseif($work_schedule==false){ //if it is a number and arrives out of hours return template wipe_out_hours
            $whatsappService->sendWhatsApp(
                '34697110110', //Number
                [], //Placeholders
                'wipe_out_hours', //template
                'es', //language
                'f6baa15e_fb52_4d4f_a5a0_cde307dc3a85');
           
        }else{ //any other option, return error
            $message = "error";
        }



        return $this->json([
            'status' => $status,
            'message' => $message
        ]);
    }
}
