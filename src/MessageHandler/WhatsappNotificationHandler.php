<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Entity\Messages;
use App\Message\WhatsappNotification;
use App\Service\BotService;
use App\Service\WhatsappService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Doctrine\ORM\EntityManagerInterface;


#[AsMessageHandler]
class WhatsappNotificationHandler
{
    
    private $logger;

    private $em;

    private $whatsappService;
    private $doctrine;

    
    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, WhatsappService $whatsappService, EntityManagerInterface $doctrine)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->whatsappService = $whatsappService;
        $this->doctrine = $doctrine;

    }
    

    public function __invoke(WhatsappNotification $message)
    {
        $message = json_decode($message);
        $textToBeSent = " ";

        //Get the previous message from the database
        $previousMessage = $this->getPreviousMessage('34697110110');

        if($previousMessage){
            //decide what/if we send a message
            $this->gettextToBeSent($previousMessage);

            //send the message
            $this->sendMessage($textToBeSent, $message);

            //save the data to the database
            $this->saveData($message);
        }

        //Think about how to deal with an error like this....

    }

    private function getPreviousMessage($waId){
        return $this->em->getRepository(Messages::class)->findOneBy([
            'wa_id' => $waId
        ],
            [
            'id' => 'DESC'
        ]);
    }

    private function gettextToBeSent($waId){
        $message = json_decode($waId);

        if($message=='wipe_in_hous'){  //get text of the next message depending on the previous message 
            if('body'==1 || 'body'==2 || 'body'==4 || 'body'==5){

                $textToBeSent = "
                Bot MYCOM: ¡Bien! Ahora indica la
                oficina de Mayekawa de México más
                cerca a tu ubicación:
                1. Ciudad de México
                2. Monterrey
                3. Guadalajara
                4. Irapuato
                5. Hermosillo
                6. Culiacán
                7. Villahermosa
                8. Mérida ";

            }else if('body'==3){

                $textToBeSent = "Bot MYCOM: ¡Gracias! Te estamos
                transfiriendo con el ejecutivo MYCOM
                que le dará seguimiento a tu petición. ";

            }else if('body'==6 || 'body'==7){

                $textToBeSent = "Bot MYCOM: ¡Gracias! Te estamos
                transfiriendo con el ejecutivo MYCOM
                que le dará seguimiento a tu petición.";
    
            }else if('body'==8) {
    
                $textToBeSent = "Bot MYCOM: ¡Bien! Ahora indica el
                producto que más te interesa:
                1. Toridas (Deshuesadora)
                2. Thermoshutter (Cortina de aire)
                3. Thermojack (Tunel de congelación)
                4. Nantsune (Rebanadoras de carne)";

            }
    
        }else if($workSchedule = false && $message == 'wipe_out_hours') {

            $textToBeSent = " Bot MYCOM: ¡Hola, gracias por
            escribir! Por el momento no nos
            encontramos disponibles. ¿Podrías
            dejarnos tus datos para que uno de
            nuestros ejecutivos se ponga en
            contacto en horario laboral?

            Bot MYCOM:
            Nombre
            Correo
            Teléfono";

        }
       
        return $textToBeSent;

    }
    

    private function sendMessage($textToBeSent,$message){  //send message with text depending on previous message
       
            $this->whatsappService->sendWhatsAppText(
                $message->number,
                'text',
                $textToBeSent);

        return $message;
        dd($message);
        }
    

    private function saveData($message){ //save data of the sent message

            $messages = new Messages();
            $messages->setWaId($message->number);
            $messages->setWhatsappMessage($message->whatsappMessage);
            $messages->setMessageType($message->messageType);
            $this->em->persist($messages);
            $this->em->flush();

    }
}
