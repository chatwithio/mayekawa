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
        $content = $message->getContent();
        $message = json_decode($content);
        $textToBeSent = " ";
        $textID= 0; 

        //Get the previous message from the database
        $previousMessage = $this->getPreviousMessage('34697110110');
    
        if($previousMessage){
            //decide what/if we send a message
            $this->gettextToBeSent($previousMessage, $message);

            //send the message
            $this->sendMessage($textToBeSent, $message);

            //save the data to the database
            $this->saveData($message, $textID);
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

    private function gettextToBeSent($previousMessage, $message){
    
        $prevtextID = $previousMessage->getID();
        $textMessage = $message -> getWhatsappMessage();

        if($prevtextID== 0){  //get text of the next message depending on the previous message 
            if($textMessage==1 || $textMessage==2 || $textMessage==4 || $textMessage==5){

                $textToBeSent = "Bot MYCOM: ¡Bien! Ahora indica la
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

                $textID = "2"; 

            }else if($textMessage==3){

                $textToBeSent = "Bot MYCOM: ¡Gracias! Te estamos
                transfiriendo con el ejecutivo MYCOM
                que le dará seguimiento a tu petición. ";

                $textID = "3";

            }else if($textMessage==6 || $textMessage==7){

                $textToBeSent = "Bot MYCOM: ¡Gracias! Te estamos
                transfiriendo con el ejecutivo MYCOM
                que le dará seguimiento a tu petición.";

                $textID = "4";
    
            }else if($textMessage==8) {
    
                $textToBeSent = "Bot MYCOM: ¡Bien! Ahora indica el
                producto que más te interesa:
                1. Toridas (Deshuesadora)
                2. Thermoshutter (Cortina de aire)
                3. Thermojack (Tunel de congelación)
                4. Nantsune (Rebanadoras de carne)";

                $textID = "5";

            }
    
        }else if($prevtextID== 1) {

            $textToBeSent = "¡Hola, gracias por
            escribir! Por el momento no nos
            encontramos disponibles. ¿Podrías
            dejarnos tus datos para que uno de
            nuestros ejecutivos se ponga en
            contacto en horario laboral?

            Bot MYCOM:
            Nombre
            Correo
            Teléfono";
            $textID = "18";

            

        }else if($prevtextID = "2"){

            if($textMessage==1){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                1. Reina Bustamante.";  // I put the text instead of the link to the executive until I know how to do it.
                $textID = "6";
            }else if($textMessage==2){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                2. César González.";
                $textID = "7";
            }else  if($textMessage==3){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                3. Héctor Rubio.";
                $textID = "8";
            }else  if($textMessage==4){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                4. Patricia Ramírez.";
                $textID = "9";
            }else  if($textMessage==5){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                5. Edgar Martínez.";
                $textID = "10";
            }else  if($textMessage==6){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                6. Nolberto Flores";
                $textID = "11";
            }else  if($textMessage==7){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                7. Isaac Rodríguez.";
                $textID = "12";
            }else  if($textMessage==8){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                8. Raúl Solís.";
                $textID = "13";
            }           

        }else if($prevtextID = "3"){

            $textToBeSent = "Atención
            3. Oscar Cabrera.";
            $textID = "14";

        }else if($prevtextID = "4"){

            $textToBeSent = "Atención
            6 y 7: En revisión.";

            $textID = "15";

        }else if($prevtextID = "5"){

            if($textMessage==1 || $textMessage==2 || $textMessage==3){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                1,2 y 3: Ferando Vera.";
                $textID = "16";
            }else  if($textMessage==4){
                $textToBeSent = "¡Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dará seguimiento a su petición.
                4. Julián Valenzuela.";
                $textID = "17";
            }           

        }else if($prevtextID = "18"){

            $textToBeSent = "Bot MYCOM: ¡Muchas gracias! El
            equipo MYCOM se comunicará en
            breve. ";

            $textID = "19";

        }
       
        return $textToBeSent;

    }
    

    private function sendMessage($textToBeSent,$message){  //send message with text depending on previous message
       
            $this->whatsappService->sendWhatsAppText(
                $message->number,
                'text',
                $textToBeSent);

        return $message;

        }
    

    private function saveData($message, $textID){ //save data of the sent message

            $messages = new Messages();
            $messages->setID($textID);
            $messages->setWaId($message->number);
            $messages->setWhatsappMessage($message->whatsappMessage);
            $messages->setMessageType($message->messageType);
            $this->em->persist($messages);
            $this->em->flush();

    }
}
