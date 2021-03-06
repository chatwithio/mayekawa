<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Entity\Messages;
use App\Message\WhatsappNotification;
use App\Repository\MessagesRepository;
use App\Service\BotService;
use App\Service\WhatsappService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Message;

#[AsMessageHandler]
class WhatsappNotificationHandler
{

    private $logger;

    private $em;

    private $whatsappService;
    private $doctrine;

    private $textType;



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


        $jsonDecodedMessage = json_decode($content);

        if(!isset($jsonDecodedMessage->messages)){
            return;
        }


        //Get the previous message from the database
        $previousMessage = $this->getPreviousMessage('34697110110');

        if($previousMessage){
            //decide what/if we send a message
            $this->textType= " ";

            $textToBeSent =  $this->gettextToBeSent($previousMessage, $jsonDecodedMessage);
            //send the message
            $m = $this->sendMessage($textToBeSent, $jsonDecodedMessage);

            //save the data to the database
            $this->saveData($m);
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



        $prevtextType = $previousMessage->getMessageType();

        $msg_messages = $message ->{'messages'};
        $textMessage = $msg_messages[0]->text->body;
        $textToBeSent = 'This is not a valid answer,sorry';


        if($prevtextType== 'IH'){  //get text of the next message depending on the previous message
            if($textMessage==1 || $textMessage==2 || $textMessage==4 || $textMessage==5){

                $textToBeSent= "Bot MYCOM: ??Bien! Ahora indica la oficina de Mayekawa de M??xico m??s cerca a tu ubicaci??n:
                1. Ciudad de M??xico
                2. Monterrey
                3. Guadalajara
                4. Irapuato
                5. Hermosillo
                6. Culiac??n
                7. Villahermosa
                8. M??rida ";

                $this->textType = "IA0";

            }else if($textMessage==3){

                $textToBeSent = "Bot MYCOM: ??Gracias! Te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a tu petici??n. 
                
                
                Atenci??n
                3. ??scar Cabrera";

                $this->textType = "IB0";

            }else if($textMessage==6 || $textMessage==7){

                $textToBeSent = "Bot MYCOM: ??Gracias! Te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a tu petici??n.
                
                
                Atenci??n
                6 y 7: En revisi??n";

                $this->textType = "IC0";

            }else if($textMessage==8) {

                $textToBeSent = "Bot MYCOM: ??Bien! Ahora indica el producto que m??s te interesa:
                1. Toridas (Deshuesadora)
                2. Thermoshutter (Cortina de aire)
                3. Thermojack (Tunel de congelaci??n)
                4. Nantsune (Rebanadoras de carne)";

                $this->textType = "ID0";

            }

        }else if($prevtextType== 'OH') {

            $textToBeSent = "??Hola, gracias por escribir! Por el momento no nos encontramos disponibles. ??Podr??as dejarnos tus datos para que uno de nuestros ejecutivos se ponga en contacto en horario laboral?


            Bot MYCOM:
            Nombre
            Correo
            Tel??fono";
            $this->textType = "OA0";



        }else if($prevtextType == "IA0"){

            if($textMessage==1){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                1. Reina Bustamante.
                https://wa.me/34622814642 
                ";  // I put this link instead of the link to the executive until I what link is.
                $this->textType= "IA1";
            }else if($textMessage==2){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                2. C??sar Gonz??lez.
                https://wa.me/34622814642 
                ";  //change the link to the link of the executive

                $this->textType = "IA2";
            }else  if($textMessage==3){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                3. H??ctor Rubio. 
                https://wa.me/34622814642 
                ";  //change the link to the link of the executive

                $this->textType = "IA3";
            }else  if($textMessage==4){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                4. Patricia Ram??rez.";

                $this->textType = "IA4";
            }else  if($textMessage==5){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                5. Edgar Mart??nez.";
                $this->textType = "IA5";
            }else  if($textMessage==6){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                6. Nolberto Flores";
                $this->textType = "IA6";
            }else  if($textMessage==7){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                7. Isaac Rodr??guez.";
                $this->textType = "IA7";
            }else  if($textMessage==8){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                8. Ra??l Sol??s.";
                $this->textType = "IA8";
            }

        }else if($prevtextType == "IB0"){

            $textToBeSent = "Atenci??n
            3. Oscar Cabrera.";
            $this->textType = "IB1";

        }else if($prevtextType == "IC0"){

            $textToBeSent = "Atenci??n
            6 y 7: En revisi??n.";

            $this->textType = "IC1";

        }else if($prevtextType == "ID0"){

            if($textMessage==1 || $textMessage==2 || $textMessage==3){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                
                
                Atenci??n
                1,2 y 3: Ferando Vera.";
                $this->textType = "ID1";
            }else  if($textMessage==4){
                $textToBeSent = "??Gracias! te estamos transfiriendo con el ejecutivo MYCOM que le dar?? seguimiento a su petici??n.
                
                Atenci??n
                4. Juli??n Valenzuela.";
                $this->textType = "ID2";
            }

        }else if($prevtextType== "OA0"){

            $textToBeSent = "Bot MYCOM: ??Muchas gracias! El equipo MYCOM se comunicar?? en breve. ";

            $this->textType = "OA1";

        }
        return $textToBeSent;
    }


    private function sendMessage($textToBeSent,$message){  //send message with text depending on previous message
        $msg = $message->{'contacts'};
        $this->whatsappService->sendWhatsAppText(
            $msg[0]->wa_id,
            $textToBeSent
        );
    

        return $message;
    }

    private function saveData($message){ //save data of the sent message
        $msg_contacts = $message -> {'contacts'};
        $msg_messages = $message ->{'messages'};
        $messages = new Messages();
        $messages->setWaId($msg_contacts[0]->wa_id);
        $messages->setWhatsappMessage($msg_messages[0]->text->body);
        $messages->setMessageType($this->textType);
        $this->em->persist($messages);
        $this->em->flush();
    }
}
