<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Entity\Messages;
use App\Message\WhatsappNotification;
use App\Service\BotService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private $logger;

    private $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;

    }

    public function __invoke(WhatsappNotification $message)
    {
        $message = json_decode($message);

        //Get the previous message from the database
        $previousMessage = $this->getPreviousMessage('34622814642');

        if($previousMessage){
            //decide what/if we send a message
            $this->getMessageToBeSent();

            //send the message
            $this->sendMessage();

            //save the data to the database
            $this->saveData();
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

    private function getMessageToBeSent(){

    }

    private function sendMessage(){

    }

    private function saveData(){

    }
}
